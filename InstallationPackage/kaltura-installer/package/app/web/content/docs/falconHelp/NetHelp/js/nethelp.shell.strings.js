(function($, core, shell, undefined) {

var reSelector = /[#~\.\>\:\[\s]/;

shell.driver({
    name: 'stringsMap',
    create: function() {
        var strings = shell.settings.strings,
            getElem = this._getElem;
        if (!strings) {
            shell.settings.strings = strings = {};
        }
        // window title
        if (!strings.title) {
            strings.title = strings.pageHeaderText || document.title || 'No title';
        }
        document.title = strings.title;
        core.each(shell.settings.stringsMap || [], function(str, elem) {
            elem && str != null && getElem(elem).html(core.str(str.indexOf('.') > -1 ? shell.setting(str) : strings[str]));
        });
    },
    _getElem: function (elem) {
        return elem && $((reSelector.test(elem) ? '' : '#') + elem);
    }
});

})(jQuery, nethelp, nethelpshell);