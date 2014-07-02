(function($, core, undefined) {

var p = 'c1-splitter',
    c_side = p + '-side',
    c_main = p + '-main',
    c_overlay = p + '-overlay',
    c_button = 'splitter-button',
    c_btn_show = c_button + '-show',
    c_btn_hide = c_button + '-hide';

var Splitter = core.widget('splitter', {
    options: {
        rightSide: false,
        distance: 10,
        overlayCss: { opacity: 0.1, zIndex: 99 },
        leftMargin: 100,
        rightMargin: 100,
        dblclick: true,
        keyboardControl: true,
        keyboardStep: 25,
        icons: {
            splitter: 'ui-icon ui-icon-grip-dotted-vertical',
            sideShow: 'ui-icon ui-icon-triangle-1-e',
            sideHide: 'ui-icon ui-icon-triangle-1-w',
            invertForRightSide: true
        },
        sideShowTooltip: 'Show side panel',
        sideHideTooltip: 'Hide side panel',
        position: undefined,
        // panels
        side: undefined, // auto
        main: undefined  // auto
    },
    _create: function() {
        var self = this,
            options = self.options,
            icons = options.icons || {},
            rtl = options.rightSide = !!options.rightSide,
            invert = rtl && icons.invertForRightSide,
            splitter = self.splitter = self.element,
            parent = self.parent = splitter.parent(),
            button = self.button = splitter.find('.' + c_button),
            splitterAndButton = splitter.add(button).addClass('ui-state-default'),
            icon = self.icon = splitter.find('.splitter-icon')
                .addClass(icons.splitter),
            iconShow = self.iconShow = splitter.find('.splitter-icon-show')
                .addClass(icons[invert ? 'sideHide' : 'sideShow']),
            iconHide = self.iconHide = splitter.find('.splitter-icon-hide')
                .addClass(icons[invert ? 'sideShow' : 'sideHide']),
            side = self.side = (options.side ? $(options.side) : 
                    splitter[rtl ? 'next' : 'prev']())
                .addClass(c_side),
            main = self.main = (options.main ? $(options.main) : 
                    splitter[rtl ? 'prev' : 'next']())
                .addClass(c_main)
                .prependTo(parent),
            overlay = self.overlay = $('<div class="' + c_overlay + ' ui-widget-overlay" />')
                .css(options.overlayCss || {})
                .hide()
                .appendTo(parent),
            width = splitter.width(),
            left = splitter.position().left,
            right = parent.width() - left,
            dragging = false,
            checkX = rtl ? function(x) {
                    return x < parent.width() - width;
                } : function(x) {
                    return x > width;
                },
            _pos,
            sw = splitter.addClass(p).appendTo(parent)
                .css('position', 'absolute') // otherwise draggable would set position to 'relative'
                .draggable({
                    axis: 'x',
                    distance: options.distance,
                    start: function() {
                        var w = parent.width();
                        dragging = true;
                        splitterAndButton.addClass('ui-state-hover');
                        if (rtl) {
                            splitter.css({ right: 'auto', left: w - self.x });
                        }
                        overlay.show();
                        sw.containment = sw.options.containment = 
                            [ options.leftMargin, 0, w - options.rightMargin, 0 ];
                    },
                    drag: function(e) {
                        self[checkX(e.pageX) ? 'showSide' : 'hideSide']();
                    },
                    stop: function() {
                        setTimeout(function() { dragging = false; }, 50);
                        overlay.hide();
                        if (self._hidden) {
                            self.hideSide(true);
                        }
                        else {
                            var left = splitter.position().left,
                                x = self.x = rtl ? parent.width() - left : left;
                            self.position(x);
                        }
                    }
                }).data('draggable');
        if (rtl && button.hasClass('ui-corner-right') && !button.hasClass('ui-corner-left')) {
            button.removeClass('ui-corner-right').addClass('ui-corner-left');
        }
        _pos = self._pos = {
            side: rtl ?
                function(x) { return { width: x - width }; } :
                function(x) { return { width: x }; },
            main: rtl ?
                function(x) { return { right: x }; } :
                function(x) { return { left: x + width }; },
            splitter: rtl ?
                function(x) { return { left: 'auto', right: x - width }; } :
                function(x) { return { left: x }; },
            sideHideSplitter: rtl ? 
                { left: 'auto', right: 0 } : 
                { left: 0 },
            sideHideMain: rtl ? 
                { right: width } : 
                { left: width }
        };
        _pos.sideShowMain = _pos.main;
        self.x = rtl ? right : left;
        splitter.bind('mouseenter focusin', function() {
            splitterAndButton.addClass('ui-state-hover');
        }).bind('mouseleave focusout', function() {
            dragging || splitterAndButton.removeClass('ui-state-hover');
        });
        button.addClass(c_button)
            .addClass(c_btn_hide)
            .click(function() { dragging || self.toggleSide(); });
        options.dblclick && splitter.dblclick(function() { self.toggleSide(); });
        options.sideShowTooltip && iconShow.attr('title', options.sideShowTooltip);
        options.sideHideTooltip && iconHide.attr('title', options.sideHideTooltip);
        if (options.keyboardControl) {
            splitter.attr('tabindex', 0)
                .keydown(function(e) {
                    switch(e.which) {
                        case 37:
                            self.position((rtl ? '+=' : '-=') + options.keyboardStep);
                            break;
                        case 39:
                            self.position((rtl ? '-=' : '+=') + options.keyboardStep);
                            break;
                        case 13:
                        case 32:
                            self.toggleSide();
                            break;
                    }
                });
        }
        var position = parseInt(options.position, 10);
        !isNaN(position) && self.position(position);
    },
    hideSide: function(reset) {
        var self = this;
        if (reset !== true && self._hidden) {
            return;
        }
        var _pos = self._pos,
            btn = self.button;
        self.side.hide();
        self.splitter.css(_pos.sideHideSplitter);
        self.main.css(_pos.sideHideMain);
        if (btn) {
            btn.removeClass(c_btn_hide).addClass(c_btn_show);
        }
        self._hidden = true;
    },
    showSide: function(reset) {
        var self = this;
        if (reset !== true && !self._hidden) {
            return;
        }
        var _pos = self._pos,
            btn = self.button,
            x = self.x;
        self.main.css(_pos.sideShowMain(x));
        self.splitter.css(_pos.splitter(x));
        self.side.show();
        if (btn) {
            btn.removeClass(c_btn_show).addClass(c_btn_hide);
        }
        self._hidden = false;
    },
    toggleSide: function(show) {
        var self = this;
        self[(show == undefined ? self._hidden : show) ? 'showSide' : 'hideSide']();
    },
    position: function(x) {
        var self = this,
            options = self.options;
        if (x == undefined) {
            return self.x;
        }
        var diff = /^(\+|-)=(\d+)$/.exec(x);
        if (diff) {
            diff = +(diff[2]) * (diff[1] === '-' ? -1 : 1);
            x = self.x + diff;
        }
        if (!isNaN(x = +x)) {
            if (x > 0) {
                /* In LOCAL IE8 sometimes self.parent.width() can be 0 on widget initialization.
                x = Math.max(options.leftMargin, Math.min(self.parent.width() - options.rightMargin, x)); */
                x = Math.max(options.leftMargin, x);
                var _pos = self._pos;
                self.side.show().css(_pos.side(x));
                self.main.css(_pos.main(x));
                self.splitter.css(_pos.splitter(x));
                self.trigger('splitter');
                self.x = x;
                self._hidden = false;
            }
            else {
                self.hideSide();
            }
        }
    }
});

Splitter.settings2options = function(stgs) {
    return core.extend({},
        stgs,
        stgs.strings,
        { strings: null });
};

})(jQuery, nethelp);