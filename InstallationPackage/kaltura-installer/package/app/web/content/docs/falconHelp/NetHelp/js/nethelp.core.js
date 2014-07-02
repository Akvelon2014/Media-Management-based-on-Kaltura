(function($, core, undefined) {

//#region jQuery extensions
if (!$.isFunction($.fn.hashchange)) {
    $.fn.hashchange = function(handler) {
        return handler ? this.bind('hashchange', handler) : this.trigger('hashchange');
    };
}
$.fn.findAnchor = function(anchor) {
    if (anchor.charAt(0) === '#') {
        anchor = anchor.substring(1);
    }
    var res = this.find('a[name="' + anchor + '"]');
    if (!res.length) {
        res = this.find('#' + anchor);
    }
    return res.first();
};
//#region event queue
var eventProp = 'queue';
$.event.props.push(eventProp);
function initEventProp(e) {
    return e[eventProp] || (e[eventProp] = {});
}
$.extend($.Event.prototype, {
    handled: function(type) {
        var e = this,
            queue = initEventProp(e),
            d = queue['handle' + type];
        queue['handled' + type] = true;
        if (d) {
            d.resolveWith(e, e);
        }
    },
    isHandled: function(type, setter) {
        var e = this,
            queue = initEventProp(e),
            result;
        if (typeof type !== 'string') {
            setter = arguments.length ? type : true;
            type = e.type;
        }
        result = !!queue['handled' + type];
        if (setter && !result) {
            e.handled(type);
        }
        return result;
    },
    onHandle: function(type, fn) {
        var e = this,
            queue = initEventProp(e);
        if (type && $.isFunction(fn)) {
            if (e.isHandled(type)) {
                fn.call(e, e);
            }
            else {
                type = 'handle' + type;
                (queue[type] || (queue[type] = $.Deferred())).done(fn);
            }
        }
    }
});
//#endregion
//#endregion

var slice = Array.prototype.slice;

core = window.nethelp = $.nethelp = $.extend($.nethelp || {}, {
    version: '1.1',
    
    str: function(val, ifundef) {
        return val === undefined ? ifundef : String(val);
    },
    call: function(callbacks, context /* , args */) {
        if (!callbacks) {
            return;
        }
        if ($.isFunction(callbacks)) {
            callbacks = [ callbacks ];
        }
        var args, res = $.isArray(callbacks) ? [] : {};
        args = arguments.length > 2 ? core.slice(arguments, 2) : [];
        $.each(callbacks, function(i, fn) {
            if ($.isFunction(fn)) {
                res[i] = fn.apply(context, args);
            }
        });
        return res;
    },
    concat: function(v1, v2 /* , ... */) {
        var res = [],
            arg;
        for (var i = 0, l = arguments.length; i < l; ++i) {
            if ((arg = arguments[i]) != undefined) {
                res = res.concat(arg);
            }
        }
        return res;
    },
    slice: function(array, start, end) {
        return slice.apply(array, slice.call(arguments, 1));
    },
    error: function(msg) {
        window.console && console.error('nethelp-error: ' + msg);
    },
    limitCall: function(key, limit, time) {
        var rkey = '__recursion' + key,
            r = this[rkey];
        if (!r) {
            this[rkey] = r = {};
        }
        if ((r.count || 0) > (limit || 1000)) {
            this.error('recursion limit: ' + key);
        }
        var now = $.now();
        if ((r.start || 0) - now < (time || 3000)) {
            r.count = 0;
            r.start = now;
        }
    },
    Event: function(type, originalEvent) {
        var event = $.Event(originalEvent);
        // Copy original event properties over to the new event.
        // This would happen if we could call $.event.fix instead of $.Event
        // but we don't have a way to force an event to be fixed multiple times
        if (originalEvent) {
            for (var t = $.event.props, i = t.length, prop; i; ) {
                prop = t[--i];
                event[prop] = originalEvent[prop];
            }
        }
        event.type = type || 'unknown';
        initEventProp(event);
        return event;
    },
    px2em: function(el, px) {
        var t;
        if (px.jquery || px.nodeType) {
            t = el;
            el = px;
            px = t;
        }
        if (typeof px === 'string') {
            px = parseFloat(px);
        }
        if (isNaN(px)) {
            return 0;
        }
        if (el.nodeType) {
            el = $(el);
        }
        return Math.round(px * 10 / parseFloat(el.css('font-size'))) / 10 + 'em';
    },

    //#region Tools
    each: function(list, fn, context) {
        $.each(list, function(key, val) {
            fn.call(context, val, key);
        });
    },
    map: $.map,
    extend: $.extend,

    inherit: function(heir, base) {
        var F = function() {};
        F.prototype = base.prototype;
        heir.prototype = new F();
        heir.prototype.constructor = base;
        heir.prototype.base = base.prototype;
    },

    trim: $.trim,

    type: $.type,
    isString: function(obj) {
        return core.type(obj) === 'string';
    },
    isBoolean: function(obj) {
        return core.type(obj) === 'boolean';
    },
    isNumber: function(obj) {
        return core.type(obj) === 'number';
    },
    isObject: function(obj) {
        return core.type(obj) === 'object';
    },
    isArray: $.isArray,
    isLikeArray: function(obj) {
        var t = core.type(obj);
        return !!obj &&
            t !== 'function' && t !== 'string' &&
            obj.hasOwnProperty('length') && obj.length > -1;
    },
    isFunction: $.isFunction,
    isPlainObject: $.isPlainObject,

    noop: function() {},
    proxy: $.proxy,
    onlyFunction: function(fn) {
        for (var i = 0, l = arguments.length; i < l; ++i) {
            fn = arguments[i];
            if (core.isFunction(fn)) {
                return fn;
            }
        }
        return core.noop;
    },

    quickSearch: function(list, check, start, end) {
        /*
            check: function(item) =>
               -1: item < searched
                1: item > searched
                0: item is searched
            return > -1 (index), -1 (not found), < -1 (error code)
        */
        if (!list || !list.length || !core.isFunction(check)) {
            return -1;
        }
        if (start == undefined) {
            start = 0;
            end = list.length - 1;
        }
        var i, r, l = list.length;
        while (l-- && start < end) {
            i = parseInt((start + end) / 2);
            r = check(list[i]);
            if (r === -1) {
                start = i + 1;
            }
            else if (r === 1) {
                end = i - 1;
            }
            else if (r === 0) {
                return i;
            }
            else {
                return -Math.abs(r); // error code
            }
        }
        return start === end && check(list[start]) === 0 ? start : -1;
    },
    //#endregion

    templateSettings: {
        code: /<#([\s\S]+?)#>/g,
        prop: /#{([\s\S]+?)}/g
    },
    template: function (str, data, options) {
        options = core.extend({}, core.templateSettings, options);
        var fn = core.isString(str) ? new Function('__d', 
            "var __p=[],print=function(){__p.push.apply(__p,arguments);};" +
            "with(__d||{}){__p.push('" +
            str.replace(/\\/g, "\\\\")
                .replace(/'/g, "\\'")
                .replace(options.prop, function (match, code) {
                    return "'," + code.replace(/\\'/g, "'") + ",'";
                })
                .replace(options.code || null, function (match, code) {
                    return "');" + code.replace(/\\'/g, "'")
                        .replace(/[\r\n\t]/g, ' ') + "__p.push('";
                })
                .replace(/\r/g, '\\r')
                .replace(/\n/g, '\\n')
                .replace(/\t/g, '\\t') + 
                "');}return __p.join('');") : core.isFunction(str) ? str : undefined;
        options.template = fn;
        return fn && (data != undefined ? fn.call(data, data) :
            fn.template ? fn :
            core.extend(function(d) {
                if (d == undefined) d = {};
                return fn.call(d, d);
            }, {
                template: fn,
                templateString: str
            }));
    },

    //#region include script
    includeScript: function(url, callback) {
        var type = 'text/javascript',
            async = true,
            charset;
        if (core.isObject(url)) {
            type = url.type || type,
            async = url.async !== false; // default: true
            charset = url.charset;
            callback = url.callback || url.success;
            url = url.url;
        }
        if (!url || !core.isString(url)) {
            // core.error();
            return;
        }
        var d = $.Deferred(),
            head = document.head || document.getElementsByTagName('head')[0] || document.documentElement,
            script = document.createElement('script');
        if (async) {
            script.async = 'async';
        }
        if (charset) {
            script.charset = charset;
        }
        script.type = type;
        script.src = url;
        script.onload = script.onreadystatechange = function(_, isAbort) {
            if (isAbort || !script.readyState || /loaded|complete/.test(script.readyState)) {
                script.onload = script.onreadystatechange = null;
                if (isAbort && head && script.parentNode) {
                    head.removeChild(script);
                }
                script = undefined;
                d[isAbort ? 'reject' : 'resolve']();
            }
        };
        d.done(callback);
        head.appendChild(script);
        return d.promise({
            abort: function() {
                script && script.onload(0, 1);
            }
        });
    },
    //#endregion

    //#region accessibility
    accessibility: false,
    accessibilityFocusTo: function(el) {
        if (!el || !core.accessibility) {
            return el;
        }
        el = $(el);
        if (el.attr('tabindex') == undefined) {
            el.attr('tabindex', -1);
        }
        el.is(':visible') && el.focus();
        return el;
    },
    //#endregion

    debug: function() {}
});

//#region Flags

var isFlags;
var Flags = core.Flags = function(s, mask) {
    var a, i;
    if (isFlags(s)) {
        return s;
    }
    if (!isFlags(this)) {
        return new Flags(s);
    }
    if (core.isString(s)) {
        a = core.trim(s).split(/\s+/);
        for (i in a) {
            this[a[i]] = true;
        }
    }
    else if (core.isPlainObject(s)) {
        for (i in s) {
            this[i] = mask ? s[i] !== undefined : !!s[i];
        }
    }
};
isFlags = core.isFlags = function(obj) {
    return obj instanceof Flags;
};

//#endregion

//#region StringBuilder
function StringBuilder(str) {
    if (!(this instanceof StringBuilder)) {
        return new StringBuilder(str);
    }
    this._s = [];
    if (str != null) {
        this.append(str);
    }
}
core.extend(StringBuilder.prototype, {
    lineBreak: '\n',
    add: function(str) {
        for (var i = 0, l = arguments.length, s; i < l; ++i) {
            if ((s = arguments[i]) != null) {
                this._s.push(s);
            }
        }
        return this;
    },
    addLine: function(str) {
        for (var i = 0, l = arguments.length, s; i < l; ++i) {
            if ((s = arguments[i]) != null) {
                this._s.push(s);
                this._s.push(this.lineBreak);
            }
        }
        return this;
    },
    empty: function() {
        this._s = [];
        return this;
    },
    cancel: function() {
        this._s.pop();
        return this;
    },
    last: function() {
        var s = this._s;
        return s[s.length - 1];
    },
    getString: function() {
        return this._s.join('');
    }
});
StringBuilder.prototype.toString = function() {
    return this.getString();
}
core.StringBuilder = StringBuilder;
//#endregion

//#region href/url/attr utilities
var el1 = $('<p><br /></p>'),
    el11 = $('br', el1),
    urlAttrs = {
        A: 'href',
        AREA: 'href',
        LINK: 'href',
        IMG: 'src',
        SCRIPT: 'src',
        INPUT: 'src'
    },
    a = document.createElement('a'),
    $a = $(a),
    rePrototcol = /^\w+:/,
    root,
    rootlen;

a.href = '.';
var hrefExpanded = $.support.hrefExpanded = (a.href !== '.');
root = (hrefExpanded ? a.href : a.getAttribute('href', 4)).toLowerCase();
rootlen = root.length;

function isAbsoluteUrl(url) {
    return rePrototcol.test(url);
}
function expandUrl(url) {
    a.href = url || '';
    return hrefExpanded ? a.href : a.getAttribute('href', 4);
}
function normalizeUrl(url, abs) {
    var s = expandUrl(url);
    return abs ? s : s.toLowerCase().indexOf(root) === 0 ? s.substring(rootlen) : url;
}
//#region quick access to url-attributes
$.fn.url = function(value) {
    if (arguments.length) {
        return this.each(function() {
            var attr = urlAttrs[this.nodeName.toUpperCase()];
            if (attr) {
                this.setAttribute(attr, value === true ? this[attr] : value);
            }
        });
    }
    var el = this[0],
        attr = urlAttrs[el.nodeName.toUpperCase()];
    return attr && (hrefExpanded ? el[attr] : el.getAttribute(attr, 4));
};
//#endregion

$.extend(core, {
    urlAttrs: urlAttrs,
    escapeAttr: function(text) {
        el11.attr('a', text);
        return /a="([^"]*)"/.exec(el1.html())[1];
    },
    expandUrl: expandUrl,
    normalizeUrl: normalizeUrl,
    getUrlPath: function(url) {
        return (/(.*\/)[^\/]*$/.exec(normalizeUrl(url.split('?')[0])) || [])[1] || '';
    },
    addUrlSearch: function(url, add) {
        if (!add) {
            return url;
        }
        var m = url && /^([^?#]*)(\?[^#]*)?(#.*)?$/.exec(url) || [],
            q = m[2] || '?';
        if (!RegExp('\\b' + add.replace(/([.?*+^$[\]\\(){}-])/g, "\\$1") + '\\b').test(q)) {
            if (q.length > 1) {
                q += '&';
            }
            q += add;
        }
        return m[0] ? 
            (m[1] || '') + q + (m[3] || '') :
            url;
    },
    isAbsoluteUrl: isAbsoluteUrl,
    normalizeHref: function(link, spec) {
        if (!spec && $.support.hrefNormalized) {
            return link.jquery ? link.attr('href') : link.nodeType ? $(link).attr('href') : link;
        }
        // For dynamically created HTML that contains a relative url as href, IE < 8 expands such href to a full page url.
        // So fix the href attribute:
        var href;
        if (typeof link === 'string') {
            href = link;
            link = $a;
        }
        else {
            if (link.jquery) {
                href = link.attr('href');
                link = link.length > 1 ? link.eq(0) : link;
            }
            else if (link.nodeType) {
                link = $(link);
                href = link.attr('href');
            }
        }
        if (!href) {
            return;
        }
        href = normalizeUrl(href);
        link.attr('href', href);
        return href;
    },
    replaceUrl: function(el, attr, path, spec) {
        var url;
        if (typeof el === 'string') {
            spec = path;
            path = attr;
            url = el;
            el = attr = undefined;
        }
        else {
            el = $(el);
            url = el.attr(attr);
        }
        if (url && url.charAt(0) !== '#' && !isAbsoluteUrl(url)) {
            url = (spec || '') + normalizeUrl(path + url);
            el && el.attr(attr, url);
            return url;
        }
    },
    replaceUrls: function(el, map, path, spec) {
        /*
            map: [
                [ "selector of elements", "attribute" ],
                ...
            ]
        */
        var self = this;
        el = el.jquery ? el : $(el);
        if ($.isArray(map)) {
            $.each(map, function(i, p) {
                var s = p[0];
                ($.isFunction(s) ? s(el) : el.find(p[0]))
                    .not('.dont-replace-link')
                    .not(p[2])
                    .each(function() {
                        self.replaceUrl(this, p[1], path, spec);
                    });
            });
        }
        else if (typeof map === 'string') {
            el.each(function() {
                self.replaceUrl(this, map, path, spec);
            });
        }
    }
});
//#endregion

//#region virtual doc
$.extend(core, {
    escapeJSNode: function(node, verified) {
        if (verified || (node && node.nodeName.toUpperCase() === 'SCRIPT' && (!node.type || node.type === 'text/javascript'))) {
            node.setAttribute('type', '_js');
            if (node.src) {
                node.setAttribute('js_src', node.src);
                node.src = undefined;
                node.removeAttribute('src');
            }
        }
        return node;
    },
    unescapeJSNode: function(node, verified) {
        if (verified || (node && node.nodeName.toUpperCase() === 'SCRIPT' && node.type === '_js')) {
            node.setAttribute('type', 'text/javascript');
            for (var attrs = node.attributes, len = attrs.length, attr, i = 0; i < len; ++i) {
                attr = attrs[i];
                if (attr.specified && attr.name.toLowerCase() === 'js_src') {
                    node.setAttribute('src', attr.value);
                    if ('js_src' in node) {
                        node.js_src = undefined;
                    }
                    node.removeAttribute('js_src');
                    break;
                }
            }
        }
        return node;
    },
    escapeScripts: function(html) {
        var self = this,
            escNode = function() { self.escapeJSNode(this); };
        if (typeof html === 'string') {
            return html.replace(/clsid:D27CDB6E-AE6D-11CF-96B8-444553540000/gi, '_$&').replace(/<script(\s+[^>]*)?>/gi, function (s, attrs) {
                var typeReplaced = false;
                if (attrs) {
                    attrs = attrs.replace(/(\w+)(?:\s*=\s*(?:(?:"((?:\\.|[^"])*)")|(?:'((?:\\.|[^'])*)')|([^>\s]+)))?/g, function(match, name) {
                        var value = arguments[2] || arguments[3] || arguments[4];
                        switch (name.toLowerCase()) {
                            case 'type':
                                typeReplaced = true;
                                if (!value || value.toLowerCase() === 'text/javascript') {
                                    return 'type="_js"';
                                }
                                break;
                            case 'src':
                                return 'js_' + match;
                        }
                        return match;
                    });
                }
                return '<script' + (attrs || '') + (typeReplaced ? '' : ' type="_js"') + '>';
            });
        }
        if (html.jquery && html.length) {
            if (html[0].nodeName.toUpperCase() === 'SCRIPT') {
                html.each(escNode);
            }
            else {
                html.find('script').each(escNode);
            }
            return html;
        }
        if (html.nodeType) {
            return self.escapeJSNode(html);
        }
        if ($.isArray(html) && html.length) {
            $.each(html, escNode);
            return html;
        }
    },
    unescapeScripts: function(html) {
        var self = this,
            unescNode = function() { self.unescapeJSNode(this); };
        if (html.jquery && html.length) {
            if (html[0].nodeName.toUpperCase() === 'SCRIPT') {
                html.each(unescNode);
            }
            else {
                html.find('script').each(unescNode);
            }
            return html;
        }
        if (typeof html === 'string') {
            return html
                .replace(/_(clsid\:D27CDB6E-AE6D-11CF-96B8-444553540000)/gi, '$1')
                .replace(/<script([^>]*)?\stype="?_js\b"?([^>]*)?>/gi, '<script$1 type="text/javascript"$2>')
                .replace(/<script([^>]*)?\sjs_src=([^>]*)?>/gi, '<script$1 src=$2>');
        }
        if (html.nodeType) {
            return self.unescapeJSNode(html);
        }
        if ($.isArray(html) && html.length) {
            $.each(html, unescNode);
            return html;
        }
    },

    copyNode: function(node, options) {
        /*
            options: {
                nativeImport: boolean (default false)
                tag: string,
                content: string|false (default "innerHtml" (for create) or true (for import))
                attrs: boolean (default true)
            }
        */
        options = options || {};
        var doc = options.document || document,
            content = options.content;
        if (options.nativeImport && doc.importNode) {
            return doc.importNode(node, content !== false);
        }
        var t = options.tag || node.nodeName,
            newnode = doc.createElement(t);
        if (options.attrs !== false) {
            for (var attrs = node.attributes, i = attrs.length, attr; i--; ) {
                attr = attrs[i];
                if (attr.specified) {
                    newnode.setAttribute(attr.name, attr.value);
                }
            }
        }
        if (content !== false) {
            content = core.isString(content) ? content : 'innerHTML';
            try {
                if (t = node[content]) {
                    newnode[content] = t;
                }
            }
            catch(e) {
                options.contentError = e;
            }
        }
        return newnode;
    }
});
//#endregion

//#region widget
var Widget = core.Widget = function(element, options) {
    if (!this.widgetName) {
        return new Widget(element, options);
    }
    this._createWidget(element, options);
};
Widget.prototype = {
    constructor: Widget,
    widgetName: 'Widget',
    widgetEventPrefix: '',
    options: {
        disabled: false
    },
    _createWidget: function(element, options) {
        var self = this;
        if (!element.jquery) {
            element = $(element);
        }
        if (element.length > 1) {
            element = $(element[0]);
        }
        element.data(self.widgetName, self);
        element.bind('remove.' + self.widgetName, function() {
            self.destroy();
        });
        self.element = element;
        self.options = $.extend(true, {},
            self.options,
            self._getCreateOptions(),
            options);
        self._create();
        self.trigger('create');
    },
    _getCreateOptions: function() {
        return $.metadata && this.element && $.metadata.get(this.element[0])[this.widgetName];
    },
    _create: function() { },
    _setOption: function(key, value) {
        this.options[key] = value;
        return this;
    },
    _setOptions: function(options) {
        var self = this;
        $.each(options, function(key, value) {
            self._setOption(key, value);
        });
        return this;
    },
    _debug: function(info) {
        core.debug(core.extend({
            src: this.widgetName + ' widget'
        }, info));
    },
    destroy: function() { },
    option: function(key, value) {
        var self = this;
        if (arguments.length === 0) {
            // don't return a reference to the internal hash
            return $.extend({}, self.options);
        }
        if (typeof key === "string") {
            return value === undefined ? self.options[key] : self._setOption(key, value);
        }
        return self._setOptions(key);
    },
    enable: function() {
        return this._setOption('disabled', false);
    },
    disable: function() {
        return this._setOption('disabled', true);
    },
    queue: function(fn) {
        var self = this,
            args = arguments;
        if ($.isFunction(fn)) {
            self.element.queue(function(next) {
                if (!fn.length || args.length > 1) {
                    fn.apply(self, core.slice(args, 1));
                    next();
                }
                else {
                    fn.call(self, next);
                }
            });
        }
        return self;
    },
    dequeue: function() {
        return this.element.dequeue();
    },
    delay: function(timeout) {
        return this.queue(function(n) { setTimeout(n, timeout); });
    },
    trigger: function(type, event, data) {
        var self = this,
            element = self.element,
            callback = self.options[type],
            eventAll;
        event = core.Event((type === self.widgetEventPrefix ? type :
            self.widgetEventPrefix + type).toLowerCase(), event);
        data = data || {};
        data.widget = self;
        element.trigger(event, data);
        eventAll = $.Event(event);
        eventAll.type = self.widgetEventPrefix + '*';
        element.trigger(eventAll, data);
        return !($.isFunction(callback) &&
            callback.call(self.element[0], event, data) === false ||
            event.isDefaultPrevented());
    },
    bind: function(type, data, handler, isSplit) {
        var self = this,
            element = self.element;
        if (arguments.length === 2 || data === false) {
            handler = data;
            data = undefined;
        }
        if (typeof type === 'string' && type.length) {
            var types;
            if (!isSplit && (types = type.split(' ')).length > 1) {
                for (var t in types) {
                    self.bind(types[t], data, handler, true);
                }
            }
            else {
                element.bind((self.widgetEventPrefix === type ? type : 
                    self.widgetEventPrefix + type).toLowerCase(), data, handler);
            }
        }
        else if (typeof type === 'object') {
            for (var key in type) {
                self.bind(key, type[key]);
            }
        }
        return self;
    },
    unbind: function(type, handler, isSplit) {
        var self = this,
            element = self.element;
        if (typeof type === 'string' && type.length) {
            var types;
            if (!isSplit && (types = type.split(' ')).length > 1) {
                for (var t in types) {
                    self.unbind(t, handler, true);
                }
            }
            else {
                element.unbind(type.charAt(0) === '.' ? type : 
                    (self.widgetEventPrefix + type).toLowerCase(), handler);
            }
        }
        else if (typeof type === 'object') {
            if (type.preventDefault) {
                element.unbind(type);
            }
            else {
                for (var key in type) {
                    self.unbind(key, type[key]);
                }
            }
        }
        return self;
    },
    one: function(type, data, handler) {
        var self = this;
        if (arguments.length === 2 || data === false) {
            handler = data;
            data = undefined;
        }
        function h(e) {
            self.unbind(e, h);
            return handler && handler.apply(this, arguments);
        }
        return self.bind(type, data, h);
    },
    //#region Ready
    _Ready: function() {
        var self = this,
            _ready = self._ready = $.Deferred();
        _ready.fire = function() {
            _ready.resolveWith(self);
        };
        _ready.cancel = function(error) {
            _ready.rejectWith(self, [ error ]);
        };
        self.ready = function(done, fail) {
            if (arguments.length) {
                _ready.then(done, fail);
            }
            return _ready.promise();
        };
        return _ready;
    },
    ready: function(done, fail) {
        return $.when().then(done, fail);
    },
    readyState: function() {
        var r = this.ready();
        return r.isResolved() ? 1 : r.isRejected() ? -1 : 0;
    }
    //#endregion
};

core.widget = function(name, base, prototype, constructor) {
    if (!prototype) {
        prototype = base;
        base = Widget;
    }
    var widget = core[name] = constructor || function(element, options) {
        if (!this.widgetName) {
            return new widget(element, options);
        }
        this._createWidget(element, options);
    };
    core.inherit(widget, base);
    var p = widget.prototype;
    p.options = core.extend(true, {}, base.prototype.options);
    core.extend(true, p, {
            widgetName: name,
            widgetEventPrefix: p.widgetEventPrefix || name
        },
        prototype, {
            constructor: widget
        });
    return widget;
};
//#endregion

//#region dataProviders
var dataProviderBase = {
    load: function(src, done, fail) {
        /*
            done: function(data)
            fail: function(error)
            return: jQuery.Deferred
        */
        if (src) {
            done(src);
        }
        else {
            fail({ textStatus: 'error', errorThrow: 'data is null' });
        }
        return $.when();
    },
    success: undefined,
    error: undefined,

    getObject: function(item) { return item; },
    getChildren: function(item) { return item.items; },

    read: function(src, callback) {
        /* callback: function(data, error) */
        var self = this,
            d = $.Deferred();
        self.request = self.load(src,
            function(resp, status, jqXHR) {
                delete self.request;
                d.resolveWith(self, [ self.success ? self.success(resp, status, jqXHR) : resp ]);
            },
            function(error) {
                delete self.request;
                d.resolveWith(self, [ undefined, self.error ? self.error(error) : error ]);
            }
        ).always(function() {
            delete self.request;
        });
        return d.done(callback);
    },
    abort: function() {
        var r = this.request;
        if (r) {
            r.abort();
        }
    }
};

var dataProviderJson = $.extend({}, dataProviderBase, {
    load: function(url, done, fail) {
        return $.ajax({
            type: 'GET',
            url: url,
            dataType: 'json',
            success: done,
            error: function(request, textStatus, errorThrow) {
                fail({ textStatus: textStatus, errorThrow: errorThrow, request: request });
            }
        });
    }
});

var dataProviderXml = $.extend({}, dataProviderBase, {
    load: function(dataSrc, done, fail) {
        return $.ajax({
            type: 'GET',
            url: dataSrc,
            dataType: 'xml',
            success: done,
            error: function(request, textStatus, errorThrow) {
                fail({ textStatus: textStatus, errorThrow: errorThrow, request: request });
            }
        });
    },
    success: function(resp) {
        return $('items', resp).children();
    },
    getObject: function(item) {
        item = $(item);
        return {
            id: item.attr('id') || '',
            url: item.attr('url') || '',
            text: item.children('text').text() || item.attr('text') || '',
            title: item.children('title').text() || item.attr('title') || ''
        };
    },
    getChildren: function(item) {
        return $(item).children('item');
    }
});

core.dataProviders = {
    base: dataProviderBase,
    json: dataProviderJson,
    xml: dataProviderXml
};
//#endregion

})(jQuery);