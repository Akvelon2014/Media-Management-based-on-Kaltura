(function ($, core, shell, undefined) {

    // default button options
    shell.mergeSettings({
        buttons: {
            contents: {
                id: 'c1contentsButton',
                showLabel: false,
                label: undefined, // from layout
                icon: 'home',
                iconpos: 'left'
            },
            index: {
                id: 'c1indexButton',
                showLabel: false,
                label: undefined, // from layout
                icon: 'home',
                iconpos: 'left'
            },
            prev: {
                id: 'c1topicPreviousButton',
                showLabel: false,
                label: undefined, // from layout
                icon: 'arrow-l',
                iconpos: 'left',
                click: 'this.gotoPrev(e);'
            },
            next: {
                id: 'c1topicNextButton',
                showLabel: false,
                label: undefined, // from layout
                icon: 'arrow-r',
                iconpos: 'right',
                click: 'this.gotoNext(e);'
            },
            prevBottom: {
                id: 'c1topicBottomPreviousButton',
                showLabel: false,
                label: undefined, // from layout
                icon: 'arrow-l',
                iconpos: 'left',
                click: 'this.gotoPrev(e);'
            },
            nextBottom: {
                id: 'c1topicBottomNextButton',
                showLabel: false,
                label: undefined, // from layout
                icon: 'arrow-r',
                iconpos: 'right',
                click: 'this.gotoNext(e);'
            }
        },
        search: {
            buttons: {
                go: {
                    id: 'c1searchButton',
                    showLabel: false,
                    label: undefined, // from layout
                    icon: 'search'
                }
            }
        }
    });

    shell.driver({
        name: 'buttons',
        create: function () {
            var buttons = {},
                btns = $.extend({}, shell.settings.buttons, shell.settings.caption.buttons, shell.settings.header.buttons, shell.settings.toc.buttons, shell.settings.index.buttons, shell.settings.search.buttons, shell.settings.topic.buttons),
                self = this;
            shell.buttons = buttons;
            core.each(btns || [], function (b, name) {
                var sel = core.isObject(b) ? b.id ? '#' + b.id : b.selector : undefined,
                bel = sel && $(sel),
                f;
                if (bel && bel.length) {
                    var btn = (bel && bel.hasClass('ui-btn')) ? bel : bel.closest('.ui-btn');
                    buttons[name] = bel;
                    if (b.visible || b.visible == undefined) {
                        if (b.icon !== undefined) {
                            var icon = btn.find('.ui-icon');
                            if (b.icon) {
                                var classes = icon.attr("class").split(" ");
                                for (var i = 0; i < classes.length; i++) {
                                    if (classes[i].indexOf('ui-icon-') === 0 && classes[i] !== 'ui-icon-shadow') {
                                        icon.removeClass(classes[i]);
                                    }
                                }
                                icon.addClass('ui-icon-' + b.icon);
                            }
                            else {
                                icon.remove();
                            }
                        }
                        if (b.showLabel && b.label) {
                            var txt = btn.find('.ui-btn-text');
                            (txt.length ? txt : btn).html(b.label);
                        }
                        if (b.label) {
                            btn.attr('title', b.label);
                        }

                        if ((!b.showLabel && b.showLabel != undefined) || !b.label) {
                            btn.removeClass(self.getIconPos(btn)).addClass('ui-btn-icon-notext');
                        }
                        else if (b.iconpos) {
                            btn.removeClass(self.getIconPos(btn)).addClass('ui-btn-icon-' + b.iconpos);
                        }
                        f = b.click;
                        f = core.isFunction(f) ? f :
                        core.isString(f) ? Function('e', 'target', 'options', f) :
                        undefined;
                        f && btn.click(function (e) {
                            return f.call(shell, e, this, b);
                        });
                    }
                    else {
                        btn.remove();
                    }
                }
            });
            buttons.prev = $(buttons.prev).add(buttons.prevBottom);
            buttons.next = $(buttons.next).add(buttons.nextBottom);
        },
        getIconPos: function (btn) {
            var styles = ['ui-btn-icon-left', 'ui-btn-icon-right', 'ui-btn-icon-top', 'ui-btn-icon-bottom', 'ui-btn-icon-notext'];
            for (var i = 0; i < styles.length; i++) {
                if (btn.hasClass(styles[i])) {
                    return styles[i];
                }
            }
        },
        hideBtnText: function (btn) {
            var self = this,
                iconpos = self.getIconPos(btn),
                pos = btn.data('pos');
            if (!pos || iconpos !== 'ui-btn-icon-notext') {
                btn.data('pos', iconpos);
            }
            btn.removeClass(iconpos).addClass('ui-btn-icon-notext');
        },
        showBtnText: function (btn) {
            var iconpos = btn.data('pos');
            if (iconpos) {
                btn.removeClass('ui-btn-icon-notext').addClass(iconpos);
            }
        }
    });

})(jQuery, nethelp, nethelpshell);