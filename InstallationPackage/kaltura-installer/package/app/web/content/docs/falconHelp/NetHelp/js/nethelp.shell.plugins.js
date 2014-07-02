(function($, core, shell, undefined) {

/*
    pluginDescriptor: {
        name: string, (required)
        create: function,
        created: boolean,
        remove: function
    }
*/

//#region helpers
function getRefElement(el, context, cache) {
    var ref = el.data('ref');
    if (typeof ref === 'string') {
        ref = $(ref, context);
        if (cache !== false) {
            el.data('ref', ref);
        }
    }
    return ref;
}
if ($.ui) { // check if jQuery UI is used
    var flip = $.ui.position.flip;
    $.ui.position.c1flip = {
        left: function(position, data) {
            var t = $.ui.position.c1flip;
            min = (t.container || shell.topic.element).offset().left;
            flip.left(position, data);
            if (position.left < min) {
                position.left = min;
            }
        },
        top: function(position, data) {
            var t = $.ui.position.c1flip;
            min = (t.container || shell.topic.element).offset().top;
            flip.top(position, data);
            if (position.top < min) {
                position.top = data.collisionPosition.top;
            }
        }
    };
    shell.ready(function() {
        $.ui.position.c1flip.container = (shell.topic || {}).element || $('body');
    });
}
function popupInTopicOptions(options) {
    return $.extend(true, {
        popupCss: {
            maxHeight: 400,
            overflow: 'auto'
        },
        position: {
            collision: 'c1flip'
        }
    }, options || {});
}
//#endregion

//#region inline-text
shell.plugin({
    name: 'inlineText',
    create: function(topicElement) {
        topicElement = topicElement ? $(topicElement) : $('body');
        if (topicElement) {
            topicElement.delegate('.inline-text:not(.service)', 'click', function(e) {
                e.preventDefault();
                core.accessibilityFocusTo(getRefElement($(this), topicElement).toggle());
            });
        }
    }
});
//#endregion
//#region popup-text
shell.plugin({
    name: 'popupText',
    create: function(topicElement) {
        topicElement = topicElement ? $(topicElement) : $('body');
        if (topicElement) {
            topicElement.delegate('.popup-text:not(.service)', 'click', function(e) {
                e.preventDefault();
                var el = $(this), s;
                if (shell.settings.disablePopups) {
                    s = el.data('ref');
                    if (s && !s.jquery) {
                        s = 'title' === s ? el.attr('title') : $(s).html();
                        if (s) {
                            el.data('ref', s = $('<span />').hide().html(' ' + s).insertAfter(el));
                        }
                    }
                    s && core.accessibilityFocusTo(s.toggle());
                    return;
                }
                var pe = el.is('area') ? e : undefined;
                if (!shell.popup(el, 'toggle', pe)) {
                    s = el.data('ref');
                    if (s) {
                        s = 'title' === s ? el.attr('title') : $(s).html();
                        if (s) {
                            shell.popup(el, { html: s, event: pe });
                        }
                    }
                }
            });
        }
    }
});
//#endregion
//#region a/k-links
$.each({ index: 'k-link', groups: 'a-link' }, function(widget, plugin) {
    shell.plugin({
        name: plugin,
        create: function(topicElement) {
            function genAKLinksHtml(links, options) {
                if (typeof options === 'string') {
                    options = { tmpl: options };
                }
                options = options || {};
                var s = '<ul class="aklinks-menu">',
                    tmpl = core.template(options.tmpl || '<li><a href="#{url}"#{this.target ? \' target="\' + target + \'"\' : \'\'}>#{text}</a></li>');
                for (var i in links) {
                    s += tmpl(core.extend({ target: options.target }, links[i]));
                }
                return s + '</ul>';
            }
            var akLinksMenuHeader = core.template(core.str(shell.setting('accessibility.akLinksMenuHeader'), ''));
            topicElement = topicElement ? $(topicElement) : $('body');
            if (topicElement) {
                topicElement.delegate('.' + plugin + ':not(.service)', 'click', function(e) {
                    e.preventDefault();
                    var w = shell[widget];
                    if (w) {
                        var el = $(this),
                            key = el.data('ref'),
                            target = el.data('target') || el.attr('target');
                        w.find(key, function(err, links) {
                            if (links.length === 1) {
                                shell.topicLink(links[0].url, {
                                    target: target,
                                    element: el,
                                    event: e
                                });
                            }
                            else if (links.length > 1) {
                                var pe = el.is('area') ? e : undefined;
                                if (shell.settings.disablePopups) {
                                    var html = '<h3 class="aklinks-menu-title">' +
                                        akLinksMenuHeader({ count: links.length }) +
                                        '</h3>' +
                                        genAKLinksHtml(links, { target: target });
                                    core.accessibilityFocusTo(shell.topicHtml(html).find('.aklinks-menu-title'));
                                }
                                else if (!shell.popup(el, 'toggle', pe)) {
                                    shell.popup(el, popupInTopicOptions({ html: genAKLinksHtml(links, { target: target }), event: pe }));
                                }
                            }
                        });
                    }
                });
            }
        }
    });
});
//#endregion
//#region links in topic
shell.mergeSettings({
    topic: {
        externalLinkTarget: '_blank',
        topicLinkPattern: '\\.html?((\\?|#).*)?$'
    },
    windows: {
        secondary: 'left=100,top=100,width=800,height=600'
    }
});
function normalizeSettingsWindows() {
    var wins = shell.settings.windows,
        t = $.type(wins),
        torig = t;
    if (t === 'object') {
        wins = wins.window;
        if (wins && ($.isArray(wins) || wins.name)) {
            wins = $.makeArray(wins);
            t = 'array';
        }
        else {
            return;
        }
    }
    if (t === 'array') {
        t = {};
        $.each(wins, function(i, v) {
            var name = core.str(v.name, '');
            if (name) {
                t[name] = v.features;
            }
        });
        if (torig === 'object') {
            delete shell.settings.windows.window;
            shell.mergeSettings({ windows: t });
        }
        else {
            shell.settings.windows = t;
        }
    }
    else {
        shell.settings.windows = {};
    }
}
shell.plugin({
    name: 'topicLinks',
    serviceLinks: '.inline-text, .popup-text, .k-link, .a-link, .service'.split(', '),
    create: function() {
        shell.normalizeSettingsWindows = normalizeSettingsWindows;
        normalizeSettingsWindows();
        var rIsId = /^\w+$/,
            namesCache = {},
            guid = 189543,
            topic = shell.topic,
            topicElement = topic ? topic.element : $('body'),
            ignore = this.serviceLinks.join(', ');
        shell.popupLink = function(el, ref, e) {
            var pe = el.is('area') ? e : undefined,
                w = shell.popup(el, 'toggle', pe);
            if (w && !w.state()) {
                return;
            }
            var popup = w && w.popup,
                hash = ref.split('#');
            ref = hash[0];
            hash = hash[1];
            function toAnchor() {
                if (hash) {
                    var anchor = popup.findAnchor(hash);
                    if (anchor.length) {
                        anchor.scrollup({ parent: popup });
                    }
                }
            }
            if (w) {
                w.onShow(toAnchor);
            }
            else {
                var c = $('<p/>'),
                    part = el.data('part');
                $.ajax({
                    url: ref,
                    dataType: 'text',
                    success: function(resp, status, req) {
                        var c = $('<p/>').html(core.escapeScripts(resp));
                        if (part) {
                            c = c.find(part);
                        }
                        c.find('script[type="_js"], style, link[rel~="stylesheet"]').remove();
                        var html = c.html(),
                            path;
                        if (html) {
                            path = core.getUrlPath(ref);
                            w = shell.popup(el, { html: html, event: pe });
                            popup = w.popup;
                            w.onShow(toAnchor);
                            if (shell.topic) {
                                core.replaceUrls(popup, shell.topic.options.replaceUrlElements, path);
                            }
                            popup.addClass('popup-page');
                        }
                    }
                });
            }
        }
        function winName(name, prefix) {
            return rIsId.test(name) ? name :
                namesCache[name] || (namesCache[name] = prefix + guid++);
        }
        shell.topicLink = function(ref, options) {
            /*
                options: {
                    href,
                    target,
                    element,
                    event,
                    external
                }
            */
            if (!ref || typeof ref !== 'string') {
                return;
            }
            options = options || {};
            var target = options.target,
                el = options.element,
                e = options.event || core.Event(),
                win = target && target.charAt(0) !== '_' && wins[target];
            if (!core.isAbsoluteUrl(ref) && !options.external) {
                // topic-link
                if (target === 'popup' && el) {
                    e.preventDefault();
                    if (shell.settings.disablePopups) {
                        shell.loadTopic(ref, e);
                    }
                    else {
                        shell.popupLink(el, ref, e);
                    }
                    return;
                }
                else if (!target) {
                    if (topic) {
                        e.preventDefault();
                        shell.loadTopic(ref, e);
                    }
                    else if (!options.href) {
                        location.href = ref;
                    }
                    return;
                }
                else if (win) {
                    ref = core.addUrlSearch(ref, 'topiconly=true');
                }
            }
            else if (!target) {
                target = core.str(shell.setting('topic.externalLinkTarget'));
            }

            if (win) {
                e.preventDefault();
                window.open(ref, winName(target, 'swindowName'), win);
            }
            else if (target) {
                e.preventDefault();
                window.open(ref, winName(target, 'windowName'));
            }
            else if (e.isDefaultPrevented() || !options.href) {
                window.open(ref);
            }
        };
        if (topic) {
            // block handle links in widget topic
            topic.options.linkCheck = function() { return false; };
            topic.options.replaceUrlElements.push([
                'a, area', 'data-ref', ignore
            ]);
        }
        var wins = shell.settings.windows || {},
            isTopicFile = new RegExp(core.str(shell.setting('topic.topicLinkPattern')), 'i');
        function handler(e) {
            var el = $(this),
                href = el.is('a, area') && el.attr('href') != undefined,
                ref = el.data('ref') || href && core.normalizeHref(el, true);
            if (!ref || el.is(ignore) || !isTopicFile.test(ref)) {
                return;
            }
            shell.topicLink(ref, {
                href: href,
                event: e,
                element: el,
                external: el.hasClass('external-link'),
                target: el.data('target') || href && el.attr('target')
            });
        };
        topicElement.delegate('a, area, .topic-link, .external-link', 'click', handler);
        $('body').delegate('.c1-popup a, .c1-popup area, .c1-popup .topic-link, .c1-popup .external-link', 'click', handler);
        shell.bind('tocopen', function(e, d) {
            shell.topicLink(d.url, { target: d.target });
        });
        shell.bind('topicupdate', function() {
            // fix: Backspace key does not work in IE after clicking a link in topic text
            $('body').focus();
        });
    }
});
//#endregion
//#region goto Prev/Next/Home functions
shell.plugin({
    name: 'goto_',
    create: function() {
        var topic = shell.topic,
            toc = shell.toc,
            buttons = shell.buttons;
        $.each({ gotoPrev: 'prev', gotoNext: 'next' }, function(name, data) {
            shell[name] = function() {
                var url = (topic.getData() || {})[data];
                if (url) {
                    shell.loadTopic(url);
                }
                else {
                    toc[name]();
                }
            };
        });
        shell.gotoHome = function() {
            var navigator = shell.navigator;
            navigator && navigator.home();
        };
        shell.bind('topicupdate', function() {
            if (buttons) {
                buttons.prev && buttons.prev
                    .button((topic.getData() || {})['prev'] || toc.getPrev() ? 'enable' : 'disable')
                    .removeClass('ui-state-hover');
                buttons.next && buttons.next
                    .button((topic.getData() || {})['next'] || toc.getNext() ? 'enable' : 'disable')
                    .removeClass('ui-state-hover');
            }
        });
    }
});
//#endregion
//#region collapsible sections
shell.mergeSettings({
    topic: {
        collapsibleSections: {
            expanded: {
                icon: 'ui-icon ui-icon-circle-triangle-n',
                tooltip: 'Click to collapse'
            },
            collapsed: {
                icon: 'ui-icon ui-icon-circle-triangle-s',
                tooltip: 'Click to expand'
            },
            expandAll: {
                icon: 'ui-icon ui-icon-circle-triangle-s',
                label: undefined
            },
            collapseAll: {
                icon: 'ui-icon ui-icon-circle-triangle-n',
                label: undefined
            },
            highlight: undefined,
            headerClasses: [ 'C1SectionCollapsed', 'C1SectionExpanded' ]
        }
    }
});
shell.plugin({
    name: 'collapsibleSection',
    create: function() {
        var topic = shell.topic,
            topicElement = topic ? topic.element : $('body'),
            panel = $('#c1collapsiblePanel'),
            stg = shell.setting('topic.collapsibleSections') || {},

            c_highlight = core.str(shell.setting(stg, 'highlight')),
            ic_expanded = core.str(shell.setting(stg, 'expanded.icon')),
            ic_collapsed = core.str(shell.setting(stg, 'collapsed.icon')),
            it_expanded = core.str(shell.setting(stg, 'expanded.tooltip')),
            it_collapsed = core.str(shell.setting(stg, 'collapsed.tooltip')),
            c_header_classes = shell.setting(stg, 'headerClasses'),
            c;
        function cookieName(id) {
            return encodeURIComponent((topic ? (topic.getData() || {}).query || '' : 
                window.location.path).toLowerCase() + id);
        }
        function toggle(el, expand, store, init) {
            var elems = el.children(),
                header = elems.eq(0),
                section = elems.eq(1),
                icon = header.children('.icon'),
                id = el.attr('id');
            if (expand == undefined) {
                expand = el.data('collapsed') !== false;
            }
            el.toggleClass('collapsed', !expand);
            if (c_header_classes) {
                (c = c_header_classes[0]) && (header.toggleClass(c, !expand));
                (c = c_header_classes[1]) && (header.toggleClass(c, expand));
            }
            icon.removeClass(expand ? ic_collapsed : ic_expanded)
                .addClass(expand ? ic_expanded : ic_collapsed);
            if (core.accessibility) {
                icon.attr('title', expand ? it_expanded : it_collapsed);
            }
            section.toggle(expand);
            el.data('collapsed', !expand);
            if (store !== false && $.cookie && id) {
                $.cookie(cookieName(id),
                    expand ? true : false, { expires: 365 });
            }
            if (!init) {
                core.accessibilityFocusTo(section);
            }
        }
        function createCollapsible(el, expand) {
            el.addClass('collapsible');
            var header = el.children().eq(0).addClass('collapsible-header'),
                section = el.wrapInner('<div class="collapsible-section" />').children(),
                icon, id;
            header.insertBefore(section);
            if (header.length && section.length) {
                if (header.attr('tabindex') == undefined) {
                    header.attr('tabindex', 0);
                }
                if (core.accessibility) {
                    header.attr('onclick', 'void(0)');
                }
                icon = header.children('.icon');
                if (!icon.length) {
                    icon = $('<span class="icon"/>').prependTo(header);
                }
                id = el.attr('id');
                if ($.cookie && id) {
                    switch ($.cookie(cookieName(id))) {
                        case 'true':
                            expand = true;
                            break;
                        case 'false':
                            expand = false;
                            break;
                        default:
                            expand = undefined;
                    }
                }
                if (c_highlight) {
                    header.hover(function() {
                        section.addClass(c_highlight);
                    }, function() {
                        section.removeClass(c_highlight);
                    });
                }
                header.bind('click keydown', function(e) {
                    if (e.type === 'click' || e.which == 13) {
                        e.preventDefault();
                        toggle($(this).parent());
                    }
                });
                toggle(el, expand == undefined ? el.data('collapsed') === false : expand, false, true);
            }
        }
        function expandAll() {
            topicElement.find('.collapsible').each(function() {
                toggle($(this), true, undefined, true);
            });
        };
        function collapseAll() {
            topicElement.find('.collapsible').each(function() {
                toggle($(this), false, undefined, true);
            });
        };
        shell.expandAll = expandAll;
        shell.collapseAll = collapseAll;
        $('#c1expandAll').click(expandAll)
            .children()
            .filter('.icon').addClass(core.str(shell.setting(stg, 'expandAll.icon'), ''))
            .end()
            .filter('.label').text(core.str(shell.setting(stg, 'expandAll.label')));
        $('#c1collapseAll').click(collapseAll)
            .children()
            .filter('.icon').addClass(core.str(shell.setting(stg, 'collapseAll.icon'), ''))
            .end()
            .filter('.label').text(core.str(shell.setting(stg, 'collapseAll.label')));
        shell.bind('topicupdate', function() {
            var len = topicElement.find('[data-role="collapsible"]').each(function() {
                createCollapsible($(this));
            }).length;
            panel.toggle(!!len);
        });
    }
});
//#endregion
//#region related topics
shell.mergeSettings({
    topic: {
        relatedTopics: {
            icon: 'ui-icon ui-icon-arrowreturnthick-1-e'
        }
    }
});
shell.plugin({
    name: 'relatedTopics',
    create: function() {
        var c_icon = core.str(shell.setting('topic.relatedTopics.icon')),
            topicElement = (shell.topic || {}).element || $('body');
        shell.bind('topicupdate', function() {
            topicElement.find('.related-topics').children().each(function() {
                $(this).css('clear', 'left');
                $('<span/>')
                    .addClass('related-topic-icon')
                    .addClass(c_icon)
                    .prependTo(this);
            });
        });
    }
});
//#endregion
//#region context-sensitive
shell.mergeSettings({
    contextSensitive: {
        dataPath: '',
        dataSource: 'context.xml',
        dataType: 'xml',
        strings: {
            title: 'Topics for "#{key}": "#{value}"',
            notfound: 'No topics found.',
            notsupported: 'The key "#{key}" is not supported.'
        },
        searchOutputAsTopic: false
    }
});
shell.plugin({
    name: 'contextSensitive',
    create: function() {
        var ids = {},
            options = shell.settings.contextSensitive || {},
            strings = options.strings || {};
        // xmlDataProvider
        var _data = $.extend(true, core.dataProviders['xml'], {
            success: function(resp, status, request) {
                var r = {};
                $('context', resp).children().each(function() {
                    var i = $(this),
                        url = i.attr('url');
                    if (url) {
                        i.children('id').each(function() {
                            var id = $(this).text();
                            if (id) {
                                r[id] = url;
                            }
                        });
                    }
                });
                return r;
            }
        });
        shell.bind('navigatorchange', function(e, d) {
            if (d.isQuery && d.key && d.value) {
                var toc = shell.toc,
                    topic = shell.topic,
                    index = shell.index,
                    groups = shell.groups,
                    search = shell.search,
                    key = d.key,
                    value = d.value,
                    t,
                    html = '';
                function linksHandler(err, links) {
                    if (links.length === 1) {
                        shell.loadTopic(links[0].url);
                    }
                    else if (links.length > 0) {
                        var s = '<ul class="c1-index">';
                        for (var i = 0, l; (l = links[i]); ++i) {
                            s += '<li><a href="' + l.url + '">' + l.text + '</a></li>';
                        }
                        topic.html(s + '</ul>');
                    }
                    else {
                        topic.html(core.str(strings.notfound, ''));
                    }
                }
                if (shell.settings.updateTitle) {
                    document.title = core.str(strings.title, '')
                        .replace(/#{key}/g, key)
                        .replace(/#{value}/g, value);
                }
                toc && toc.deselect();
                switch (key.toLowerCase()) {
                    case 'keyword':
                        index && index.find(value, linksHandler, false);
                        break;
                    case 'group':
                        groups && groups.find(value, linksHandler, false);
                        break;
                    case 'id':
                        t = ids[value];
                        if (t) {
                            shell.loadTopic(t);
                        }
                        else {
                            topic.html(core.str(strings.notfound, ''));
                        }
                        break;
                    case 'search':
                        var serror = function() {
                            t = $('<ul class="c1-search" />');
                            search.trigger('loaderror', undefined, { element: t, highlight: false });
                            topic.html(t);
                        };
                        if (search) {
                            if (!search.readyState()) {
                                topic.html($('<p/>').html(core.str(shell.setting('search.strings.loading'), '')));
                            }
                            search.ready(function() {
                                topic.html('');
                                if (shell.setting('contextSensitive.searchOutputAsTopic') || shell.isTopicOnlyMode()) {
                                    t = $('<ul class="c1-search" />');
                                    if (search.options.disabled) {
                                        search.trigger('disabled', undefined, { element: t, highlight: false });
                                        topic.html(t);
                                    }
                                    else {
                                        search.search(value, t, function() {
                                            t.find('a.correcting').each(function() {
                                                var el = $(this);
                                                el.addClass('service')
                                                    .attr('href', '#?search=' + encodeURIComponent(el.text()));
                                            });
                                            topic.html(t);
                                        });
                                    }
                                }
                                else {
                                    shell.switchTab('search');
                                    search.search(value, undefined, function() {
                                        search.element
                                            .find('.c1-search-text')
                                            .first()
                                            .click();
                                    });
                                }
                            }, serror);
                        }
                        else {
                            serror();
                        }
                        break;
                    default:
                        topic.html(core.str(strings.notsupported, '').replace(/#{key}/g, key));
                        break;
                }
            }
        });
        return _data.read(options.dataPath + options.dataSource, function(data, error) {
            if (!error) {
                ids = data;
            }
        });
    }
});
//#endregion

})(jQuery, nethelp, nethelpshell);