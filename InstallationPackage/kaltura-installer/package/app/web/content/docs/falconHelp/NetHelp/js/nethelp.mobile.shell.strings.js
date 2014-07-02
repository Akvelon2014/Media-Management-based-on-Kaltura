(function ($, core, shell, undefined) {

var drv = nethelp.shell.driver('stringsMap');
_baseGetElem = drv._getElem;
drv._getElem = function(elem) {
    elem = _baseGetElem.call(this, elem).find('.ui-btn-text');
    return elem.length ? elem : elem.end();
};

})(jQuery, nethelp, nethelpshell);