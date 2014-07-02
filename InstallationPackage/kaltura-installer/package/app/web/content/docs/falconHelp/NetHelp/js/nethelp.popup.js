(function($, core, undefined) {

var c_popup = 'c1-popup',
    c_popupHtml = c_popup + '-html',
    c_container = 'c1-popup-position-container',
    qname = 'popup-tasks';

var Popup = core.widget('popup', {
    options: {
        autoShow: true,
        popupClass: 'ui-corner-all ui-state-highlight',
        popupCss: {
            zIndex: 500
        },
        popupHtmlCss: {
            padding: 5
        },
        html: '',
        position: {}
    },
    //popup: undefined,     // will be defined in instance
    //popupHtml: undefined, // will be defined in instance
    _create: function() {
        var self = this,
            element = self.element,
            options = self.options,
            css = options.popupCss,
            e = options.event,
            body = element.closest('body'),
            t = element.closest('.' + c_popup),
            popup, popupHtml;
        options.event = undefined;
        if (t[0]) {
            css.zIndex = Math.max(css.zIndex, (parseInt(t.css('zIndex'), 10) || 0) + 1);
        }
        self.popup = popup = $('<div/>').hide()
            .appendTo(body)
            .addClass(c_popup)
            .addClass(options.popupClass)
            .css(css || {})
            .css('overflow', 'auto')
            .data('popup', self);
        self.popupHtml = popupHtml = $('<div/>')
            .appendTo(popup)
            .addClass(c_popupHtml)
            .css(options.popupHtmlCss || {});
        if (options.html) {
            popupHtml.html(options.html);
        }
        self.hide(); // reset state
        if (options.autoShow) {
            self.show(e);
        }
    },
    destroy: function() {
        this.popup.hide().remove();
    },
    isVisible: function() {
        return this.popup.css('display') !== 'none';
    },
    state: function() {
        return this._state;
    },
    show: function(event, reposition) {
        if (event != undefined && !event.type) {
            reposition = event;
            event = undefined;
        }
        var self = this;
        if (!reposition && self.isVisible()) {
            core.accessibilityFocusTo(self.popup);
            return;
        }
        self._state = 'showing';
        Popup.position(self.element, self.popup, $.extend({}, self.options.position, {
            visible: true,
            event: event,
            callback: function() {
                self._onshown.resolve();
                self._state = true;
                core.accessibilityFocusTo(self.popup);
            }
        }));
        return self;
    },
    hide: function() {
        var self = this;
        self.popup.hide();
        self._state = false;
        self._onshown = $.Deferred();
        return self;
    },
    toggle: function(event) {
        return this[this.isVisible() ? 'hide' : 'show'](event);
    },
    html: function(html) {
        var el = self.popupHtml;
        if (!arguments.length) {
            return el.html();
        }
        el.html(html);
        return self;
    },
    onShow: function(fn) {
        if (!this._onshown) {
            this._onshown = $.Deferred();
        }
        this._onshown.done(fn);
    }
});

$.extend(Popup, {
    positionOptions: {
        minHeight: 0,
        minWidth: 300,
        maxHeight: 400,
        maxWidth: 600
    },
    position: function(elem, popup, options) {
        /* required:
            elem = $(elem);
            popup = $(popup);
        */
        options = $.extend(true, {}, Popup.positionOptions, options);
        var t = elem[0].ownerDocument,
            win = $(t.defaultView || t.parentWindow),
            doc = $(win[0].document),
            body = elem.closest('body'),
            winH = Math.max(win.height(), doc.height()),
            winW = Math.max(win.width(), doc.width()),
            event = options.event,
            callback = options.callback,
            area = {},
            pos = {},
            visible = 'visible' in options ? options.visible :
                popup.css('display') !== 'none',
            res,
            container = $('.' + c_container, body);
        if (!container.length) {
            container = $('<div/>')
                .addClass(c_container)
                .css({
                    position: 'absolute',
                    left: 0,
                    top: '-101%',
                    width: '99%',
                    height: '99%',
                    overflow: 'hidden'
                })
                .appendTo(body);
        }
        container.append(popup);
        function complete() {
            body.append(popup);
            if ($.isFunction(callback)) {
                callback();
            }
        }
        function addTasks() {
            $.each($.makeArray(arguments), function(i, task) {
                elem.delay(10, qname).queue(qname, function(n) {
                    if (res !== false) {
                        res = task();
                    }
                    n();
                });
            });
        }
        function checkPos() {
            if (pos.top >= 0 && pos.left >= 0 && 
                    pos.top + pos.height <= winH && pos.left + pos.width <= winW) {
                popup.hide().css({
                    top: pos.top,
                    left: pos.left,
                    visibility: 'visible',
                    display: visible ? 'block' : 'none'
                });
                complete();
                return true;
            }
            popup.hide();
            return false;
        }

        if (event && 'pageX' in event) {
            area.left = area.right = event.pageX;
            t = parseInt(elem.css('fontSize')) || 0;
            area.top = event.pageY - t;
            area.bottom = event.pageY + t;
        }
        else {
            t = elem.offset();
            area.left = t.left;
            area.top = t.top;
            area.right = t.left + elem.outerWidth();
            area.bottom = t.top + elem.outerHeight();
        }

        //#region tasks
        addTasks(function() {
            var w = Math.min(Math.max(winW - area.left - 5, options.minWidth), options.maxWidth, winW - 5),
                h = Math.min(winH - 5, options.maxHeight);
            popup.hide().css({
                visibility: 'hidden',
                position: 'absolute',
                top: -100,
                left: -100,
                margin: 0,
                padding: 0,
                width: 'auto',
                height: 'auto',
                maxWidth: w,
                maxHeight: h
            }).show();
        }, function() {
            pos.top = area.bottom;
            pos.left = area.left;
            pos.height = popup.height();
            pos.width = popup.width();
            if (checkPos()) {
                return false;
            }
            pos.top = area.top - pos.height;
            if (checkPos()) {
                return false;
            }
        }, function() {
            var max = Math.min(winW, options.maxWidth), c = 0;
            popup.css({
                maxWidth: max
            });
            function itsMax() {
                var ontop = area.top > (winH - area.bottom),
                    mh = ontop ? area.top : winH - area.bottom - 5;
                popup.css({
                    width: pos.width,
                    maxHeight: mh,
                    height: 'auto'
                });
                addTasks(function() {
                    popup.css({
                        top: ontop ? 0 : area.bottom,
                        left: Math.min(area.left, winW - pos.width - 5),
                        height: popup.height() > mh ? mh : 'auto',
                        visibility: 'visible',
                        display: visible ? 'block' : 'none'
                    });
                    complete();
                });
            }
            function step() {
                if (++c > 10000) {
                    core.error('Popup.position: infinite recursion');
                    return;
                }
                var w = pos.width + 10;
                if (w > max) {
                    itsMax();
                    return;
                }
                popup.hide().css({
                    visibility: 'hidden',
                    width: w
                }).show();
                addTasks(function() {
                    pos.height = popup.height() + 5;
                    pos.width = popup.width();
                    pos.top = area.bottom;
                    pos.left = winW - pos.width - 5;
                    if (checkPos()) {
                        return false;
                    }
                    pos.top = area.top - pos.height;
                    pos.height -= 5;
                    if (checkPos()) {
                        return false;
                    }
                    step();
                });
            };
            step();
        });
        elem.dequeue(qname);
        //#endregion
    },
    globalEvent: function(e) {
        var target = e.target;
        $('.' + c_popup + ':visible', this).each(function(i, popup) {
            if (target != popup && !$.contains(popup, target)) {
                var w = $(popup).data('popup'), wel = w.element[0];
                if (target != wel && !$.contains(wel, target)) {
                    w.hide();
                }
            }
        });
    }
});

$(function() {
    $('body').bind('mousedown keydown', function(e) {
        if (e.type === 'mousedown' || e.which === 13) {
            Popup.globalEvent.call(this, e);
        }
        else if (e.which === 27) {
            Popup.globalEvent.call(this, {});
        }
    });
});

})(jQuery, nethelp);