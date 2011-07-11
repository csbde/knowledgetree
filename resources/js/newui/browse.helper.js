// Additional kt.lib functions

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

// kt.pages.browse class

kt.pages.browse = new function() {

    var self = this;

    self.curPage = 1;
    self.loading = false;
    // NOTE if you change the limit here, you should also change it on the server side
    //      (although the code should function fine if you don't, as this value operates as an override...)
    self.limit = 3;
    self.retryIn = 3000; // milliseconds

    this.addDocumentItem = function(item)
    {
        item.is_shortcut = item.is_shortcut ? '' : ' not_supported';
        item.is_immutable = item.is_immutable ? '' : ' not_supported';
        item.is_checkedout = item.is_checkedout ? '' : ' not_supported';
        item.document_link = item.document_url;

        var newItem = jQuery(jQuery('.fragment.document')[0]).html();
        newItem = kt.lib.parseTemplate(newItem, item);
        var elem = jQuery(newItem);
        var mime = jQuery('.doc.icon', elem).attr('style', item.mimeicon);
        jQuery('.page.page_' + self.curPage).append(elem);
    };

    this.viewPage = function(pageNum, folderId, fetch)
    {
        if (self.loading) { return self.retry(pageNum, folderId, fetch); }

        // TODO consider rather just returning if pageNum < 1?
        if (pageNum < 1) { pageNum = 1; }
        var pageItem = jQuery('.paginate>li.page_' + pageNum);
        if (pageItem.length <= 0) { return; }

        // if the selected page was already loaded, display immediately
        var loaded = false;
        if (jQuery('.page.page_' + pageNum).length > 0) {
            self.showPage(pageNum, pageItem);
            loaded = true;
        }

        // check for additional content within the requested range, not yet loaded
        fetch = (typeof fetch == 'undefined') ? true : fetch;
        if (fetch && self.checkRange(pageNum)) {
            self.loading = true;
            if (!loaded) {
                jQuery.loading.css.background = '#FFFEA1';
                jQuery.loading(true, { text: 'Loading...', effect: 'update' });
            }

            self.timeout = self.limit * 6000;
            if (self.timeout < 30000) {
                self.timeout = 30000;
            }

            jQuery.ajax({
                url: '/browse.php?action=paging&fFolderId=' + folderId + '&page=' + pageNum
                   + '&options={"offset":' + self.offset + ',"limit":'+ self.pages + '}',
                timeout: self.timeout,
                success: function(data) { self.loaded(data, pageNum, pageItem, loaded); },
                error: self.loadingFailed
            });
        }
    };

    this.retry = function(pageNum, folderId, fetch)
    {
        jQuery.loading(false);
        jQuery.loading(true, { text: 'Busy, please wait...trying again in ' + (self.retryIn / 1000) + ' seconds', max: self.retryIn });
        setTimeout(function() { self.viewPage(pageNum, folderId, fetch); }, self.retryIn);
        return;
    }

    this.checkRange = function(requested)
    {
        requested = Number(requested);

        var mid = null;
        var half = Math.floor(self.limit / 2);
        var remainder = self.limit % 2;
        if (remainder != 0) {
            mid = half + 1;
            var first = requested - half;
        }
        else {
            mid = half;
            var first = requested - half - 1;
        }

        index = (first > 0) ? first : 1;
        limit = index + self.limit;
        var pages = new Array;
        for (var i = index; i < limit; ++i) {
            var pageItem = jQuery('.paginate>li.page_' + i);
            if (pageItem.length <= 0) { continue; }
            if (jQuery('.page.page_' + i).length <= 0) { pages[pages.length] = i; }
        }

        self.offset = (pages.length > 0) ? pages[0] : 0;
        self.pages = pages.length;

        return pages.length > 0;
    }

    this.loaded = function(data, pageNum, pageItem, loaded)
    {
        try {
            var responseJSON = jQuery.parseJSON(data);
        }
        catch(e) {
            self.loading = false;
            return;
        }

        var pages = 0;
        jQuery.each(responseJSON, function() { ++pages; });
        if (pages > 0) {
            for (var pageId in responseJSON) {
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
            self.showPage(pageNum, pageItem);
            jQuery.loading(false);
        }

        self.loading = false;
    }

    this.loadingFailed = function(request, errorType, thrown)
    {
        jQuery.loading(false);
        self.loading = false;
    }

    this.showPage = function(pageNum, pageItem)
    {
        jQuery('.page').hide(0, function() { jQuery('.page.page_' + pageNum).show(0); })
        jQuery('.paginate>li.item').removeClass('highlight');
        pageItem.addClass('highlight');
        self.curPage = new Number(pageNum);
        jQuery('html, body').animate({ scrollTop: 0 }, 0);

        var selectedItems = jQuery('.page.page_' + self.curPage + ' .item .checkbox>input:checkbox:checked:enabled').length;
        jQuery('.select_all').attr('checked', (selectedItems > 0));
    }

    this.nextPage = function(folderId)
    {
        self.viewPage(self.curPage + 1, folderId);
        return false;
    };

    this.prevPage = function(folderId)
    {
        self.viewPage(self.curPage - 1, folderId);
        return false;
    };

    this.selectAllItems = function()
    {
        jQuery('.page.page_' + self.curPage + ' .item .checkbox > input:checkbox:enabled').each(function() {
            if (!this.checked) { jQuery(this).click(); }
            jQuery(this).parents('.item').addClass('highlighted');
        });
        self.setBulkActionMenuStatus();
        return false;
    }

    this.deSelectAllItems = function()
    {
        jQuery('.page.page_' + self.curPage + ' .item .checkbox > input:checkbox:enabled').each(function() {
            if (this.checked) { jQuery(this).click(); }
            jQuery(this).parents('.item').removeClass('highlighted');
        });
        self.setBulkActionMenuStatus();
        return false;
    }

    this.getSelectedItems = function()
    {
    	var list = new Array;
    	var name;
        jQuery('.page .item .checkbox > input:checkbox:enabled').each(function() {
            if (this.checked) {
                name = this.name.replace('[]', '');
                list.push(name+'_'+this.value);
            }
        });

        return list;
    }

    this.setBulkActionMenuStatus = function()
    {
        var selectedItems = jQuery('.itemContainer .item .checkbox>input:checkbox:checked:enabled').length;
        if (selectedItems > 0) {
        	jQuery('.browseView.bulkActionMenu td:first-child').removeClass('disabled');
        	jQuery('.browseView.bulkActionMenu td:first-child input[type="submit"]').attr('disabled', '');
        	jQuery('.browseView.bulkActionMenu .status').html(selectedItems + '&nbsp;Item(s)&nbsp;Selected');
        } else {
        	jQuery('.browseView.bulkActionMenu td:first-child').addClass('disabled');
        	jQuery('.browseView.bulkActionMenu td:first-child input[type="submit"]').attr('disabled', 'disabled');
        	jQuery('.browseView.bulkActionMenu .status').html('');
        }
    }

};

// Setup
kt.lib.shortcut.add('ctrl+right', kt.pages.browse.nextPage);
kt.lib.shortcut.add('ctrl+left', kt.pages.browse.prevPage);
kt.lib.shortcut.add('ctrl+a', kt.pages.browse.selectAllItems);
kt.lib.shortcut.add('ctrl+shift+a', kt.pages.browse.deSelectAllItems);

// Main
jQuery(document).ready(function() {
    kt.pages.browse.viewPage(1, null, false);

    jQuery('.actionIcon.comments').click(function() {
        var docItem=jQuery(this).parents('.item')[0];
        var thisField=jQuery('.expanderField',docItem);
        jQuery('.expanderField').not(thisField[0]).hide();
        thisField.toggle();
    });

    jQuery('table.doc.item input:checkbox').click(function() {
        kt.pages.browse.setBulkActionMenuStatus();

        if (jQuery(this).is(':checked')) {
        	jQuery(this).parent().parent().parent().parent().addClass('highlighted');
        } else {
        	jQuery(this).parent().parent().parent().parent().removeClass('highlighted');
        }
    });

    jQuery('table.doc.item input:checkbox:checked').parent().parent().parent().parent().addClass('highlighted');

    jQuery('table.folder.item input:checkbox').click(function() {
        kt.pages.browse.setBulkActionMenuStatus();

        if (jQuery(this).is(':checked')) {
        	jQuery(this).parent().parent().parent().parent().addClass('highlighted');
        } else {
        	jQuery(this).parent().parent().parent().parent().removeClass('highlighted');
        }
    });

    jQuery('table.folder.item input:checkbox:checked').parent().parent().parent().parent().addClass('highlighted');

    if (jQuery.browser.msie) {
    (function() {
        function hide() {
        	jQuery('.doc.browseView:first-child .item .actionMenu .actions>ul').css({display: '', visibility: ''});
        }

        jQuery('.doc.browseView:first-child .item .actionMenu .actions>ul').css({display: 'block', visibility: 'hidden'});
        setTimeout(hide,200);
    })();
    }

	/**
	 * Functionality to place the menu in an always visible state
	 */
	jQuery('.doc.browseView .item .actionMenu .actions').live("hover", function() {
		// Reset Position Everytime relative to the parent item
		jQuery(this).children("ul:first").css({'top': 15+'px', 'position':'absolute', 'margin-top':0});

		// Chrome/Safari uses body, IE/Firefox uses HTML for scrolling offset
		if (jQuery("body").scrollTop() > jQuery("html").scrollTop()) {
			scrollElement = "body";
		} else {
			scrollElement = "html";
		}

		// If (parent position+child height) > (window height + scroll offset), Reposition child
		// 35 is height of footer
		
		if (jQuery(this).offset().top+jQuery(this).children("ul:first").height()+5+35 > jQuery(window).height()+jQuery(scrollElement).scrollTop()) {
			diff = (jQuery(this).offset().top+jQuery(this).children("ul:first").height()) - (jQuery(window).height() + jQuery(scrollElement).scrollTop());

			// Move item up by difference + 20px
			jQuery(this).children("ul:first").css('margin-top', '-'+(diff+18+35)+'px');
		}
	});

    kt.pages.browse.setBulkActionMenuStatus();

    jQuery('.browseView.bulkActionMenu .select_all').change(function() {
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
