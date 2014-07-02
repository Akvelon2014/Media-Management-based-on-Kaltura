(function($, core, undefined) {

var d_topicData = 'topicData';

core.widget('topic', {
    options: {
        path: '',
        updateTitle: true,
        linkSelector: 'a:not(.service), area:not(.service), .topic-link',
        linkCheck: function(link) {
            var url = link.data('ref') || (link.is('a, area') && core.normalizeHref(link)) || '';
            return (!core.isAbsoluteUrl(url) || link.hasClass('nethelp-link')) && url;
        },
        scriptsFilter: function(i, el) {
            return !/nethelp.redirector.js$/i.test(el.src || '');
        },
        replaceUrlElements: [
            ['a, area, link', 'href' ],
            ['img, input, embed', 'src'],
            [function(context) { return $('param', context).filter('[name="movie"]'); }, 'value'],
            ['.topic-link', 'data-ref']
        ],
        scrollup: {
            margin: 5
        }
    },
    _create: function() {
        var self = this,
            options = self.options,
            element = self.element;
        element.addClass('c1-topic');
        element.delegate(options.linkSelector, 'click', function(e) {
            var url = options.linkCheck($(this));
            if (url !== false) {
                e.preventDefault();
                self.load(url);
            }
        });
    },
    html: function(html, event, params) {
        /*
            html: {
                title: 'text',
                body: 'html',
                links: jquery,
                scripts: jquery,
                styles: jquery,
                meta: object
            },
            params: object (extra-params for events),
                params.data: object (add-in information) or false (to remove add-in information)
        */
        if ($.type(params) !== 'object') {
            params = { data: false };
        }
        var self = this,
            element = self.element,
            ie8 = $.browser.msie && parseInt($.browser.version.charAt(0), 10) < 9,
            r = $.when(),
            f,
            data,
            t,
            els;
        if (html === undefined) {
            return element.html();
        }
        if (!self.trigger('updating', event, params)) {
            return self;
        }
        data = (params === false || params.data === false) ? false : params;
        if (typeof html === 'string') {
            element.html(html);
            html = false;
        }
        if (data && html) {
            els = html.links;
            if (els) {
                if ((t = els.filter('[rel="prev"]')).length) {
                    data.prev = t.attr('href');
                }
                if ((t = els.filter('[rel="next"]')).length) {
                    data.next = t.attr('href');
                }
                data.links = els;
            }
            data.meta = html.meta || {};
        }
        if (data) {
            element.data(d_topicData, data);
        }
        else {
            element.removeData(d_topicData);
        }
        if (html) {
            if (!html.inTopic) {
                if (typeof html.body === 'string') {
                    element.html(html.body);
                }
                else if (html.body && html.body.jquery) {
                    element.html(html.body);
                }
                else if (html.jquery) {
                    element.html(html);
                }
                else {
                    element.html('');
                }
                t = { auto: 1, scroll: 1 };
                els = element.parents().filter(function() {
                    return !!t[$(this).css('overflow-y')];
                });
                (els.length ? els.first() : element).scrollTop(0);
                t = html.styles;
                if (t && t.length) {
                    t.each(function() {
                        var s = this;
                        if (ie8 && s.nodeName.toUpperCase() === 'LINK' && s.href) {
                            // IE7-8 required absolute urls
                            s.href = core.expandUrl(s.href);
                        }
                        element.prepend(s);
                    });
                }
                t = html.scripts;
                if (t && t.length) {
                    t.each(function() {
                        var script = this.src;
                        if (script) {
                            r = r.pipe(f = function() {
                                return $.ajax(script, {
                                    dataType: 'script',
                                    crossDomain: true,
                                    cache: true
                                });
                            }, f);
                        }
                        else {
                            element.append(this);
                        }
                    });
                }
            }
            if (self.options.updateTitle && 'title' in html) {
                document.title = html.title || 'No title';
            }
        }
        self.trigger('update', event, params);
        r.always(function() {
            self.trigger('ready', event, params);
        });
        return self;
    },
    load: function(url, event, params) {
        if (!params || typeof params !== 'object') {
            params = {};
        }
        // parse url ('test.html'|'test.html#header1'|'#header2')
        var self = this,
            element = self.element,
            options = self.options,
            data = element.data(d_topicData),
            t = typeof url === 'string' ? url.split('#') : [],
            query = t[0] || '',
            hash = t[1];
        if (!data) {
            element.data(d_topicData, data = {});
        }

        if (query) {
            if (!self.trigger('loading', event, $.extend({}, params, { url: query }))) {
                return $.when();
            }
            self.abort();
            var loadUrl = core.addUrlSearch(options.path + query, 'nhr=false');
            $.extend(params, {
                url: url,
                query: query,
                hash: hash,
                src: loadUrl
            });
            return self.request = $.ajax({
                url: loadUrl,
                dataType: 'text'
            }).then(function(resp, status, req) {
                var ifr = $('<iframe />').hide().appendTo('body'),
                    idoc = ifr[0].contentWindow.document,
                    doc = $(idoc),
                    loadPath,
                    meta = {};
                try {
                    idoc.open();
                    idoc.write(core.escapeScripts(req.responseText));
                    idoc.close();
                    if (self.trigger('validate', event, $.extend({}, params, { frame: ifr, document: doc }))) {
                        t = {
                            title: idoc.title,
                            baseUrl: doc.find('head base:last').remove().attr('href'),
                            scripts: doc.find('script[type="_js"]')
                                .remove()
                                .map(function() {
                                    // in native .importNode() in IE9, js will be loaded and executed
                                    return core.unescapeJSNode(core.copyNode(this, { content: 'text' }), true);
                                })
                                .filter(options.scriptsFilter),
                            styles: $($(idoc.createElement('p')).append(doc.find('style').remove()).html())
                                .add(doc.find('link[rel~="stylesheet"]').remove().map(function() {
                                    return core.copyNode(this, { content: false });
                                })),
                            links: doc.find('link')
                                .remove()
                                .map(function() {
                                    return core.copyNode(this, { content: false });
                                }),
                            meta: meta,
                            virtualDoc: true,
                            src: loadUrl
                        };
                        doc.find('head title, meta[http-equiv]').remove();
                        doc.find('meta[name]').remove().each(function() {
                            if (this.name) {
                                meta[this.name] = this.content;
                            }
                        });
                        loadPath = core.getUrlPath(loadUrl);
                        core.replaceUrls(doc, options.replaceUrlElements, loadPath);
                        core.replaceUrls(t.scripts, 'src', loadPath);
                        core.replaceUrls(t.links, 'href', loadPath);
                        core.replaceUrls(t.styles.filter('link'), 'href', loadPath);
                        t.body = core.unescapeScripts(doc.find('head').html() + doc.find('body').html());
                        doc = t;
                        ifr.remove();
                        ifr = idoc = undefined;
                        params.title = doc.title || params.title || 'No title';
                        self.trigger('load', event, params);
                        // TODO: (?) load-handler can change params.title
                        // if location.hash changes in load-event, title should be set after location.hash change
                        params.afterLoad = true;
                        self.html(doc, event, params);
                        if (hash) {
                            self.scrollTo(hash);
                        }
                    }
                }
                finally {
                    ifr && ifr.remove();
                }
            }, function(request, status, error) {
                if (status !== 'abort') {
                    element.removeData(d_topicData);
                }
                var d = $.extend({}, params, { status: status, error: error || true, request: request });
                self.trigger('loaderror', event, d);
                self.trigger('load', event, d);
            }).always(function() {
                delete self.request;
            });
        }
        else if (hash) {
            self.scrollTo(hash);
        }
        return $.when();
    },
    scrollTo: function(target) {
        var self = this,
            element = self.element,
            options = self.options.scrollup;
        if (options === false) {
            return self;
        }
        target = typeof target === 'string' ? element.findAnchor(target) : target = $(target);
        if (target && target.scrollup) {
            target.scrollup(options);
        }
        return self;
    },
    abort: function() {
        var request = this.request;
        if (request) {
            request.abort();
        }
    },
    getData: function() {
        return this.element.data(d_topicData);
    }
});

})(jQuery, nethelp);