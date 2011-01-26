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

kt.pages.browse.curPage = 1;

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

kt.pages.browse.viewPage = function(pageNum, fetch) {
    if (pageNum < 1) { pageNum = 1; }
    var pageItem = jQuery('.paginate>li.page_' + pageNum);

    if (pageItem.length <= 0) { return; }

    // if the selected page was already loaded, display immediately
    var loaded = false;
    if (jQuery('.page.page_' + pageNum).length > 0) {
        console.log('Show page ' + pageNum + ' (previously loaded)');
        kt.pages.browse.showPage(pageNum, pageItem);
        loaded = true;
    }

    // then check for additional content within the requested range, not yet loaded
    fetch = (typeof fetch == 'undefined') ? true : fetch;
    if (fetch) {
        var response = jQuery.get('/browse.php?action=paging&fFolderId=6&page=' + pageNum, function(data) {
            if (data != '[]') {
                var responseJSON = jQuery.parseJSON(data);
                for (var pageId in responseJSON) {
                    console.log("Load page " + pageId);
                    if (pageNum == 1) {
                        // we prepend because otherwise it switches the location of the page navigator
                        jQuery('.itemContainer').prepend(responseJSON[pageId]);
                    }
                    else {
                        var appendTo = pageId - 1;
                        while (jQuery('.page.page_' + appendTo).length <= 0) {
                            --appendTo;
                        }
                        jQuery('.page.page_' + appendTo).after(responseJSON[pageId]);
                    }
                    jQuery('.page.page_' + pageId).hide(0);
                }
            }

            if (!loaded) {
                console.log("Show page " + pageNum);
                kt.pages.browse.showPage(pageNum, pageItem);
            }
        });
    }
};

kt.pages.browse.showPage = function(pageNum, pageItem) {
    jQuery('.page').hide(0, function() { jQuery('.page.page_' + pageNum).show(0); })
    jQuery('.paginate>li.item').removeClass('highlight');
    pageItem.addClass('highlight');
    kt.pages.browse.curPage = new Number(pageNum);
    jQuery('html, body').animate({ scrollTop: 0 }, 0);
}

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

jQuery(document).ready(function() {
    kt.pages.browse.viewPage(1, false);
    kt.pages.browse.setBulkActionMenuStatus = function() {
        var selectedItems = jQuery(".itemContainer .item .checkbox>input:checkbox:checked:enabled").length;
        if (selectedItems > 0) {
        	jQuery('.browseView.bulkActionMenu td:first-child').removeClass('disabled');
        	jQuery('.browseView.bulkActionMenu td:first-child input[type="submit"]').attr('disabled', '');
            //				jQuery('.browseView.bulkActionMenu').slideDown(350,function() {kt.lib.setFooter();});
            //				jQuery('.browseView.bulkActionMenu').slideDown(350);
        	jQuery('.browseView.bulkActionMenu .status').html(selectedItems + '&nbsp;Item(s)&nbsp;Selected');
        } else {
        	jQuery('.browseView.bulkActionMenu td:first-child').addClass('disabled');
        	jQuery('.browseView.bulkActionMenu td:first-child input[type="submit"]').attr('disabled', 'disabled');
            //				jQuery('.browseView.bulkActionMenu').hide(200);
            //				jQuery('.browseView.bulkActionMenu').hide(200,function() {kt.lib.setFooter();});
        	jQuery('.browseView.bulkActionMenu .status').html('');
        }
    }

    jQuery('.actionIcon.comments').click(function() {
        var docItem=jQuery(this).parents('.item')[0];
        var thisField=jQuery('.expanderField',docItem);
        jQuery('.expanderField').not(thisField[0]).hide();
        thisField.toggle();
    });

    jQuery("table.doc.item input:checkbox").click(function() {
        kt.pages.browse.setBulkActionMenuStatus();

        if (jQuery(this).is(':checked')) {
        	jQuery(this).parent().parent().parent().parent().addClass("highlighted");
        } else {
        	jQuery(this).parent().parent().parent().parent().removeClass("highlighted");
        }
    });

    jQuery("table.doc.item input:checkbox:checked").parent().parent().parent().parent().addClass("highlighted");

    jQuery("table.folder.item input:checkbox").click(function() {
        kt.pages.browse.setBulkActionMenuStatus();

        if (jQuery(this).is(':checked')) {
        	jQuery(this).parent().parent().parent().parent().addClass("highlighted");
        } else {
        	jQuery(this).parent().parent().parent().parent().removeClass("highlighted");
        }
    });

    jQuery("table.folder.item input:checkbox:checked").parent().parent().parent().parent().addClass("highlighted");

    if (jQuery.browser.msie) {
    (function() {
        function hide() {
        	jQuery(".doc.browseView:first-child .item .actionMenu .actions>ul").css({display: '', visibility: ''});
        }

        jQuery(".doc.browseView:first-child .item .actionMenu .actions>ul").css({display: 'block', visibility: 'hidden'});
        setTimeout(hide,200);
    })();
    }

    kt.pages.browse.setBulkActionMenuStatus();

    jQuery(".browseView.bulkActionMenu .select_all").change(function() {
        if (this.checked) {
            kt.pages.browse.selectAllItems();
        } else {
            kt.pages.browse.deSelectAllItems();
        }
    });

    if (jQuery('ul.paginate li.item').length == 3) {
    	jQuery('ul.paginate').hide();
    }
});
