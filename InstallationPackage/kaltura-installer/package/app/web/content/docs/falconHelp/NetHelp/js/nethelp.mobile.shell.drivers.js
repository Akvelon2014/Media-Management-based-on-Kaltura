(function ($, core, shell, undefined) {

    //#region init jQuery Mobile pages
    shell.driver('createpages', function () {
        $('#c1indexPage').page();
        $('#c1searchPage').page();
    });
    //#endregion

})(jQuery, nethelp, nethelpshell);