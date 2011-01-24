(function($) {
    kt.lib.str_replace = function(search, replace, subject, count) {
        var i = 0, j = 0, temp = '', repl = '', sl = 0, fl = 0,
        f = [].concat(search),
        r = [].concat(replace),
        s = subject,
        ra = r instanceof Array, sa = s instanceof Array;
        s = [].concat(s);

        if (count) {
            this.window[count] = 0;
        }

        for (i = 0, sl = s.length; i < sl; i++) {
            if (s[i] === '') {
                continue;
            }

            for (j = 0, fl = f.length; j < fl; j++) {
                temp = s[i]+'';
                repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
                s[i] = (temp).split(f[j]).join(repl);
                if (count && (s[i] !== temp)) {
                    this.window[count] += (temp.length-s[i].length)/f[j].length;
                }
            }
        }

        return sa ? s : s[0];
    };

    kt.lib.parseTemplate = function(str, obj) {
        str = str + '';
        var fr = new Array();
        var to = new Array();
        if (typeof(obj) == 'object') {
            for(var item in obj) {
                fr[fr.length] = '[' + item + ']';
                to[to.length] = obj[item] + '';
            }

        }
        return kt.lib.str_replace(fr, to, str);
    };

    kt.pages.browse = {};

    kt.pages.browse.addDocumentItem = function(item) {
        item.is_shortcut = item.is_shortcut ? '' : ' not_supported';
        item.is_immutable = item.is_immutable ? '' : ' not_supported';
        item.is_checkedout = item.is_checkedout ? '' : ' not_supported';
        //item.document_link = 'view.php?fDocumentId=' + item.id;
        item.document_link = item.document_url;

        var newItem = jQuery(jQuery('.fragment.document')[0]).html();
        newItem = kt.lib.parseTemplate(newItem, item);
        var elem = jQuery(newItem);
        var mime = jQuery('.doc.icon',elem).attr('style', item.mimeicon);
        jQuery('.page.page_' + kt.pages.browse.curPage).append(elem);
    };

    kt.pages.browse.curPage = 1;
    kt.pages.browse.viewPage = function(pageNum) {
        if (pageNum < 1) { pageNum = 1; }
        var pageItem = jQuery('.paginate>li.page_' + pageNum);

        if (pageItem.length <= 0) {
            return;
        }

        // around here is where you would fire off a request to get more content, so let's give that a try
        // and see what comes back :)
        var testOutput = jQuery.get('http://paul.knowledgetree.com/browse.php?action=paging&fFolderId=6&page=' + pageNum);
        console.log(testOutput);

        jQuery('.page').hide(0, function() {jQuery('.page.page_' + pageNum).show(0);})
        jQuery('.paginate>li.item').removeClass('highlight');

        pageItem.addClass('highlight');

        kt.pages.browse.curPage = new Number(pageNum);
    };

    kt.pages.browse.nextPage = function() {
        kt.pages.browse.viewPage(kt.pages.browse.curPage + 1);
        return false;
    };

    kt.pages.browse.prevPage = function() {
        kt.pages.browse.viewPage(kt.pages.browse.curPage - 1);
        return false;
    };

    kt.pages.browse.selectAllItems = function() {
        jQuery('.itemContainer .item .checkbox > input:checkbox:enabled').each(function() {
            if (!this.checked) { jQuery(this).click(); }
            jQuery(this).parents('.item').addClass('highlighted');
        });
        kt.pages.browse.setBulkActionMenuStatus();
        return false;
    }

    kt.pages.browse.deSelectAllItems = function() {
        jQuery('.itemContainer .item .checkbox>input:checkbox:enabled').each(function() {
            if (this.checked)jQuery(this).click();
            jQuery(this).parents('.item').removeClass('highlighted');
        });
        kt.pages.browse.setBulkActionMenuStatus();
        return false;
    }

    kt.lib.shortcut.add("ctrl+right", kt.pages.browse.nextPage);
    kt.lib.shortcut.add("ctrl+left", kt.pages.browse.prevPage);
    kt.lib.shortcut.add("ctrl+a", kt.pages.browse.selectAllItems);
    kt.lib.shortcut.add("ctrl+shift+a", kt.pages.browse.deSelectAllItems);

    $(document).ready(function() {
        kt.pages.browse.viewPage(1);
        kt.pages.browse.setBulkActionMenuStatus = function() {
            var selectedItems = $(".itemContainer .item .checkbox>input:checkbox:checked:enabled").length;
            if (selectedItems > 0) {
                $('.browseView.bulkActionMenu td:first-child').removeClass('disabled');
                $('.browseView.bulkActionMenu td:first-child input[type="submit"]').attr('disabled', '');
                //				$('.browseView.bulkActionMenu').slideDown(350,function() {kt.lib.setFooter();});
                //				$('.browseView.bulkActionMenu').slideDown(350);
                $('.browseView.bulkActionMenu .status').html(selectedItems + '&nbsp;Item(s)&nbsp;Selected');
            } else {
                $('.browseView.bulkActionMenu td:first-child').addClass('disabled');
                $('.browseView.bulkActionMenu td:first-child input[type="submit"]').attr('disabled', 'disabled');
                //				$('.browseView.bulkActionMenu').hide(200);
                //				$('.browseView.bulkActionMenu').hide(200,function() {kt.lib.setFooter();});
                $('.browseView.bulkActionMenu .status').html('');
            }
        }

        $('.actionIcon.comments').click(function() {
            var docItem=$(this).parents('.item')[0];
            var thisField=$('.expanderField',docItem);
            $('.expanderField').not(thisField[0]).hide();
            thisField.toggle();
        });

        $("table.doc.item input:checkbox").click(function() {
            kt.pages.browse.setBulkActionMenuStatus();

            if ($(this).is(':checked')) {
                $(this).parent().parent().parent().parent().addClass("highlighted");
            } else {
                $(this).parent().parent().parent().parent().removeClass("highlighted");
            }
        });

        $("table.doc.item input:checkbox:checked").parent().parent().parent().parent().addClass("highlighted");

        $("table.folder.item input:checkbox").click(function() {
            kt.pages.browse.setBulkActionMenuStatus();

            if ($(this).is(':checked')) {
                $(this).parent().parent().parent().parent().addClass("highlighted");
            } else {
                $(this).parent().parent().parent().parent().removeClass("highlighted");
            }
        });

        $("table.folder.item input:checkbox:checked").parent().parent().parent().parent().addClass("highlighted");

        if ($.browser.msie) {
        (function() {
            function hide() {
                $(".doc.browseView:first-child .item .actionMenu .actions>ul").css({display: '', visibility: ''});
            }

            $(".doc.browseView:first-child .item .actionMenu .actions>ul").css({display: 'block', visibility: 'hidden'});
            setTimeout(hide,200);
        })();
        }

        kt.pages.browse.setBulkActionMenuStatus();

        $(".browseView.bulkActionMenu .select_all").change(function() {
            if (this.checked) {
                kt.pages.browse.selectAllItems();
            } else {
                kt.pages.browse.deSelectAllItems();
            }
        });

        if ($('ul.paginate li.item').length == 3) {
            $('ul.paginate').hide();
        }

    });
})(jQuery);