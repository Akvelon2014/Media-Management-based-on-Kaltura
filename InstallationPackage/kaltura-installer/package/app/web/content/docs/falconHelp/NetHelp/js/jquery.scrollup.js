(function($, undefined) {

var states = { auto: 1, scroll: 1 },
    defOptions = { position: 'top', always: true, margin: 0 };

$.fn.scrollup = function(options) {
    if (!this[0]) {
        return;
    }
    options = $.extend({}, defOptions, options);
    var el = $(this[0]),
        eltop = el.offset().top,
        elheight = el.outerHeight(),
        position = options.position,
        p = options.parent,
        ptop, pheight,
        diff,
        origDisplay = el.css('display');
    if (!eltop && origDisplay === 'inline') {
        origDisplay = function() {
            el.css('display', 'inline');
        };
        el.css('display', 'inline-block');
        eltop = el.offset().top;
    }
    else {
        origDisplay = $.noop;
    }
    if (!p || !p.length) {
        p = el.parents().filter(function() {
            return !!states[$(this).css('overflow-y')];
        }).first();
    }
    if (p.length) {
        ptop = p.offset().top;
    }
    else {
        p = el[0].ownerDocument;
        p = $(p.parentWindow || p.defaultView);
        ptop = 0;
    }
    pheight = p.height();
    diff = eltop - ptop;
    if (elheight >= pheight && position.charAt(position.length - 1) !== '!') {
        options.always = true;
    }
    else {
        var margin = Math.min(options.margin, Math.max(0, pheight - elheight));
        switch(position.split('!')[0]) {
            case 'top':
                diff -= margin;
                break;
            case 'bottom':
                diff += elheight - pheight + margin;
                break;
            case 'center':
                diff += (elheight - pheight) / 2;
                break;
            case 'auto':
                if (eltop > ptop && elheight < pheight) {
                    diff += elheight - pheight;
                    if (diff <= 0) {
                        diff = false;
                    }
                }
                break;
        }
    }
    if (diff && (options.always || !(eltop > ptop && (eltop + elheight) < (ptop + pheight)))) {
        if (options.animation) {
            p.animate({ scrollTop: p.scrollTop() + diff }, options.animation);
        }
        else {
            p.scrollTop(p.scrollTop() + diff);
        }
    }
    origDisplay();
    return this;
};

})(jQuery);