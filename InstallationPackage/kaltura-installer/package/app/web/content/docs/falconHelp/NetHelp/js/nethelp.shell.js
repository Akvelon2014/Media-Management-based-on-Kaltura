(function($, core, undefined) {

var _ready,
    Shell = core.widget('shell', {
        _create: function() {
            _ready = this._Ready();
        }
    }),
    shell = new Shell('html');
core.Shell = Shell;
core.shell = window.nethelpshell = $.nethelpshell = shell;

function proxy(fn, context) {
    // proxy for a method call without arguments
    if (typeof context === 'string') {
        var tmp = fn[context];
        context = fn;
        fn = tmp;
    }
    return $.isFunction(fn) ? function() {
        return fn.call(context);
    } : $.noop;
}

shell.extend = $.extend;

shell.extend({
    isTopicOnlyMode: function() {
        var self = this,
            key = '_topicOnlyMode';
        return key in self ? self[key] : 
            (self[key] = /(^|\?|&)topiconly=true($|&)/i.test(location.search));
    },
    print: function() {
        var self = this;
        self.trigger('beforeprint');
        print();
        self.trigger('afterprint');
    },
    mailtoUrl: function() {
        return 'mailto:' + core.str(shell.setting('strings.emailAddress'), '') + 
            '?subject=' + encodeURIComponent('RE:"' + document.title + '"') + 
            '&body=' + encodeURIComponent(location.href);
    }
});

//#region settings
var cacheTypesFlags = {
    "": { "object": true },
    "string": {}
};
function parseTypesFlags(str) {
    var t = cacheTypesFlags[str];
    if (t) {
        return t;
    }
    t = {};
    $.each($.trim(str).split(/\s+/), function(i, v) {
        t[v] = true;
    });
    return cacheTypesFlags[str] = t;
}
shell.extend({
    settings: {
        general: {
            updateTitle: true
        },
        theme: {},
        references: {}
    },
    defaultSettings: {},
    setDefaultSettings: function(merge) {
        this.defaultSettings = $.extend(true, merge && this.defaultSettings || {}, this.settings);
    },
    //#region normalization
    _norm: [],
    normSettings: function(settings) {
        var self = this,
            norm = self._norm;
        settings = settings || self.settings;
        if (norm.length) {
            core.call(norm, self, settings);
        }
        return settings;
    },
    addSettingsNorm: function(fn) {
        // fn: function(settings) { }
        if (fn && $.isFunction(fn)) {
            this._norm.push(fn);
        }
    },
    removeSettingsNorm: function(fn) {
        var norm = this._norm;
        for (var i = norm.length; i; --i) {
            if (norm[i] == fn) {
                norm.splice(i, 1);
                break;
            }
        }
    },
    //#endregion
    mergeSettings: function(settings, overwrite) {
        if (settings) {
            var self = this;
            return self.settings = overwrite !== false ?
                $.extend(true, self.settings, settings || {}) :
                $.extend(true, {}, settings, self.settings);
        }
    },
    setting: function(setter, settings, prop, options) {
        if (typeof setter !== 'boolean') {
            options = prop;
            prop = settings;
            settings = setter;
            setter = false;
        }
        if (typeof settings === 'string') {
            options = prop;
            prop = settings;
            settings = this.settings;
        }
        var self = this,
            props = prop.split('.'),
            setting = settings,
            len = props.length - 1,
            i, t;
        if (setter) {
            // setter
            for (i = 0; i < len; ++i) {
                prop = props[i];
                setting = settings[prop];
                if (!setting || typeof setting !== 'object') {
                    settings = settings[prop] = {};
                }
                else {
                    settings = setting;
                }
            }
            settings[props[len]] = options;
        }
        else {
            // getter
            for (i = 0; (t = props[i]) && setting != undefined; ++i) {
                setting = setting[t];
                if (setting === null && i < len) {
                    setting = undefined;
                }
            }
            options = options || {};
            if ($.isFunction(options.convert)) {
                return options.convert(setting);
            }
            t = options.parse;
            if (t && typeof setting === 'string') {
                setting = $.isFunction(t) ? t(setting) : self.parseSetting(setting);
            }
            if ('types' in options && !parseTypesFlags(options.types)[$.type(setting)]) {
                setting = options.mismatch;
            }
            if (setting === undefined && 'isundef' in options) {
                return options.isundef;
            }
            if (setting == null && 'isnull' in options) {
                return options.isnull;
            }
            if (setting === '' || setting == null) {
                if ('isempty' in options) {
                    return options.isempty;
                }
                if (options.defualtIfEmpty) {
                    options.defaultIfEmpty = false;
                    return self.setting(false, self.defaultSettings, prop, options);
                }
            }
            return setting;
        }
    },
    parseSetting: function(val) {
        var t;
        if (/^true$/i.test(val)) {
            val = true;
        }
        else if (/^false$/i.test(val)) {
            val = false;
        }
        else if (val === 'null') {
            val = null;
        }
        else if (/\d+/.test(val) && !isNaN(t = Number(val))) {
            val = t;
        }
        return val;
    },
    readXmlSettings: function(xml, options) {
        if (typeof xml === 'string') {
            xml = $.parseXML(xml);
        }
        var self = this,
            result = {};
        options = options || {};
        xml = $('settings', xml);

        function f(settings, node) {
            var prop = node.nodeName,
                t,
                val;
            node = $(node);
            t = node.children();
            if (t.length) {
                val = {};
                t.each(function() { f(val, this); });
            }
            else {
                val = $.trim(node.text());
                if (val && options.parse !== false) {
                    val = self.parseSetting(val);
                }
            }
            settings[prop] = prop in settings ?
                core.concat(settings[prop], val) : val;
        }
        xml.children().each(function() { f(result, this); });

        if (options.norm) {
            self.normSettings(result);
        }

        return result;
    },
    loadSettings: function(options) {
        /*
            options: {
                url,
                dataType,
                async = true,
                normalize = true,
                merge = true,
                overwrite = true
            }
        */
        if (typeof options === 'string') {
            options = { url: options };
        }
        if (!options || !options.url) {
            return;
        }
        var self = this,
            url = options.url,
            type = options.dataType,
            d = $.Deferred();
        if (!type) {
            type = url.substring(url.length - 2) === 'js' ? 'json' : 'xml';
        }
        $.ajax({
            url: url,
            dataType: type,
            async: options.async !== false,
            success: function(settings) {
                if (type == 'xml') {
                    settings = self.readXmlSettings(settings);
                }
                // move node general to top-level
                settings = $.extend({}, settings.general, settings);
                if (options.norm) {
                    core.call(options.norm, self, settings);
                }
                if (options.normalize !== false) {
                    settings = self.normSettings(settings);
                }
                if (options.merge !== false) {
                    self.mergeSettings(settings, options.overwrite);
                }
                d.resolveWith(self, [ settings ]);
            },
            error: function(request, status, error) {
                core.error('in "loadSettings" (ajax): ' + error);
                d.rejectWith(self, [ { error: error, status: status, request: request } ]);
            }
        });
        return d;
    }
});
shell.setDefaultSettings();
//#endregion

//#region infrastructure
$.each({
    widget: ['_widgets', 'removeWidget', 'createWidgets' ],
    driver: [ '_drivers', 'removeDriver', 'createDrivers' ],
    plugin: [ '_plugins', 'removePlugin', 'createPlugins' ]
}, function(prop, props) {
    var collProp = props[0],
        removeProp = props[1],
        createProp = props[2];
    shell[collProp] = [];
    shell[prop] = function(descriptor, ctr) {
        if (descriptor && core.isString(descriptor) && core.isFunction(ctr)) {
            descriptor = {
                name: descriptor,
                create: ctr
            };
        }
        var self = this,
            coll = self[collProp],
            i = coll.length,
            read = typeof descriptor === 'string',
            name = read ? descriptor : descriptor.name,
            d, append = true;
        if (name) {
            while(i--) {
                d = coll[i];
                if (d.name === name) {
                    if (read) {
                        return d;
                    }
                    self[removeProp](d);
                    coll[i] = descriptor;
                    append = false;
                    break;
                }
            }
            if (!read) {
                append && coll.push(descriptor);
                if (self[collProp + 'Created']) {
                    self[createProp]([ descriptor ]);
                }
            }
        }
    };
    shell[removeProp] = function(descriptor) {
        if (typeof descriptor === 'object') {
            if (descriptor.created && $.isFunction(descriptor.remove)) {
                descriptor.remove();
            }
            return;
        }
        var coll = this[collProp],
            i = coll.length,
            d;
        while(i--) {
            d = coll[i];
            if (d && d.name === descriptor) {
                this[removeProp](d);
                coll.splice(i, 1);
                return;
            }
        }
    };
    shell[createProp] = function(coll) {
        var proc = $.when();
        $.each(coll || this[collProp], function(i, item, def) {
            if (item && $.isFunction(item.create) && !item.created) {
                var def = item.create(), f;
                item.created = true;
                if (def && def.promise) {
                    proc = proc.pipe(f = function() {
                        return def;
                    }, f);
                }
            }
        });
        this[collProp + 'Created'] = true;
        return proc;
    };
});
shell.extend({
    uiTheme: function(url) {
        var themes = $('head link.ui-theme'),
            tmpl = '<link rel="stylesheet" type="text/css" class="ui-theme" />',
            cur = themes.last(),
            cacheSize = shell.setting('theme.jqueryuiCacheSize');
        cacheSize = typeof cacheSize !== 'number' || cacheSize < 0 ? 0 : cacheSize;
        if (!cur.length) {
            cur = $('head link').filter(function() {
                return /\bstylesheet\b/i.test(this.rel || '') &&
                    /jquery[-\.]ui/.test(this.href || '');
            }).last().addClass('ui-theme');
        }
        if (!url) {
            return cur.url();
        }
        url = core.expandUrl(url).toLowerCase();
        if (!cur.length) {
            $(tmpl).appendTo('head')[0].href = url;
            return;
        }
        if (cur.url() !== url) {
            if (cacheSize === 0) {
                themes.not(cur).remove();
                cur[0].href = url;
            }
            else {
                $(tmpl).insertAfter(cur)[0].href = url;
                if (themes.length > cacheSize) {
                    themes.slice(0, themes.length - cacheSize).remove();
                }
            }
        }
    },
    createWidgets: function() {
        var self = this,
            finish = $.Deferred(),
            proc = $.when();
        $.each(self._widgets, function(i, w) {
            var name = w && w.name,
                element, options, widget, events;
            if (name && !w.created && (!$.isFunction(w.check) || w.check())) {
                if ($.isFunction(w.create)) {
                    widget = w.create();
                }
                else {
                    element = w.element;
                    element = $.isFunction(element) ? w.element() : 
                        (shell.setting(name + '.element') || element || ('#' + name));
                    options = w.options;
                    options = ($.isFunction(options) ? w.options() : 
                        (shell.setting(name + '.options') || options)) || {};
                    options.descriptor = w;
                    widget = core[w.widgetName || name](element, options);
                }
                if (widget) {
                    self[name] = w.instance = widget;
                    events = w.events;
                    if (events !== false) {
                        if ($.isFunction(events)) {
                            w.events(widget);
                        }
                        else {
                            if (typeof events !== 'string') {
                                events = '*';
                            }
                            widget.bind(events, function(e, d) {
                                e = (events === '*') && e.originalEvent || e;
                                if (!e.isHandled()) {
                                    shell.trigger(e.type, e, d);
                                }
                            });
                        }
                    }
                    if ($.isFunction(w.init)) {
                        w.init(widget);
                    }
                    if (widget.ready) {
                        widget.ready(proxy(w, 'ready'), proxy(w, 'cancel'));
                        if (!w.nowait) {
                            proc = proc.pipe(widget.ready, widget.ready);
                        }
                    }
                    else if ($.isFunction(w.ready)) {
                        w.ready();
                    }
                    if ($.isFunction(w.finish)) {
                        finish.done(proxy(w, 'finish'));
                    }
                    if ($.isFunction(w.shellready)) {
                        shell.ready().done(proxy(w, 'shellready'));
                    }
                    w.created = true;
                }
            }
        });
        proc.always(function() {
            finish.resolveWith(self);
        });
        self._widgetsCreated = true;
        return proc;
    },
    switchTab: function(tab) {
        // This method must be implemented in theme script.
    },
    start: function(options) {
        /*
            options: {
                url: url of target-settings
                dataType: "json"|"xml"
            }
        */
        shell.setDefaultSettings();
        if (typeof options === 'string') {
            options = { url: options };
        }
        else if (!options || !options.url || typeof options.url !== 'string') {
            options = { url: 'settings.xml', dataType: 'xml' };
        }
        var d = $.Deferred(),
            targetSettings = {},
            headHtml = '', bodyHtml = '',
            topicOnly = options.topicOnly,
            rootPath = options.rootPath,
            path,
            f,
            abort = false;
        shell.writeHead = function() {
            headHtml && document.write(headHtml);
            shell.trigger('writehead');
        };
        shell.writeBody = function() {
            if (bodyHtml) {
                if (topicOnly) {
                    var body = $('body'),
                        bodyInner = body.wrapInner('<div />').children(),
                        c1topic = body.prepend(bodyHtml).find('#c1topic');
                    if (c1topic.length && !$.contains(bodyInner[0], c1topic[0])) {
                        c1topic.append(bodyInner);
                    }
                    $(bodyInner[0].firstChild).unwrap();
                }
                else {
                    document.write(bodyHtml);
                }
            }
            shell.trigger('writebody');
        };
        //#region load target settings and load theme
        options.async = options.merge = false;
        function normThemeSettings(settings) {
            var s = shell.setting(settings, 'theme.jqueryui');
            if (s) {
                settings.theme.jqueryui = core.replaceUrl(s, path);
            }
            s = shell.setting(settings, 'pageHeader.logoImage');
            if (s) {
                settings.pageHeader.logoImage = core.replaceUrl(s, path);
            }
        }
        shell.loadSettings(options).pipe(function(settings) {
            if (rootPath) {
                core.each([ 'theme.url', 'theme.configUrl', 'theme.jqueryui',
                        'references.css', 'references.js' ], function(stg) {
                    var url = shell.setting(settings, stg);
                    if (core.isString(url)) {
                        url = core.replaceUrl(url, rootPath);
                    }
                    else if (core.isArray(url)) {
                        url = $.map(url, function(i) {
                            return core.replaceUrl(i, rootPath);
                        });
                    }
                    shell.setting(true, settings, stg, url);
                });
            }
            var t = settings.theme
                url = t.url,
                dt = t.dataType;
            targetSettings = settings;
            path = core.getUrlPath(url);
            return shell.loadSettings({
                url: url,
                dataType: dt,
                async: false,
                norm: normThemeSettings
            });
        }, function(error) {
            // error
            var exc = error.error;
            if ($.browser.webkit && exc && exc.code == 101) {
                bodyHtml = '<p>Due to security limitations, this version of Chrome ' +
                    'browser does not work correctly with NetHelp stored in local ' +
                    'files on your computer. You can use this Chrome version to view ' +
                    'NetHelp deployed on the web without limitations, but for local ' +
                    'files please use a different browser.</p>';
            }
            else if ($.browser.opera) {
                bodyHtml = '<p>NetHelp 2.0 engine failed to load. It may be caused by ' +
                    'security settings of the Opera browser disallowing AJAX access ' +
                    'to local files (file XMLHttpRequest). Try to change the setting ' +
                    'or use another browser.</p>';
            }
            else {
                bodyHtml = '<p>Error: NetHelp 2.0 engine failed to load.</p>';
            }
            window.console && console.log(error);
            abort = true;
        }).pipe(function(settings) {
            // load settings from <theme>/<configUrl> if it is defined
            var t = targetSettings.theme
                url = t.configUrl,
                dt = t.configDataType;
            return url ? shell.loadSettings({
                url: url,
                dataType: dt,
                async: false,
                norm: normThemeSettings
            }) : settings;
        }).pipe(function(settings) {
            // now theme settings are loaded and merged
            shell.mergeSettings(targetSettings);
            // loading layout
            return $.ajax({
                url: path + (shell.setting(settings, 'theme.layout') || 'layout.html'),
                dataType: 'text',
                async: false
            });
        }).pipe(function(resp, status, req) {
            core.accessibility = /^section\s*508$/i.test(shell.settings.accessibilityMode || '');
            var t;

            var fr = $('<iframe />').hide().appendTo('head'),
                doc = fr[0].contentWindow.document;
            doc.open();
            doc.write(core.escapeScripts(req.responseText));
            doc.close();
            doc = $(doc);

            //#region replace Urls
            core.replaceUrls(doc, [
                ['a, area, link', 'href'],
                ['script, img, input', 'src'],
                ['script[type="_js"]', 'js_src']
            ], path);
            //#endregion
            shell.trigger('themelayout', undefined, {
                frame: fr,
                document: doc
            });
            doc.find('script, link, style, meta').addClass('from-theme-layout');
            //#region current ui-theme
            t = doc.find('head link.ui-theme').remove();
            t = (/\?(?:.*&)?jqueryui=([^&]*)(?:&|$)/i.exec(location.search) || [])[1] ||
                shell.setting('theme.jqueryui') || t.last().attr('href');
            if (t) {
                $('link.ui-theme:last').attr('href', t);
            }
            //#endregion
            var docTitle = doc.find('title').text();
            headHtml += core.unescapeScripts(doc.find('head').html())
                .replace(/<title[\s\S]*?>[\s\S]*?<\/title>/gi, '');
            // add scripts from settings
            t = shell.settings.references || {};
            if (!$.isArray(t.css)) {
                t.css = t.css ? [ t.css ] : [];
            }
            if (!$.isArray(t.js)) {
                t.js = t.js ? [ t.js ] : [];
            }
            $.each(t.css, function(i, v) {
                headHtml += '<link rel="stylesheet" type="text/css" href="' + v + '" class="from-settings" />';
            });
            $.each(t.js, function(i, v) {
                headHtml += '<script type="text/javascript" src="' + v + '" class="from-settings"></script>';
            });
            bodyHtml = core.unescapeScripts(doc.find('body').html());
            fr.remove();
        }).done(function() {
            $(proxy(d, 'resolve')); // start when dom is ready
        });
        //#endregion
        return abort ? $.Deferred().reject() : (topicOnly ? d : d
                .pipe(f = proxy(shell, 'createDrivers'), f)
                .pipe(f = proxy(shell, 'createWidgets'), f)
                .pipe(f = proxy(shell, 'createPlugins'), f))
            .then(f = _ready.fire, f);
    }
});
//#endregion

})(jQuery, nethelp);