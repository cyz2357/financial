

//树扩展
(function ($) {

    $.QsTree = {
        options: [],
        getOpts: function (id)
        {
            for (var i in $.QsTree.options)
            {
                if (id == $.QsTree.options[i].id)
                {
                    return $.QsTree.options[i];
                }
            }
            return null;
        }
    };

    //树扩展
    $.fn.extend({
        QsTree: function (options) {
            var _this = this;
            var id = $(_this).attr("id");
            var defaults = {
                loadUrl: $(_this).attr("data-url"),
                id: id
            };
            var opts = $.extend(defaults, options);
            $(this).attr("data-url", opts.loadUrl);
            $(this).loadTree(opts.loadUrl);
            $("#pannel_" + id + " .tree_refresh").click(function () {
                $("#" + id).refreshTree();
            });
            $.QsTree.options.push(opts);
        },
        refreshTree: function () {
            var url = $(this).attr("data-url");
            var id = $(this).attr("id");
            if (window.sessionStorage) {
                sessionStorage.removeItem(url);
            }
            //$(this).jstree('destroy');
            $("#pannel_" + id + " .tree_refresh").addClass("qs-rotat");
            $(this).loadTree(url, function () {
                $("#pannel_" + id + " .tree_refresh")
                        .removeClass("qs-rotat");
            });
        },
        loadTree: function (url,callback) {
            var id = $(this).attr("id");
            $(this).innerLoading();
            
            $.AuthGet(url, function (result) {
                var instance = $('#' + id).jstree(true);
                if (instance)
                {
                    instance.destroy();
                }
                $("#" + id).jstree({
                    'core': {
                        'multiple': false,
                        'data': result
                    }
                });
                $("#" + id).on("changed.jstree", function (e, data) {
                    var opts = $.QsTree.getOpts(id);
                    var _id = data.selected[0];
                    opts.treeNodeClick(_id);
                });
                if (callback)
                {
                    callback();
                }
            });
        },
        lockTree: function () {
            var id = $(this).attr("id");
            var instance = $('#' + id).jstree(true);
            instance.disable_node(instance.get_node($("li[role='treeitem'] a"),true));
        },
        unlockTree: function () {
            var id = $(this).attr("id");
            var instance = $('#' + id).jstree(true);
        }
    });
})(jQuery);
