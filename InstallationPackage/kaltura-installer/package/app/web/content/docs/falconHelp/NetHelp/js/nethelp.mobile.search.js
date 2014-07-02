(function ($, core, undefined) {

    nethelp.search.filterDataKey = 'input';
    var _baseBindEvents = nethelp.search.prototype._bindEvents;
    nethelp.search.prototype.options.scrollElement = [];
    nethelp.search.prototype.options.pageSize = 50;
    nethelp.search.prototype._bindEvents = function () {
        var self = this;
        _baseBindEvents.call(self);
        
        nethelp.shell.bind('searchnextpage searchdisabled searchnotfound', function () {
            self.element.listview('refresh');
        });

        $(document).scroll(function (e) {
            if ($.mobile.activePage && $.mobile.activePage[0] == $('#c1searchPage')[0]) {
                if (self.element[0].offsetTop + self.element.innerHeight() - $(window).height() - $(document).scrollTop() < 60) {
                    self.nextPage();
                }
            }
        });
    }

    function initPage() {
        var self = nethelp.shell.search,
            filterElement = self.filterElement,
            urlObj = $.mobile.path.parseUrl(window.location.href);
        if (urlObj.hash.search(/.*query=/) !== -1) {
            filterElement.val(urlObj.hash.replace(/.*query=/, ""));
        }
    }

    $('#c1searchPage').live('pageshow', function (event) {
        nethelp.shell.ready(initPage);
    });


})(jQuery, nethelp);
