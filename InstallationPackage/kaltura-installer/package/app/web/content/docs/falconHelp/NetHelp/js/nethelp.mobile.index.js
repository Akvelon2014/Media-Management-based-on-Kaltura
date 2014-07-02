(function ($, core, undefined) {

    nethelp.index.filterDataKey = 'input';
    var _baseBindEvents = nethelp.index.prototype._bindEvents;
    nethelp.index.prototype.options.scrollElement = [];
    nethelp.index.prototype.options.pageSize = 50;
    nethelp.index.prototype._bindEvents = function () {
        var self = this;
        _baseBindEvents.call(self);

        nethelp.shell.bind('indexnextpage indexerror indexnotfound indexinsufficientfilter indexemptyFilter', function () {
            var level = 0,
                f = function () {
                    level++;
                    var ul = $(this),
                        items = ul.children(),
                        parent = ul.closest('li');
                    items.unwrap();
                    items.detach();
                    items.css('margin-left', level * 30 + 'px');
                    items.insertAfter(parent);
                    items.children('ul').each(f);
                    level--;
                };
            self.element.children('li').children('ul').each(f);
            self.element.listview('refresh');
        });

        $(document).scroll(function (e) {
            if ($.mobile.activePage && $.mobile.activePage[0] == $('#c1indexPage')[0]) {
                if (self.element[0].offsetTop + self.element.innerHeight() - $(window).height() - $(document).scrollTop() < 60) {
                    self.nextPage();
                }
            }
        });
    }

})(jQuery, nethelp);