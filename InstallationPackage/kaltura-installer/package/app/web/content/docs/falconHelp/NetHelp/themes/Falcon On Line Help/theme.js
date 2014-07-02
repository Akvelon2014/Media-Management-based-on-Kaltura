(function($, core, shell, undefined) {

shell.mergeSettings({
    rightToLeft: false,
    stringsMap: {
        "c1tabTocLabel": "toc.label",
        "c1tabIndexLabel": "index.label",
        "c1tabSearchLabel": "search.label",
        "c1headerText": "pageHeaderText",
        "c1topicSpinnerText": "topicSpinnerText"
    },
    strings: {
        topicSpinnerText: 'Loading...'
    },
    pageHeader: {
        visible: true,
        height: 40,
        logoImage: '',
        showText: true
    },
    topic: {
        jqueryuiStyle: true
    },
    toc: {
        label: 'Contents'
    },
    index: {
        visible: true,
        label: 'Index',
        hideEmpty: true
    },
    search: {
        visible: true,
        label: 'Search'
    }
}, false);
shell.bind('writebody', function() {
    // RTL
    if (shell.settings.rightToLeft) {
        $('body').attr('dir', 'rtl').css('direction', 'rtl');
    }
    // header
    var t = core.str(shell.setting('pageHeader.logoImage'), '');
    if (t) {
        $('#c1headerLogo').attr('src', t);
    }
    else {
        $('#c1headerLogo').hide();
    }
    t = shell.setting('pageHeader.visible');
    if (!t && t != undefined) {
        $('#c1header').hide();
        $('#c1main').css('top', 0);
    }
    else {
        t = shell.setting('pageHeader.height');
        if (typeof t === 'number' && t > 0) {
            $('#c1header').height(t);
            $('#c1main').css('top', t + 2);
        }
        if (shell.setting('pageHeader.showText', { types: 'boolean' }) === false) {
            $('#c1headerText').hide();
        }
    }
});
shell.plugin({
    name: 'theme',
    create: function() {
        var theme = shell.theme = shell.theme || {}, t;
        if (shell.isTopicOnlyMode()) {
            $('body').addClass('topic-only');
        }
        else {
            // tabs
            var tabs = $('#c1sideTabs').tabs(),
                tabsHeader = $('#c1sideTabsHeader'),
                tabsPanel = $('#c1sideTabsPanel'),
                tabsNames = { toc: 0, index: 1, search: 2, 0: 0, 1: 1, 2: 2 },
                tabsHidden = {},
                rememberActiveTab = !!shell.setting('theme.rememberActiveTab'),
                cookieName = 'c1sideActiveTab',
                activeTab = rememberActiveTab && $.cookie && $.cookie(cookieName) ||
                    tabsNames[((/(?:\?|&|^)tab=([^&]+)(?:&|$)/i.exec(location.search) || [])[1] || '0').toLowerCase()],
                index = shell.index,
                t;
            $.each([ 'index', 'search' ], function(i, tab) {
                t = shell.setting(tab + '.visible');
                if (!i && t) { // tab === 'index'
                    t = index.hasKeywords() || !shell.setting('index.hideEmpty');
                }
                if (!t && t != undefined) {
                    i = tabsNames[tab];
                    tabsHidden[i] = true;
                    tabsHeader.children().eq(i)
                        .add(tabsPanel.children().eq(i))
                        .hide();
                }
            });
            var recalcTabsTop = theme.recalcTabsTop = function() {
                tabsPanel.css('top', core.px2em(tabsPanel, tabsHeader.outerHeight() + parseFloat(tabs.css('paddingTop')) + 3));
            }
            tabs.bind('tabsshow', function(e, d) {
                if (rememberActiveTab && $.cookie) {
                    $.cookie(cookieName, d.index || null, {
                        expires: 365
                    });
                }
            });
            if (activeTab && !tabsHidden[activeTab]) {
                tabs.tabs('select', +activeTab);
            }
            shell.switchTab = function(tab) {
                tab = tabsNames[tab];
                if (tab != undefined) {
                    tabs.tabs('select', +tab);
                }
            };
            // splitter
            var splitter = shell.splitter = core.splitter('#c1splitter', 
                core.splitter.settings2options(core.extend({
                    rightSide: !!shell.settings.rightToLeft,
                    side: '#c1side',
                    main: '#c1content'
                }, shell.setting('splitter'))));
            splitter.bind('splitter', function(e, d) {
                recalcTabsTop();
                shell.trigger('splitter', e, d);
            });
            recalcTabsTop();
            // content
            if (shell.setting('topic.jqueryuiStyle') !== false) {
                $('#c1topicPanel').addClass('ui-widget-content');
            }
            // toolbar
            $('#c1topBar > .buttonset').buttonset();
            // topic-frame
            function calcFrameTop() {
                var fr = $('#c1topic .topic-frame');
                if (fr.length) {
                    fr.css('top', $('#c1topicBar').outerHeight() + 2);
                }
            }
            $(window).resize(calcFrameTop);
            shell.bind('topicupdate breadcrumbsupdate', calcFrameTop);
        }
    }
});

})(jQuery, nethelp, nethelpshell);