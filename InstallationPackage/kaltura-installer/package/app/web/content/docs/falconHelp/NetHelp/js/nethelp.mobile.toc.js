(function ($, core, undefined) {
    nethelp.toc.prototype._bindEvents = function () {
        var self = this,
            element = self.element;        
        element.delegate('.c1-toc-item', 'click', function (e) {
            var target = $(this),
                url = target.attr('href'),
                li = self.getItem(target);
            e.preventDefault();
            var data = self.getData(li);
            if (li.length && !li.hasClass('c1-toc-item-leaf')) {
                $.mobile.changePage('#c1tocPage?path=' + data.path);
                return false;
            }
            else {
                var toc = nethelp.shell.toc;
                if (toc) {
                    var page = $('#c1tocPage'),
                        tocUL = $('#c1toc'),
                        markup = toc._itemsHtml([]);
                    tocUL.html(markup);
                    tocUL.listview('refresh');
                }
                self.select(li);
                self.expand(li);
            }
            return false;
        });
    }

    nethelp.toc.prototype._getTocUrl = function () {
        var self = this,
            options = self.options,
            url = (options.dataPath || '') + options.dataSource;
        if ($.mobile.path.isRelativeUrl(url))
            url = $.mobile.path.makeUrlAbsolute(url, nethelp.shell.baseUrl);
        return url;
    }

    nethelp.toc.prototype.options.itemTemplate = '<li id="#{id}" data-icon="#{icon || false}" class="#{itemClass}"><a href="#{this.url || \'javascript:void(0)\'}" <#if(this.target){#> target="#{target}" <#}#> title="#{tooltip}" class="inner c1-toc-text">#{title}' +
            '</a>#{subtree}</li>';


    nethelp.toc.prototype.options.icons.collapsed = 'arrow-d';
    nethelp.toc.prototype.options.icons.leaf = 'arrow-r';

})(jQuery, nethelp);