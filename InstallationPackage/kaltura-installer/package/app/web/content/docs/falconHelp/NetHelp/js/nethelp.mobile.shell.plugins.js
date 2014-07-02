(function ($, core, shell, undefined) {

    // switch off NetHelp 2.0 collapsible sections to use jQuery Mobile collapsible sections
    shell.plugin('topicLinks').serviceLinks.push('.ui-collapsible-heading-toggle');
    shell.removePlugin('collapsibleSection');


    //#region related topics
    shell.mergeSettings({
        topic: {
            relatedTopics: {
                icon: 'arrow-r'
            }
        }
    });
    shell.plugin({
        name: 'relatedTopics',
        create: function () {
            var c_icon = core.str(shell.setting('topic.relatedTopics.icon')),
            topicElement = (shell.topic || {}).element || $('body');
            shell.bind('topicupdate', function () {
                topicElement.find('.related-topics').children().each(function () {
                    $(this).css('clear', 'left');
                    if (c_icon) {
                        $('<span />')
                        .addClass('related-topic-icon')
                        .addClass('ui-icon ui-icon-' + c_icon)
                        .addClass('ui-icon-shadow')
                        .prependTo(this);
                    }
                });
            });
        }
    });

    // process topics to initialize jQuery Mobile page
    shell.plugin({
        name: 'topicMobile',
        create: function (topicElement) {
            shell.bind('topicloading', function (e, p) {
                $.mobile.showPageLoadingMsg();
            });
            shell.bind('abort', function (e, p) {
                $.mobile.hidePageLoadingMsg();
            });
            shell.bind('topicupdate', function (e, p) {
                var content = shell.topic.element,
                    page = content.closest('div[data-role="page"]'),
                    url = $.mobile.path.makeUrlAbsolute(p.url, $.mobile.path.parseUrl(core.shell.baseUrl).pathname);
                var topic = nethelp.shell.topic;
                if (topic && topic.options.updateTitle) {
                    page.attr('data-title', document.title);
                    page.jqmData("title", document.title);
                }
                content.children().wrapAll('<div data-role="content" />').parent().page().children().unwrap();
                var options = {
                    allowSamePageTransition: true,
                    changeHash: !e.fromHashChange,
                    dataUrl: url
                };
                content.find('a').each(function () {
                    var el = $(this),
                        target = el.attr('target');
                    if (target && target !== 'popup' && !/^_(blank|self|parent|top)$/.test(target)) {
                        el.removeAttr('target');
                    }
                });
                page.attr('data-url', url);
                page.jqmData("url", url);
                page.attr('data-external-page', 'true');
                page.jqmData("external-page", 'true');
                $.mobile.changePage(page, options);
                $.mobile.hidePageLoadingMsg();
            });            
        }
    });

    shell.mergeSettings({
        general: {
            showTopicAtStartup: false
        }
    });

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
        create: function () {
            var ids = {},
            options = shell.settings.contextSensitive || {},
            strings = options.strings || {};
            // xmlDataProvider
            var _data = $.extend(true, core.dataProviders['xml'], {
                success: function (resp, status, request) {
                    var r = {};
                    $('context', resp).children().each(function () {
                        var i = $(this),
                        url = i.attr('url');
                        if (url) {
                            i.children('id').each(function () {
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
            shell.bind('navigatorchange', function (e, d) {
                if (d.isQuery && d.key && d.value) {
                    var toc = shell.toc,
                    topic = shell.topic,
                    search = shell.search,
                    key = d.key,
                    value = d.value,
                    t;
                    if (shell.settings.updateTitle) {
                        document.title = core.str(strings.title, '')
                        .replace(/#{key}/g, key)
                        .replace(/#{value}/g, value);
                    }
                    toc && toc.deselect();
                    switch (key.toLowerCase()) {
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
                            var serror = function () {
                                t = $('<ul class="c1-search" />');
                                search.trigger('loaderror', undefined, { element: t, highlight: false });
                                topic.html(t);
                            };
                            if (search) {
                                if (!search.readyState()) {
                                    topic.html($('<p/>').html(core.str(shell.setting('search.strings.loading'), '')));
                                }
                                search.ready(function () {
                                    topic.html('');
                                    shell.switchTab('search');
                                    search.search(value, undefined);
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
            return _data.read(options.dataPath + options.dataSource, function (data, error) {
                if (!error) {
                    ids = data;
                }
            });
        }
    });
    //#endregion

})(jQuery, nethelp, nethelpshell);