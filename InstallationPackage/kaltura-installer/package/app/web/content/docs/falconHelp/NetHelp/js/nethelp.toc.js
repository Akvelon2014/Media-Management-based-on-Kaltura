(function($, core, undefined) {

//#region constants
var p = 'c1-toc',
    c_item = p + '-item',
    c_item_leaf = p + '-item-leaf',
    c_item_nonleaf = p + '-item-nonleaf',
    c_item_collapsed = p + '-item-collapsed',
    c_item_expanded = p + '-item-expanded',
    c_item_selected = p + '-item-selected',

    d_iconElement = 'iconElement',

    p_id = 'c1tocItem-',
    p_id_length = p_id.length,
    p_id_re = new RegExp('^#?(' + p_id + '.*\\d)$'),
    p_itemOf = '__c1ItemOfToc';
//#endregion

var Toc = core.widget('toc', {
    options: {
        dataPath: '',
        dataSource: 'toc.xml',
        dataType: 'xml',

        itemTemplate: '<li id="#{id}" class="#{itemClass}"><a href="#{this.url || \'javascript:void(0)\'}" <#if(this.target){#> target="#{target}" <#}#> title="#{tooltip}" class="inner">' +
            '<span title="#{iconTitle}" class="c1-toc-icon #{icon}"></span>' +
            '<span class="c1-toc-text">#{title}</span>' +
            '</a>#{subtree}</li>',
        icons: {
            leaf: 'ui-icon ui-icon-carat-1-e',
            collapsed: 'ui-icon ui-icon-triangle-1-e',
            expanded: 'ui-icon ui-icon-triangle-1-se'
        },
        tooltips: { /*
            topic: 'Topic',
            closedBook: 'Closed book without topic',
            openBook: 'Open book without topic',
            closedBookTopic: 'Closed book with topic',
            openBookTopic: 'Open book with topic'
        */ },
        selectedClass: 'ui-corner-all ui-state-hover',

        fullHtmlTree: false,
        expanded: false,

        expandAnimation: { duration: 0 },
        collapseAnimation: { duration: 0 },
        // for example, { effect: "blind", easing: "easeOutExpo", duration: 200 }

        keyExpand: 107,   // Num +
        keyCollapse: 109, // Num -

        scrollup: {
            position: 'center',
            always: false
        }
    },
    _data: undefined,
    _getTocUrl: function () {
        var self = this,
        options = self.options;
        return (options.dataPath || '') + options.dataSource;
    },
    _create: function() {
        var self = this,
            _ready = self._Ready(),
            options = self.options,
            element = self.element,
            t = options.itemTemplate;
        if (core.isString(t)) {
            options.itemTemplate = core.template(t);
        }
        element.addClass(p + ' c1-widget');
        self._bindEvents();

        var url = this._getTocUrl(),
        dataType = options.dataType || 'xml';
        $.ajax({
            url: url,
            dataType: dataType,
            success: function(data) {
                self._debug('Loading of TOC data succeeded.');
                self._data = new Toc.Data(data);
                element.html(self._itemsHtml([], {
                    recursive: options.fullHtmlTree,
                    expanded: options.expanded
                }));
                _ready.fire();
            },
            error: function(request, status, error) {
                error = {
                    request: request,
                    status: status,
                    error: error
                };
                self._debug({
                    error: error,
                    from: 'toc._create > $.ajax.error'
                });
                self.disable();
                _ready.cancel(error);
            }
        });
        self._debug({
            info: 'Loading of TOC data started.',
            url: url,
            dataType: dataType
        });
    },
    _bindEvents: function() {
        var self = this,
            options = self.options,
            element = self.element;
        element.delegate('.c1-toc-icon', 'click', function(e) {
            var li = self.getItem(this);
            if (li.is('.' + c_item_nonleaf)) {
                e.preventDefault();
                e.stopPropagation()
                self.toggle(this, core.Event(e));
            }
        });
        element.delegate('a', 'click keydown', function(e) {
            var target = $(this),
                wnd = target.attr('target'),
                li = self.getItem(target),
                url = self.getData(li).url;
            if (!li.length) {
                return;
            }
            if (e.type === 'keydown') {
                var key = e.which;
                switch (key) {
                    case options.keyExpand:
                        self.expand(li);
                        break;
                    case options.keyCollapse:
                        self.collapse(li);
                        break;
                }
                if (key !== 13) { // not ENTER
                    return;
                }
            }
            e.preventDefault();
            if (!url || li.hasClass(c_item_selected)) {
                self.toggle(li);
            }
            else if (wnd) {
                self.trigger('open', e, { url: url, target: wnd }) &&
                    self.expand(li);
            }
            else {
                self.select(li);
                self.expand(li);
            }
        });
    },

    itemId: function(id) {
        if (core.isArray(id)) {
            return p_id + id.join('-');
        }
        else if (core.isString(id)) {
            return id.substring(p_id_length).split('-');
        }
    },
    _find: function(url) {
        url = url.split('#')[0]; // trim url-hash
        return this._data.find(url);
    },
    _itemsHtml: function(path, items, flags) {
        if (!core.isArray(items)) {
            flags = items;
            items = undefined;
        }
        flags = core.Flags(flags);
        var self = this,
            options = self.options,
            tmpl = options.itemTemplate,
            sb = core.StringBuilder(),
            expanded = !!flags.expanded,
            recursive = expanded || !!flags.recursive,
            ind = 0,
            len,
            item,
            ip,
            iitems;
        items = items || self._data.getItem(path).items || [];

        for (len = items.length; ind < len; ++ind) {
            item = items[ind];
            ip = path.concat(ind);
            iitems = item.items;
            sb.addLine(tmpl(core.extend({}, item, {
                id: self.itemId(ip),
                items: !!iitems,
                icon: options.icons[iitems ? expanded ?
                    'expanded' : 'collapsed' : 'leaf'],
                iconTitle: options.tooltips[iitems ? (expanded ?
                    'openBook' : 'closedBook') + (item.url ? 'Topic' : '') : 'topic'] || '',
                itemClass: c_item + ' ' + (item.cssClass ? item.cssClass + ' ' : '') +
                    (iitems ? c_item_nonleaf + ' ' +
                    (expanded ? c_item_expanded : c_item_collapsed) :
                    c_item_leaf),
                url: item.url,
                target: item.window,
                subtree: (recursive || expanded) && iitems && iitems.length ?
                    '<ul' + (expanded ? '' : ' style="display:none;"') + '>' +
                    self._itemsHtml(ip, iitems, flags) +
                    '</ul>' : ''
            })));
        }
        return sb.toString();
    },
    _buildHtmlFor: function(li, path, items) {
        var self = this,
            ul = li.children('ul');
        if (ul.length) {
            return ul;
        }
        path = path || self.itemId(li.attr('id'));
        items = items || self._data.getItem(path).items;
        return items && items.length && $('<ul />')
            .hide()
            .appendTo(li)
            .html(self._itemsHtml(path, items));
    },
    _buildHtml: function(path, andChildren) {
        var self = this,
            ul = self.element,
            li = ul.find('#' + self.itemId(path)),
            items = self._data.items,
            i, l, p, p2 = [];
        if (li.length) {
            items = self._data.getItem(path).items;
            ul = li.children('ul');
        }
        else {
            li = null;
            for (i = 0, l = path.length; i < l; ++i) {
                if (!ul.length) {
                    ul = self._buildHtmlFor(li, p2, items);
                    if (!ul) {
                        li = null;
                        break;
                    }
                }
                p = path[i];
                p2.push(p);
                items = (items[p] || {}).items;
                li = ul.children().eq(p);
                ul = li.children('ul');
            }
        }
        if (andChildren && li && !ul.length) {
            self._buildHtmlFor(li, path, items);
        }
        return li;
    },

    _expandInner: function(li, expand) {
        var icon = li.data(d_iconElement);
        if (icon == null) {
            icon = li.find('> .inner .c1-toc-icon');
            li.data(d_iconElement, icon.length ? icon : (icon = false));
        }
        if (icon) {
            expand = expand !== false;
            var t = this.options.icons;
            t = [t.expanded, t.collapsed];
            var rc = t[expand ? 1 : 0],
                ac = t[expand ? 0 : 1];
            rc && icon.removeClass(rc);
            ac && icon.addClass(ac);
            icon.attr('title', this.options.tooltips[(expand ? 'openBook' : 'closedBook') + (this.getData(li).url ? 'Topic' : '')]);
        }
    },
    _selectInner: function(li, select) {
        select = select !== false;
        var c = this.options.selectedClass;
        c && li.children('.inner')[select ? 'addClass' : 'removeClass'](c);
    },
    _animation: function(ul, show, callback) {
        var self = this,
            animation = this.options[show ? 'expandAnimation' : 'collapseAnimation'],
            duration = animation ? animation.duration : 0;
        ul[show ? 'show' : 'hide']
            .apply(ul, ($.effects && duration ? [animation.effect, {}] : [])
                .concat([ duration, function() {
                    core.call(callback, self);
                } ]));
    },

    getItem: function(li, returnNull) {
        if (!li) {
            return returnNull ? null : $();
        }
        var self = this;
        if (li[p_itemOf] === self) {
            return li;
        }
        li = core.isArray(li) ?
            // get item by path
            self._buildHtml(li) || $() :
            $(li).eq(0).closest('li', self.element);
        li[p_itemOf] = self;
        return returnNull && !li.length ? null : li;
    },
    getPath: function(val) {
        var self = this;
        if (!val) {
            return null;
        }
        if (val.nodeType || val.jquery) {
            val = self.getItem(val).attr('id');
        }
        if (core.isString(val)) {
            var t = p_id_re.exec(val);
            if (t) {
                return self.itemId(t[1]);
            }
            return self._find(val);
        }
        if (core.isArray(val)) {
            return val;
        }
        return null;
    },
    getData: function(item) {
        item = core.isArray(item) ? item :
            this.getPath(item);
        if (item) {
            item = core.extend({}, this._data.getItem(item), { path: item });
            item.items = !!item.items;
            return item;
        }
        return null;
    },
    expand: function(li, event, params) {
        var self = this,
            p = params ? core.extend({}, params) : {};
        li = self.getItem(li);
        if (!self.options.disabled && li.hasClass(c_item_collapsed)) {
            if (!p.inQueue && !p.jumpQueue) {
                p.inQueue = true;
                self.queue(self.expand, li, event, p);
                return self;
            }
            p.li = li;
            if (self.trigger('expanding', event, p) === false) {
                return self;
            }
            var ul = li.children('ul');
            if (!ul.length) {
                ul = self._buildHtmlFor(li);
                if (!ul) {
                    core.debug({ error: 'TOC data mismatch', from: 'toc.expand' });
                }
            }
            li.removeClass(c_item_collapsed);
            self._expandInner(li);
            self._animation(ul, true, core.concat(p.callback, function() {
                li.addClass(c_item_expanded);
                self.trigger('expand', event, p);
            }));
        }
        return self;
    },
    collapse: function(li, event, params) {
        var self = this,
            p = params ? core.extend({}, params) : {};
        li = self.getItem(li);
        if (!self.options.disabled && li.hasClass(c_item_expanded)) {
            if (!p.inQueue && !p.jumpQueue) {
                p.inQueue = true;
                self.queue(self.collapse, li, event, p);
                return self;
            }
            p.li = li;
            if (self.trigger('collapsing', event, p) === false) {
                return self;
            }
            li.removeClass(c_item_expanded);
            self._expandInner(li, false);
            self._animation(li.children('ul'), false, core.concat(p.callback, function() {
                li.addClass(c_item_collapsed);
                self.trigger('collapse', event, p);
            }));
        }
        return self;
    },
    toggle: function(li, event, params) {
        var self = this;
        li = self.getItem(li);
        if (li.hasClass(c_item_collapsed)) {
            return self.expand(li, event, params);
        }
        if (li.hasClass(c_item_expanded)) {
            return self.collapse(li, event, params);
        }
        return self;
    },
    expandAll: function(li, event, params) {
        if (!params || typeof params !== 'object') {
            params = {};
        }
        var self = this,
            ul = li && (li = self.getItem(li, true)) && li.children('ul') || self.element;
        params.originalJumpQueue = params.jumpQueue;
        params.jumpQueue = true;
        ul.children().each(function() {
            self.expand(this, event, params);
            self.expandAll(this);
        });
    },
    collapseAll: function(li, event, params) {
        if (!params || typeof params !== 'object') {
            params = {};
        }
        var self = this,
            ul = li && (li = self.getItem(li, true)) && li.children('ul') || self.element;
        params.originalJumpQueue = params.jumpQueue;
        params.jumpQueue = true;
        ul.children().each(function() {
            self.collapseAll(this);
            self.collapse(this, event, params);
        });
    },
    select: function(li, event, params) {
        var self = this,
            options = self.options,
            element = self.element,
            p = params ? core.extend({}, params) : {},
            path;
        if (li == null) {
            return self.deselect(event, params);
        }
        if (p.queue !== false && !p.inQueue) {
            self.queue(function() {
                p.inQueue = true;
                self.select(li, event, p);
            });
            return self;
        }
        if (core.isString(li)) {
            // get path by URL or ID
            li = self.getPath(li);
        }
        if (core.isArray(li)) {
            // select by path
            return self.select(self._buildHtml(li), event, p);
        }
        self.deselect(event, p);
        li = self.getItem(li);
        if (li.length) {
            path = self.getPath(li);
            if (p.expandParents !== false) {
                var items = [], i = li;
                while ((i = i.parent().closest('li', element)).length) {
                    items.push(i);
                }
                for (i = items.length; i; ) {
                    self.expand(items[--i], event, p);
                }
            }
            core.extend(p, { li: li, url: self.getData(li).url, path: path });
            if (!li.hasClass(c_item_selected) &&
                    self.trigger('selecting', event, p) !== false) {
                self._selectInner(li);
                li.addClass(c_item_selected);
                self._selectedItem = li;
                self._selected = path;
                if (li.scrollup && options.scrollup !== false) {
                    li.find('.c1-toc-text').scrollup(options.scrollup);
                }
                self.trigger('select', event, p);
            }
        }
        return self;
    },
    deselect: function(event, params) {
        if (!core.isObject(params)) {
            params = {};
        }
        var self = this,
            li = self._selectedItem,
            path = self._selected;
        if (li) {
            self._selectInner(li, false);
            li.removeClass(c_item_selected);
            delete self._selected;
            delete self._selectedItem;
            self.trigger('deselect', event, core.extend({}, params, { li: li, path: path }));
        }
    },
    getSelected: function(item) {
        return this['_selected' + (item ? 'Item' : '')];
    },
    getNext: function() {
        var self = this,
            selected = self._selected;
        return selected && self._data.getItem(selected).nextUrl || null;
    },
    getPrev: function() {
        var self = this,
            selected = self._selected;
        return selected && self._data.getItem(selected).prevUrl || null;
    },
    getFirst: function() {
        return this._data.firstUrl || null;
    },
    getLast: function(hasUrl) {
        return this._data.lastUrl || null;
    },
    gotoNext: function(event, params) {
        var self = this,
            next = self.getNext();
        next && self.select(next, event, params);
        return self;
    },
    gotoPrev: function(event, params) {
        var self = this,
            prev = self.getPrev();
        prev && self.select(prev, event, params);
        return self;
    },
    getBreadcrumbs: function(andSelected) {
        var self = this,
            bc = [],
            path = self._selected,
            item = self._data,
            i = 0,
            l = path.length;
        if (!andSelected) {
            l -= 1;
        }
        while (i < l) {
            bc.push(core.extend({},
                item = item.items[path[i]],
                { items: null, path: path.slice(0, ++i) }));
        }
        return bc;
    }
}); // widget

//#region Data
var Data = Toc.Data = function(tree) {
    if (!(this instanceof Data)) {
        return new Data(tree);
    }
    this.size = {
        all: 0,
        links: 0
    };
    this.items = [];
    this.index = {};
    this.add(tree);
};
core.extend(Data.prototype, {
    _indexUrl: function(url, path) {
        if (core.isString(url) &&
            (url = core.trim(url).toLowerCase()) && !this.index[url]) {
            this.index[url] = path;
        }
    },
    _indexItem: function(item, path) {
        var self = this,
            items = item.items,
            i;
        self.size.all += 1;
        if (item.url) {
            self.size.links += 1;
            //#region index of URL-sequence
            if (self.firstUrl) {
                item.prevUrl = self.lastUrl;
                self._lastWithUrl.nextUrl = path;
            }
            else {
                self.firstUrl = path;
            }
            self.lastUrl = path;
            self._lastWithUrl = item;
            //#endregion
            self._indexUrl(item.url, path);
        }
        if (core.isArray(items) && items.length) {
            for (i = 0; (item = items[i]); ++i) {
                self._indexItem(item, path.concat(i));
            }
        }
    },
    getItem: function(path) {
        if (path == null) {
            return null;
        }
        var item = this,
            items;
        for (var i = 0, l = path.length; i < l && item; ++i) {
            items = item.items;
            item = core.isArray(items) ? items[path[i]] : null;
        }
        return item;
    },
    add: function(items) {
        var self = this,
            target = self.items,
            item,
            i = 0,
            l = target.length;
        if ($.isXMLDoc(items)) {
            items = Toc.readXML(items);
        }
        while (item = items[i]) {
            target[l] = item;
            self._indexItem(item, [l]);
            ++i;
            ++l;
        }
    },
    find: function(url) {
        return this.index[core.trim(url).toLowerCase()] || null;
    }
});
//#endregion

Toc.readXML = function(doc) {
    var res = [];
    doc = $(doc);
    function op(xml, items, i, children) {
        children = xml.children();
        i = {
            title: children.filter('title').text(),
            tooltip: children.filter('tooltip').text(),
            id: xml.attr('id'),
            url: xml.attr('url'),
            window: xml.attr('window')
        };
        items.push(i);
        items = [];
        children.filter('item').each(function() { op($(this), items); });
        if (items.length) {
            i.items = items;
        }
    }
    (doc.is('item, toc') ? doc : doc.find('toc'))
        .children('item')
        .each(function() { op($(this), res); });
    return res;
};

})(jQuery, nethelp);