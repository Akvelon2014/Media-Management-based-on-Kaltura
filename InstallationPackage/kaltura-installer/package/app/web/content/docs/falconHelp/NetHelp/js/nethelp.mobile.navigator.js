(function ($, core, undefined) {

    var curs, curo,
    reQuery = /^\?(\w+)=([^#]+)(#.*)?$/;

    $.event.props.push('fromHashChange');

    core.widget('navigator', {
        options: {
            hashchangeListener: true,
            home: undefined
        },
        _create: function () {
            var self = this,
            options = self.options;
            self.location = options.location || location;
            self._handlers = [];

            // Listen for any attempts to call changePage().
            $(document).bind("pagebeforechange", function (e, data) {
                if (typeof data.toPage === "string") {
                    var u = $.mobile.path.parseUrl(data.toPage);

                    if (u.hash.search(/^#c1/) !== -1) {
                        self._cur = self._read();
                    }
                    else {
                        if (data.options.fromHashChange) {
                            var url = data.toPage,
                                pathname = $.mobile.path.parseUrl(core.shell.baseUrl).directory;
                            if (url.indexOf(pathname) == 0)
                                url = url.substring(pathname.length);
                            e.fromHashChange = true;
                            self._change(url, e);
                        }
                    }
                }
            });
        },
        _read: function () {
            var s = location.hash;
            if (!s) {
                s = location.search;
            }
            return s && (s.charAt(0) === '#' ? s.substring(1) : s)
            .replace(/%23/g, '#'); // fix: Safari encodes '#' in hash string as "%23".
        },
        _write: function (s) {
        },
        _parse: function (s) {
            if (s === curs) {
                return curo;
            }
            var r = { original: s },
            m,
            o = $.mobile.path.parseUrl(s);
            curs = s;
            if (!o.search && !$('#' + s).length) {
                r.is = 'url';
                r.isUrl = true;
                r.url = s;
                r.hash = s;
                return curo = r;
            }
            m = reQuery.exec(o.search);
            if (m && m[1] !== 'query' && m[1] !== 'path') {
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
        _str: function (r) {
            if (typeof r === 'string') {
                return r;
                return '#!?'.indexOf(r.charAt(0)) > -1 ? r :
                reQuery.test(r) ? '?' + r :
                ('!' + r);
            }
            return r.original ? r.original :
            ((r.isUrl ? (r.url) :
            r.isQuery ? ('?' + r.key + '=' + encodeURIComponent(r.value)) :
            '') + (r.hash || ''));
        },
        _change: function (news, event, spec) {
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
        init: function (event) {
            var self = this,
            val = self._parse(self._read());
            if (val.isUrl && !val.url) {
                var url = location.pathname,
                    obj = $.mobile.path.parseUrl(core.shell.baseUrl),
                    pathname = obj.directory;
                if (url !== obj.pathname) {
                    if (url.indexOf(pathname) == 0) {
                        url = url.substring(pathname.length);
                    }
                    val.url = url;
                    val.original = url;
                }
            }
            var s = nethelp.shell.setting('general.showTopicAtStartup');
            if (val.url || val.hash || val.isQuery) {
                self._change(val, event);
                // workaround for Safari and some mobile browsers: sometimes animationComplete isn't fired and this class isn't removed by jQuery Mobile, so the page stays invisible to users
                $('html').removeClass("ui-mobile-rendering");
                return val;
            }
            else {                
                if (s === false) {
                    $.mobile.changePage('#c1tocPage');
                }
                else {
                    self.home(event);
                }
            }
        },
        val: function (val, event) {
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
        home: function (event) {
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
        blank: function (event) {
            var self = this;
            if (self._read()) {
                self.options._skipHashchange = 1;
                self._write('');
            }
            self.trigger('blank', event, {});
        },
        back: function (to) {
            this.go(-Math.abs(to || 1));
        },
        forward: function (to) {
            this.go(Math.abs(to || 1));
        },
        go: function (to) {
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