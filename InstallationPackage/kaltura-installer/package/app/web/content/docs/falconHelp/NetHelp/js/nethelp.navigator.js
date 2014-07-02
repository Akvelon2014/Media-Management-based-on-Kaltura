(function($, core, undefined) {

var curs, curo,
    reQuery = /^\?(\w+)=([^#]+)(#.*)?$/;

core.widget('navigator', {
    options: {
        hashchangeListener: true,
        home: undefined
    },
    _create: function() {
        var self = this,
            options = self.options;
        self.location = options.location || location;
        self._handlers = [];
        options._skipHashchange = 0;
        $(window).hashchange(function(e) {
            if (options.hashchangeListener) {
                if (options._skipHashchange) {
                    options._skipHashchange = 0;
                }
                else {
                    self._change(self._read(), e);
                }
            }
        });
    },
    _read: function() {
        var s = location.hash;
        return s && (s.charAt(0) === '#' ? s.substring(1) : s)
            .replace(/%23/g, '#'); // fix: Safari encodes '#' in hash string as "%23".
    },
    _write: function(s) {
        location.hash = s;
    },
    _parse: function(s) {
        if (s === curs) {
            return curo;
        }
        var r = { original: s },
            m;
        curs = s;
        m = /^!(.+)(#.*)?$/.exec(s);
        if (m) {
            r.is = 'url';
            r.isUrl = true;
            r.url = m[1];
            r.hash = m[2];
            return curo = r;
        }
        m = reQuery.exec(s);
        if (m) {
            r.is = 'query';
            r.isQuery = true;
            r.key = m[1];
            r.value = decodeURIComponent(m[2]);
            r.hash = m[3];
            return curo = r;
        }
        r.is = 'hash'
        r.hash = s;
        return curo = r;
    },
    _str: function(r) {
        if (typeof r === 'string') {
            return '#!?'.indexOf(r.charAt(0)) > -1 ? r :
                reQuery.test(r) ? '?' + r :
                ('!' + r);
        }
        return r.original ? r.original : 
            ((r.isUrl ? ('!' + r.url) : 
            r.isQuery ? ('?' + r.key + '=' + encodeURIComponent(r.value)) :
            '') + (r.hash || ''));
    },
    _change: function(news, event, spec) {
        var self = this;
        if (!news || typeof news !== 'object') {
            news = self._parse(news || self._read());
        }
        if (spec || self._cur !== news.original) {
            news.previos = self._cur;
            self._cur = news.original;
            if (event !== false) {
                if (news.original === '') {
                    self.home(event);
                }
                else {
                    self.trigger('change', event, news);
                }
            }
        }
        return news;
    },
    init: function(event) {
        var self = this,
            val = self._parse(self._read());
        if (val.is !== 'hash') {
            self._change(val, event);
            return val;
        }
        else {
            self.home(event);
        }
    },
    val: function(val, event) {
        var self = this,
            s;
        if (val == undefined) {
            // getter
            return self._parse(self._read());
        }
        // setter
        self.options._skipHashchange = 1;
        self._write(self._str(val));
        self._change(val, event, true);
    },
    home: function(event) {
        var self = this,
            homeUrl = self.options.home;
        if (homeUrl) {
            if (self._read()) {
                self.options._skipHashchange = 1;
                self._write('');
            }
            self._change({ url: homeUrl, isUrl: true }, event, true);
        }
        else if (homeUrl === false) {
            self.blank(event);
        }
    },
    blank: function(event) {
        var self = this;
        if (self._read()) {
            self.options._skipHashchange = 1;
            self._write('');
        }
        self.trigger('blank', event, {});
    },
    back: function(to) {
        this.go(-Math.abs(to || 1));
    },
    forward: function(to) {
        this.go(Math.abs(to || 1));
    },
    go: function(to) {
        if (to === 0 || /home/i.test(to)) {
            return this.home();
        }
        if (/blank/i.test(to)) {
            return this.blank();
        }
        history.go(to);
    }
});

})(jQuery, nethelp);