(function($, core, shell, undefined) {

//#region Accessibility
shell.mergeSettings({
    disablePopups: false,
    accessibleMode: 'Normal',
    accessibility: {
        toc: {
            topic: 'Topic',
            closedBook: 'Closed book without topic',
            openBook: 'Open book without topic',
            closedBookTopic: 'Closed book with topic',
            openBookTopic: 'Open book with topic'
        },
        collapsibleSections: {
            collapsed: 'Click to expand',
            expanded: 'Click to collapse'
        },
        akLinksMenuHeader: '#{count} topics found'
    }
});
shell.driver('accessibility', function() {
    if (core.accessibility) {
        var stgs = shell.settings.accessibility || {},
            cs = stgs.collapsibleSections || {};
        shell.mergeSettings({
            toc: {
                options: {
                    tooltips: stgs.toc
                }
            },
            topic: {
                collapsibleSections: {
                    collapsed: {
                        tooltip: cs.collapsed
                    },
                    expanded: {
                        tooltip: cs.expanded
                    }
                }
            }
        });
    }
});
//#endregion

//#region method shell.popup()
shell.driver({
    name: 'popup',
    create: function() {
        shell.popup = function(el, options) {
            var w = el.data('popup'),
                t = $.type(options);
            if (w) {
                if (t === 'string') {
                    w[options].apply(w, core.slice(arguments, 2));
                }
                return w;
            }
            else if (t === 'object') {
                w = core.popup(el, options);
                w.popup.addClass('topic-popup');
                return w;
            }
            return undefined;
        }
    }
});
//#endregion
//#region topic spinner
shell.mergeSettings({
    topic: {
        spinner: {
            spinner: '#c1topicSpinner',
            message: '#c1topicSpinnerMessage',
            content: '#c1topicPanel',
            delay: 300
        }
    }
});
shell.driver({
    name: 'topicSpinner',
    create: function() {
        var stgs = shell.setting('topic.spinner'),
            spinner = $(stgs.spinner),
            spinnerMsg = $(stgs.message),
            topicBlock = $(stgs.content),
            timer = 0,
            delay = stgs.delay;
        spinnerMsg.css({
            marginTop: -Math.round(spinnerMsg.outerHeight() / 2),
            marginLeft: -Math.round(spinnerMsg.outerWidth() / 2)
        });
        function spin(show) {
            show = show !== false;
            topicBlock.toggle(!show);
            spinner.toggle(show);
            show && core.accessibilityFocusTo(spinnerMsg);
        }
        shell.topicSpin = spin;
        shell.bind('topicloading', function() {
            timer = setTimeout(spin, delay);
        });
        shell.bind('topicload', function() {
            timer && clearTimeout(timer);
            spin(false);
        });
        shell.ready(function() {
            spin(false);
        });
    }
});
//#endregion
//#region breadcrumbs
shell.mergeSettings({
    topic: {
        breadcrumbs: {
            panel: '#c1breadcrumbs',
            andSelected: false,
            separator: ' / ',
            itemTemplate: '<a <#if(this.url){#> href="#{url}" data-ref="#{itemId}"<#}#> title="#{tooltip}">#{title}</a>'
        }
    }
});
shell.driver({
    name: 'breadcrumbs',
    create: function() {
        var stgs = shell.setting('topic.breadcrumbs')
            tmpl = stgs.itemTemplate,
            separator = core.str(stgs.separator, ' / ');
        if (core.isString(tmpl)) {
            tmpl = core.template(tmpl);
        }
        function genBreadcrumbsHtml(links) {
            var s = '',
                toc = shell.toc;
            for (var i in links) {
                i = links[i];
                s += separator + tmpl(core.extend(i, { itemId: toc.itemId(i.path) }));
            }
            return s.substring(separator.length);
        }
        var panel = $(stgs.panel);
        if (panel.length) {
            panel.delegate('a', 'click', function(e) {
                var ref = $(this).data('ref');
                if (ref) {
                    shell.toc.select(ref);
                    e.preventDefault();
                }
            });
            shell.bind('tocdeselect', function() {
                panel.empty().hide();
            });
            function printBC() {
                var bc = shell.toc.getBreadcrumbs(stgs.andSelected);
                if (bc.length) {
                    panel.append(genBreadcrumbsHtml(bc)).show();
                }
                shell.trigger('breadcrumbsupdate');
            }
            shell.bind('tocselect', printBC);
        }
    }
});
//#endregion

})(jQuery, nethelp, nethelpshell);