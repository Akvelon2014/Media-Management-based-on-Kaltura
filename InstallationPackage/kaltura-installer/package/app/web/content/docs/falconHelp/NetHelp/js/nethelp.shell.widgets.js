(function($, core, shell, undefined) {

/*
    widgetDescriptor: {
        name: string, (required)
        widgetName: string || this.name,
        check: function, (check whether an instance of this widget needs to be created)
        options: object|function|null
        create: function, (custom widget constructor, if defined)
        created: boolean, (set to true after widget is created, initialized, and registered)
        events: boolean|string || true, (flag indicating whether widget events need to be handled by the shell)
        init: function, (after create)
        nowait: boolean (default false) (if true, shell fires its ready event without waiting for this widget's ready event)
        ready: function, (fire on widget ready event)
        cancel: function, (fire on widget cancel event)
        finish: function, (fire when all widgets are created and ready)
        shellready: function, (fire when all drivers, widgets, and plugins are created and ready)
        remove: function (call when an already created widget is removed from collection)
    }
*/

var resolved = $.when();
// topic
// add settings-normalizator for option "replaceUrlElements"
shell.addSettingsNorm(function(s) {
    if ($.isArray(shell.setting('topic.options.replaceUrlElements.item'))) {
        var r = [];
        $.each(s.topic.replaceUrlElements.item, function(i, p) {
            r[i] = [ p.selector, p.attribute, p.not ];
        });
        s.topic.replaceUrlElements = r;
    }
});
var defStrTopicNotFound = 'Topic not found';
shell.mergeSettings({
    topic: {
        strings: {
            notfoundText: defStrTopicNotFound,
            notfoundTitle: defStrTopicNotFound
        }
    }
});
shell.widget({
    name: 'topic',
    element: '#c1topic',
    options: function() {
        return core.extend({
            updateTitle: shell.settings.updateTitle != false
        }, shell.setting('topic.options') || {});
    },
    init: function(topic) {
        var load = shell.loadTopic = function(url, e) {
            e = e && e.handled ? e : $.Event('unknown');
            if (!(e && e.isHandled('topicload')) && url) {
                url = core.normalizeHref(url);
                topic.abort();
                return topic.load(url, e);
            }
            return resolved;
        };
        shell.topicHtml = function(html, e) {
            topic.html(html, e);
            return topic.element;
        };
        shell.bind('tocselect navigatorchange', function(e, d) {
            if (d.url) {
                load(d.url, e);
            }
        });
        shell.bind('navigatorblank', function(e) {
            topic.html('', e);
            if (topic.options.updateTitle) {
                document.title = core.str(shell.setting('strings.title'), 'No title');
            }
        });
        shell.bind('topicloaderror', function(e, d) {
            if (d.status !== 'abort') {
                topic.html(core.str(shell.setting('topic.strings.notfoundText'), defStrTopicNotFound));
                if (topic.options.updateTitle) {
                    document.title = core.str(shell.setting('topic.strings.notfoundTitle'), defStrTopicNotFound)
                }
            }
        });
    }
});
// toc
shell.widget({
    name: 'toc',
    element: '#c1toc',
    options: function() {
        var stgs = shell.settings.toc || {};
        return core.extend(true, { icons: stgs.icons }, stgs.options || {});
    },
    init: function(toc) {
        var sync = shell.syncToc = function(url, e) {
            if (!(e && e.isHandled('tocselect')) && url) {
                return toc.select(url, e);
            }
            return resolved;
        };
        shell.bind('topicupdate', function(e, d) {
            if (d.afterLoad) {
                sync(d.query, e);
            }
            else {
                toc.deselect(e);
            }
        });
        shell.bind('topicloaderror', function(e, d) {
            if (d.status !== 'abort') {
                toc.deselect();
            }
        });
    }
});
// navigator
shell.widget({
    name: 'navigator',
    element: 'html',
    options: function() {
        var options = shell.settings.navigator || {};
        options.home = shell.setting('topic.home');
        return options;
    },
    init: function(navigator) {
        var navigate = shell.navigate = function(url, e) {
            if (!(e && e.isHandled('navigatorchange'))) {
                navigator.val({ url: url, isUrl: true }, e);
            }
        };
        shell.bind('topicload', function(e, d) {
            if (!d.error) {
                navigate(d.url, e);
            }
        });
    },
    shellready: function() {
        var w = this.instance,
            h = w.options.home,
            toc = shell.toc;
        if (!h && h !== false && toc) {
            w.options.home = toc.getData(toc.getFirst()).url;
        }
        w.init();
    }
});
// index
shell.mergeSettings({
    index: {
        strings: {
            filterTooltip: 'Filter keywords',
            loading: 'Loading...', // not used
            loaderror: 'Error: Index engine failed to load.', // not used
            emptyFilter: 'To load index keywords, enter first character(s) of the keyword.',
            insufficientFilter: 'Insufficient filter, please enter more characters.',
            notfound: 'No keywords found.',
            found: '#{count} keyword(s) found.'
        }
    }
});
shell.widget({
    name: 'index',
    element: '#c1index',
    options: function() {
        return core.extend(true, {
            moreText: shell.setting('index.strings.more')
        }, shell.setting('index.options') || {});
    },
    init: function(index) {
        var tmpl = core.template('<li><a href="#{url}" data-ref="#{url}">#{text}</a></li>');
        function genAKLinksHtml(links) {
            var s = '<ul class="aklinks-menu">';
            for (var i in links) {
                s += tmpl(links[i]);
            }
            return s + '</ul>';
        }
        var akLinksMenuHeader = core.template(core.str(shell.setting('accessibility.akLinksMenuHeader'), ''));
        shell.bind('indexselect', function(e, d) {
            var links = d.links;
            if (links.length === 1) {
                shell.loadTopic(links[0].url, e);
            }
            else if (links.length > 0) {
                var el = d.target;
                if (shell.settings.disablePopups) {
                    var html = '<h3 class="aklinks-menu-title">' +
                        akLinksMenuHeader({ count: links.length }) +
                        '</h3>' +
                        genAKLinksHtml(links);
                    core.accessibilityFocusTo(shell.topicHtml(html).find('.aklinks-menu-title'));
                }
                else if (!shell.popup(el, 'toggle')) {
                    var popup = shell.popup(el, { html: genAKLinksHtml(links) }).popup;
                    popup.delegate('a', 'click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.handled('indexselect');
                        popup.hide();
                        shell.loadTopic($(this).data('ref'), e);
                    });
                }
            }
        });
        shell.bind('topicupdate', function(e, d) {
            if (!e || !e.isHandled('indexselect')) {
                index.deselect();
            }
        });
        var indexElement = index.element;
        //#region messages
        var msgs = shell.setting('index.strings') || {},
            highlight = false;
        index.filterElement.attr('title', msgs.filterTooltip);
        function showMsg(type, text) {
            var m = indexElement.children('.' + type);
            if (!m.length) {
                m = $('<li class="service" />').addClass(type)
                    .html(core.str(text || msgs[type], type))
                    .appendTo(indexElement);
            }
            else if (text) {
                m.html(text);
            }
            m.show();
            if (highlight) {
                m.delay(10).effect('highlight', 1000);
            }
            return m;
        }
        core.each('emptyFilter insufficientFilter notfound loading loaderror'.split(' '), function(type) {
            index.bind(type, function() {
                core.accessibilityFocusTo(showMsg(type));
            });
        });
        var foundMsg = core.template(msgs['found'] || '');
        index.bind('found', function(e, d) {
            core.accessibilityFocusTo(showMsg('found', foundMsg(d)));
        });
        index.bind('load loaderror', function() {
            indexElement.children('.loading').hide();
        });
        //#endregion
    },
    ready: function() {
        shell.index.filter();
    }
});
// groups
shell.widget({
    name: 'groups',
    element: 'body',
    options: function() {
        return shell.setting('groups.options');
    }
});
// search
shell.mergeSettings({
    search: {
        strings: {
            filterTooltip: 'Search topics',
            helpMessage: 'You can use logical operations in the search string: #{and}, #{or}, #{not}.' + 
                ' Examples: football #{or} hockey, sports #{and} #{not} baseball',
            loading: 'Loading search engine...',
            loaderror: 'Error: Search engine failed to load.',
            disabled: 'Search is disabled.',
            notfound: 'No topics found.',
            correcting: 'Did you mean: #{query}',
            found: '#{count} topic(s) found.'
        },
        buttons: {
            go: {
                label: 'Search',
                icon: 'ui-icon ui-icon-search'
            },
            highlight: {
                label: 'Highlight search hits',
                icon: 'ui-icon ui-icon-lightbulb'
            },
            help: {
                label: 'Help',
                icon: 'ui-icon ui-icon-help'
            }
        }
    }
});
shell.widget({
    name: 'search',
    element: '#c1search',
    nowait: true,
    options: function() {
        var stgs = shell.settings.search || {};
        return core.extend(true, {
            operators: stgs.operators,
            moreText: (stgs.strings || {}).more
        }, stgs.options || {});
    },
    init: function(search) {
        var searchElement = search.element;
        shell.bind('searchselect', function(e, d) {
            shell.loadTopic(d.url, e);
        });
        shell.bind('topicupdate', function(e, d) {
            if (e && e.isHandled('searchselect')) {
                search.highlight();
            }
            else {
                search.deselect();
            }
        });
        //#region messages
        var msgs = shell.setting('search.strings') || {},
            highlight = false;
        search.filterElement.attr('title', msgs.filterTooltip);
        var correctingMsg = core.template(core.template(core.str(msgs.correcting, ''), 
                { query: '<a href="javascript:void(0)" class="correcting">#{correcting}</a>' })),
            foundMsg = core.template(core.str(msgs.found, ''));
        function showMsg(type, text, element, h) {
            var el = element || searchElement,
                m = el.children('.' + type);
            if (!m.length) {
                m = $('<li class="service" />').addClass(type)
                    .html(core.str(text || msgs[type], type))
                    .appendTo(el);
            }
            else if (text) {
                m.html(text);
            }
            m.show();
            if (highlight && !h) {
                m.delay(10).effect('highlight', 1000);
            }
            return m;
        }
        core.each('disabled loading loaderror'.split(' '), function(type) {
            search.bind(type, function(e, d) {
                core.accessibilityFocusTo(showMsg(type, undefined, d.element));
            });
        });
        core.each('found notfound'.split(' '), function(type, i) {
            search.bind(type, function(e, d) {
                var m = i ? msgs[type] : foundMsg(d);
                if (d.correcting) {
                    m += ' ' + correctingMsg(d);
                }
                core.accessibilityFocusTo(showMsg(type, m, d.element));
            });
        });
        //#endregion
    },
    shellready: function() {
        shell.search.options.highlight.element = shell.topic.element;
    }
});

})(jQuery, nethelp, nethelpshell);