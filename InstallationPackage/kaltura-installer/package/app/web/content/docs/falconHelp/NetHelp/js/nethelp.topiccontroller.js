(function ($, core, shell, undefined) {
    var rootPath = $('#nethelpRootPath').attr('href') || '',
        d = shell.start({
            url: rootPath + 'settings.xml',
            dataType: 'xml',
            rootPath: rootPath,
            topicOnly: true
        });
    shell.writeHead();
    d.done(function () {
        if (!$('meta[name="NetHelpPlugin"][content="sandcastle"]').length) {
            shell.writeBody();
        }
        else {
            $('head link.from-settings').remove();
            $('body').attr('class', "ui-widget-content content-topic abs fill-h fill-v scroll")
                .attr('id', 'topicBlock')
                .css('height', "100%");
        }
        shell.driver('popup').create();
        shell.driver('theme').create();
        shell.plugin('collapsibleSection').create();
        shell.plugin('inlineText').create();
        shell.plugin('relatedTopics').create();
        shell.plugin('popupText').create();
        shell.plugin('topicLinks').create();
        shell.plugin('k-link').create();
        shell.plugin('a-link').create();
        shell.trigger('topicupdate');

        //#region override methods
        var _topicLink = shell.topicLink;
        shell.topicLink = function (ref, options) {
            if (!ref || typeof ref !== 'string') {
                return;
            }
            options = options || {};
            var target = options.target,
                el = options.element,
                e = options.event;
            if (e && e.isDefaultPrevented() || !options.href || target === 'popup') {
                if (target !== 'popup')
                    options.target = undefined;
                return _topicLink.apply(this, arguments);
            }
            if (el && target && !/^_(blank|self|parent|top)$/.test(target)) {
                el.removeAttr('target');
            }
        };
        //#endregion
    });
})(jQuery, nethelp, nethelpshell);