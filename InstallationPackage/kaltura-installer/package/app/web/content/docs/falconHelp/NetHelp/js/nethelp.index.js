(function($, core, undefined) {

//#region constants
var p = 'c1-index',
    c_item = p + '-item',
    p_id = 'c1indexItem-',
    p_id_length = p_id.length,
    p_id_re = new RegExp('^#?(' + p_id + '.*\\d)$'),
    p_itemOf = '__c1ItemOfIndex';
//#endregion

var Index = core.widget('index', {
    options: {
        dataPath: '',
        dataSource: 'keywords.xml',
        dataType: 'xml',

        itemTemplate: '<li id="#{id}" class="#{itemClass}">' + 
            '<a #{hrefAttr} class="c1-index-text">#{text}</a>#{subtree}</li>',
        moreTemplate: '<li class="#{itemClass}">' + 
            '<a href="javascript:void(0)" class="c1-index-more-text">#{text}</a></li>',
        selectedClass: p + '-selected',

        filterElement: undefined,
        filterEvents: 'keyup',
        filterDelay: 600,
        allowFilterNestedKeywords: false,

        scrollElement: undefined,
        disableScrollEvent: false,
        pageSize: 300,
        emptyForEmptyFilter: false,
        moreText: 'More...'
    },
    _create: function() {
        var self = this,
            _ready = self._Ready(),
            options = self.options,
            element = self.element;

        // templates in options
        core.each([ 'itemTemplate', 'moreTemplate' ], function(o) {
            var t = options[o]
            if (core.isString(t)) {
                options[o] = core.template(t);
            }
        });

        element.addClass(p + ' c1-widget');
        self.items = [];
        self._bindEvents();

        var url = (options.dataPath || '') + options.dataSource,
            dataType = options.dataType || 'xml';
        $.ajax({
            url: url,
            dataType: dataType,
            success: function(data) {
                self._debug('Loading of Index data succeeded.');
                self._data = new Index.Data(data);
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
                    from: 'index._create > $.ajax.error'
                });
                self.disable();
                _ready.cancel(error);
            }
        });
        self._debug({
            info: 'Loading of Index data started.',
            url: url,
            dataType: dataType
        });
    },
    _bindEvents: function() {
        var self = this,
            options = self.options,
            element = self.element,
            filterElement = options.filterElement,
            scrollElement = self.scrollElement = options.scrollElement ? $(options.scrollElement) : element;
        element.delegate('.c1-index-text', 'click', function(e) {
            var target = $(this),
                li = self.getItem(target),
                d = self.getData(li);
            e.preventDefault();
            if (!d.links.length) return;
            self.deselect();
            target.addClass(options.selectedClass);
            self.trigger('select', e,
                core.extend(d, { li: li, target: target }));
        }).delegate('.c1-index-more-text', 'click', function(e) {
            e.preventDefault();
            self.nextPage();
        });
        scrollElement.scroll(function(e) {
            if (!options.disableScrollEvent && scrollElement.scrollTop() > scrollElement[0].scrollHeight - scrollElement.innerHeight() - 10) {
                self.nextPage();
            }
        });
        //#region filterElement init
        if (!filterElement) {
            filterElement = element.data(Index.filterDataKey);
        }
        if (filterElement) {
            self.filterElement = filterElement = $(filterElement).eq(0);
            var f = function() {
                self.filter(filterElement.val());
            };
            if (options.filterDelay > 0 && !core.accessibility) {
                var timerid = 0;
                filterElement.bind(options.filterEvents, function(e) {
                    clearTimeout(timerid);
                    if (e.which === 13) {
                        e.preventDefault();
                        f();
                        return;
                    }
                    timerid = setTimeout(f, options.filterDelay);
                });
            }
            else {
                filterElement.bind('keydown', function(e) {
                    if (e.which === 13) {
                        f();
                    }
                });
            }
        }
        //#endregion
    },

    itemId: function(id) {
        if (core.isArray(id)) {
            return p_id + id.join('-');
        }
        else if (core.isString(id)) {
            return id.substring(p_id_length).split('-');
        }
    },
    _itemsHtml: function(path, items, start, end) {
        if (start == undefined) {
            start = 0;
        }
        end = Math.min(items.length, end || Infinity);
        var self = this,
            tmpl = self.options.itemTemplate,
            sb = core.StringBuilder(),
            i, item, iitems, ilinks, iclass, p;
        for (i = start; i < end; ++i) {
            item = items[i];
            iitems = item.items;
            ilinks = item.links || [];
            iclass = item.cssClass;
            p = path.concat(i);
            sb.addLine(tmpl(core.extend({}, item, {
                id: self.itemId(p),
                hrefAttr: ilinks.length > 0 ? 'href="' + ilinks[0].url + '"' : '',
                itemClass: c_item + (iclass ? ' ' + iclass : ''),
                subtree: iitems && iitems.length ?
                    '<ul>' + self._itemsHtml(p, item.items) + '</ul>' : '',
                items: !!iitems
            })));
        }
        return sb.toString();
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
            $('#' + self.itemId(li), self.element) :
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
        }
        if (core.isArray(val)) {
            return val;
        }
        return null;
    },
    getData: function(item) {
        var path = core.isArray(item) ? item :
            this.getPath(item);
        if (path) {
            item = this;
            var items;
            for (var i = 0, l = path.length; i < l && item; ++i) {
                items = item.items;
                item = core.isArray(items) ? items[path[i]] : null;
            }
            item = core.extend({ links: [] }, item, { path: path });
            item.items = !!item.items;
            return item;
        }
        return null;
    },
    filterVal: function(val, andFilter) {
        var self = this,
            fel = self.filterElement;
        if (val == undefined) {
            return fel && fel.val();
        }
        if (fel) {
            fel.val(val);
        }
        if (andFilter !== false) {
            self.filter(val);
        }
    },
    filter: function(key) {
        var self = this,
            options = self.options;
        if (options.disabled) {
            return;
        }
        key = Keys(key || self.filterVal(),
            { multi: false, nested: self.options.allowFilterNestedKeywords });
        if (!key.length) {
            if (options.emptyForEmptyFilter) {
                self.empty(true);
                return;
            }
            else {
                key = '';
            }
        }
        self._data.filter(key, function(err, items) {
            self.empty();
            var t;
            if (err === -2) {
                t = 'insufficientfilter';
                err = {};
            }
            else if (err) {
                t = 'error';
            }
            else if (!items || !items.length) {
                t = 'notfound';
                err = {};
            }
            if (t) {
                self.trigger(t, undefined, err);
                return;
            }
            self.items = items;
            self.trigger('found', undefined, { count: items.length || 0 });
            self.nextPage();
        });
    },
    empty: function(emptyFilter) {
        var self = this;
        if (self.scrollElement) {
            self.scrollElement;
        }
        self.items = [];
        self.htmlSize = 0;
        self.element.scrollTop(0).empty();
        if (emptyFilter) {
            self.filterVal('', false);
            self.trigger('emptyfilter');
        }
    },
    nextPage: function() {
        var self = this,
            element = self.element,
            options = self.options,
            size = self.htmlSize,
            items = self.items,
            l = items.length,
            size2 = l > size && size + options.pageSize;
        if (size2) {
            var more = element.find(' > .c1-index-more').remove();
            element.append(self._itemsHtml([], items, size,
                self.htmlSize = size2));
            if (size2 < l) {
                element.append(more.length ? more : options.moreTemplate({
                    itemClass: c_item + ' service c1-index-more',
                    text: options.moreText
                }));
            }
            self.trigger('nextpage', undefined, { first: !size, last: size2 >= l });
        }
    },
    find: function(key, callback) {
        if (!this._data) {
            callback('Data is not initialized.');
        }
        return this._data.find(key, callback);
    },
    deselect: function() {
        var c_selected = this.options.selectedClass;
        this.element.find('.' + c_selected).removeClass(c_selected);
    },
    hasKeywords: function() {
        return !!(this._data.items || []).length;
    }
});
Index.filterDataKey = 'filter';

//#region Keys parsing
var keysSplitter = '\0';
function keysReplaceEscaped(match, esc, ch) {
    esc = esc || '';
    return esc.length % 2 ? (esc.substring(1) + ch) : (esc + keysSplitter);
}
var Keys = Index.Keys = function(str, options) {
    /*
        options: {
            multi: boolean (default: true),
            nested: boolean (default: true)
        }
        return [ [ , ], ... ]; - object like array of keywords paths
    */
    if (str instanceof Keys) {
        return str;
    }
    var self = this,
        spl = '\0',
        multi, nested;
    if (!(self instanceof Keys)) {
        return new Keys(str, options);
    }
    if (core.isBoolean(options)) {
        options = { multi: options };
    }
    options = options || {};
    self.allowMulti = multi = options.multi !== false;
    self.allowNested = nested = options.nested !== false;
    self.length = 0;
    str = core.trim(str).toLowerCase();
    if (str) {
        var arr = multi ? 
                str.replace(/(\\*)(\+)/g, keysReplaceEscaped).split(keysSplitter).sort() :
                [ str ],
            l = 0;
        core.each(arr, function(s) {
            if (s) {
                self[l++] = nested ? s.replace(/(\\*)(\,)/g, keysReplaceEscaped)
                    .replace(/\\\\/g, '\\').split(keysSplitter) : [ s ];
            }
        });
        self.length = l;
    }
};
core.extend(Keys.prototype, {
    equal: function(keys) {
        var self = this;
        if (core.isString(keys)) {
            keys = new Keys(keys, self.multi);
        }
        if (self === keys) {
            return true;
        }
        if (!(keys instanceof Keys) || self.length !== keys.length) {
            return false;
        }
        var i = self.length, s, k, j;
        while (i--) {
            s = self[i];
            j = s.length;
            k = keys[i];
            if (j !== k.length) {
                return false;
            }
            while (j--) {
                if (s[j] !== k[j]) {
                    return false;
                }
            }
        }
        return true;
    }
});
//#endregion

//#region Data
// TODO: map data is not supported
var Data = Index.Data = function(data, options) {
    if (!(this instanceof Data)) {
        return new Data(data);
    }
    this.items = $.isXMLDoc(data) ? Index.readXML(data, options) : data;
};
core.extend(Data.prototype, {
    _key: function(item) {
        return item.key || (item.key = core.trim(item.text).toLowerCase());
    },
    _itemChecker: function(key) {
        var self = this;
        return function(item) {
            var ikey = self._key(item);
            return ikey < key ? -1 : ikey > key ? 1 : 0;
        };
    },
    _findItem: function(keypath, items) {
        var self = this,
            i = 0,
            l = keypath.length - 1;
        items = items || self.items;
        for (; items && i < l; ++i) {
            items = (items[core.quickSearch(items, self._itemChecker(keypath[i]))] || l).items;
        }
        return items && items[core.quickSearch(items, self._itemChecker(keypath[l]))];
    },
    _filter: function(items, key, i) {
        i = i || 0;
        var self = this,
            r = !core.isString(key),
            k = key && (r ? key[i] : key);
        if (!k) {
            return core.extend(true, [], items);
        }
        items = core.map(items, function(item) {
            return self._key(item).indexOf(k) === 0 ? core.extend({}, item) : undefined;
        });
        r && core.each(items, function(item, t) {
            t = item.items;
            if (t && t.length) {
                item.items = self._filter(t, key, i + 1);
            }
        });
        return items.length ? items : undefined;
    },
    find: function(key, callback) {
        /* callback: function(err, links) */
        callback = core.onlyFunction(callback);
        var self = this,
            keys = Keys(key),
            i = 0,
            l = keys.length,
            item,
            links = [];
        for (; i < l; ++i) {
            item = self._findItem(keys[i]);
            links = links.concat(item && item.links || []);
        }
        // sort by link.text (null or undefined are more then any one)
        links.sort(function(a, b) {
            if (a == undefined) {
                return b == undefined ? 0 : 1;
            }
            if (b == undefined) {
                return -1;
            }
            a = a.text;
            b = b.text;
            return a < b ? -1 : a > b ? 1 : 0;
        });
        // remove duplicates and nulls
        i = 0;
        l = {};
        core.each(links, function(item) {
            if (item.text !== l.text || item.url !== l.url) {
                links[i++] = l = item;
            }
        });
        links.length = i;
        callback(undefined, links);
    },
    filter: function(key, callback) {
        /* callback: function(err, items) */
        callback = core.onlyFunction(callback);
        var self = this;
        callback(undefined, self._filter(self.items, new Keys(key, false)[0]) || []);
    }
});
//#endregion

Index.readXML = function(doc, options) {
    options = options || {};
    var res = [],
        rootTag = options.rootTag || 'keywords',
        itemTag = options.itemTag || 'keyword';
    doc = $(doc);
    function op(xml, items, i, children) {
        children = xml.children();
        i = {
            text: children.filter('text').text(),
            id: xml.attr('id'),
            links: children.filter('link').map(function(l) {
                l = $(this);
                return { url: l.attr('url'), text: l.text() };
            }).get()
        };
        items.push(i);
        items = [];
        children.filter(itemTag).each(function() { op($(this), items); });
        if (items.length) {
            i.items = items;
        }
    }
    (doc.is(itemTag + ', ' + rootTag) ? doc : doc.find(rootTag))
        .children(itemTag)
        .each(function() { op($(this), res); });
    return res;
};

core.widget('groups', {
    options: {
        dataPath: '',
        dataSource: 'groups.xml',
        dataType: 'xml'
    },
    _create: function() {
        var self = this,
            _ready = self._Ready(),
            options = self.options;
        var url = (options.dataPath || '') + options.dataSource,
            dataType = options.dataType || 'xml';
        $.ajax({
            url: url,
            dataType: dataType,
            success: function(data) {
                self._debug('Loading of Groups data succeeded.');
                self._data = new Index.Data(data, { rootTag: 'groups', itemTag: 'group' });
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
                    from: 'groups._create > $.ajax.error'
                });
                self.disable();
                _ready.cancel(error);
            }
        });
        self._debug({
            info: 'Loading of Groups data started.',
            url: url,
            dataType: dataType
        });
    },
    find: function(key, callback) {
        if (!this._data) {
            callback('Data is not initialized.');
        }
        return this._data.find(key, callback);
    }
});

})(jQuery, nethelp);