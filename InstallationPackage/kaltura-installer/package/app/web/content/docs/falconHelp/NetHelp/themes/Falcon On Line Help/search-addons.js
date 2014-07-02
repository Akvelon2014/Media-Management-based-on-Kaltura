(function($, core, shell, undefined) {

shell.plugin({
    name: 'searchAddons',
    create: function() {
        var search = shell.search;
        if (!search) return;
        var options = search.options,
            buttons = shell.setting('search.buttons') || {},
            b, bel, bset = [];
        function setButton(btn, label, icon) {
            if (label !== undefined) {
                btn.attr('title', label);
            }
            if (icon !== undefined) {
                btn.attr('class', (btn.attr('class') || '').replace(/ui-icon[\w\-]*\s*/g, ''));
                btn.addClass(icon);
            }
            bset.push(bel);
        }

        // button Go
        b = buttons.go || {};
        bel = $(b.id || '#c1searchButtonGo');
        if (bel.length) {
            bel.click(function() {
                if (!this.disabled) {
                    search.search();
                }
            });
            setButton(bel, b.label, b.icon);
            search.buttonGo = bel;
        }

        // button Help
        b = buttons.help || {};
        bel = $(b.id || '#c1searchButtonHelp');
        if (bel.length) {
            var helpMsg = $(options.helpMessageElement || '#c1searchHelpMessage'),
                ops = options.operators;
            helpMsg.html(core.str(shell.setting('search.strings.helpMessage'), '')
                .replace(/#{and}/g, ops.and)
                .replace(/#{or}/g, ops.or)
                .replace(/#{not}/g, ops.not));
            bel.click(function() {
                if (!this.disabled) {
                    shell.popup($(this), 'toggle');
                }
            });
            shell.popup(bel, {
                autoShow: false,
                html: helpMsg.show(),
                position: {
                    maxWidth: 300
                }
            });
            setButton(bel, b.label, b.icon);
            search.buttonHelp = bel;
            search.helpMessage = helpMsg;
        }

        // button Highlight
        b = buttons.highlight;
        bel = $(b.id || '#c1searchButtonHighlight');
        if (bel.length) {
            (function(btn, hoptions) {
                function checkHighlight(val, onlyIndicate) {
                    val = val == undefined ? hoptions.disabled : !!val;
                    btn.css('opacity', val ? 1 : 0.3);
                    hoptions.disabled = !val;
                    !onlyIndicate && search.highlight(val);
                }
                checkHighlight(!hoptions.disabled, true);
                btn.click(function() { checkHighlight(); });
            })(bel, options.highlight || {});
            setButton(bel, b.label, b.icon);
            search.buttonHighlight = bel;
        }

        search.buttons = bset = $(bset);
    }
});

})(jQuery, nethelp, nethelpshell);