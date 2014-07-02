(function($, core, shell, undefined) {

// default button options
shell.mergeSettings({
    buttons: {
        prev: {
            id: 'c1buttonPrev',
            showLabel: false,
            label: undefined, // from layout
            icon: 'ui-icon-circle-arrow-w',
            click: 'this.gotoPrev(e);'
        },
        next: {
            id: 'c1buttonNext',
            showLabel: false,
            label: undefined, // from layout
            icon: 'ui-icon-circle-arrow-e',
            click: 'this.gotoNext(e);'
        },
        home: {
            id: 'c1buttonHome',
            showLabel: false,
            label: undefined, // from layout
            icon: 'ui-icon-home',
            click: 'this.gotoHome(e);'
        },
        print: {
            id: 'c1buttonPrint',
            showLabel: false,
            label: undefined, // from layout
            icon: 'ui-icon-print',
            click: 'this.print();'
        },
        email: {
            id: 'c1buttonEmail',
            showLabel: false,
            label: undefined, // from layout
            icon: 'ui-icon-mail-closed',
            click: 'location.href = this.mailtoUrl();'
        },
        poweredBy: {
            id: 'c1buttonPoweredBy',
            showLabel: false,
            label: undefined, // from layout
            icon: 'ui-icon-power'
        }
    }
});

shell.driver({
    name: 'buttons',
    create: function() {
        var buttons = {};
        shell.buttons = buttons;
        core.each(shell.settings.buttons || [], function(b, name) {
            var sel = core.isObject(b) ? b.id ? '#' + b.id : b.selector : undefined,
                bel = sel && $(sel),
                f;
            if (bel && bel.length) {
                if (b.visible || b.visible == undefined) {
                    bel.show();
                    buttons[name] = bel.button({
                        text: b.showLabel !== false && b.label !== '',
                        label: b.label,
                        icons: { primary: b.icon, secondary: b.icon2 }
                    });
                    if (b.label === '') {
                        bel.find('.ui-button-text').html('&nbsp;');
                    }
                    f = b.click;
                    f = core.isFunction(f) ? f :
                        core.isString(f) ? Function('e', 'target', 'options', f) :
                        undefined;
                    f && bel.click(function(e) { return f.call(shell, e, this, b); });
                }
                else {
                    bel.remove();
                }
            }
        });
        // fix for the browser default behavior: focus out from button after click
        $('body').delegate('.ui-button', 'mouseup', function() { $(this).blur(); });
    }
});

})(jQuery, nethelp, nethelpshell);