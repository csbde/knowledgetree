/* Initializing kt.app if it wasn't initialized before */
if (typeof(kt.app) == 'undefined') { kt.app = {}; }

/* Initializing kt.api if it wasn't initialized before */
if (typeof(kt.api) == 'undefined') { kt.api = {}; }

/**
 * General document details related js functions
 */
kt.app.docdetails = new function() {
    var self = this;
    this.init = function() {}

	this.showPageUrl = function() {
	    var url = document.location.href;
	    url = url.replace('#', '');
	    self.showUrlWin(url, 'pageurldispwin', 'Page URL');
	}

	this.getDownloadUrl = function() {
	    // check for old url path
	    var iDocId = self.getQueryVariable('fDocumentId');
	    var params = {};
	    if(iDocId == ''){
    	    var path = document.location.pathname;
    	    path = path.replace('/', '');
    	    params.clean = path;
	    }else{
	        params.docId = iDocId;
	    }

	    var func = 'siteapi.getDownloadUrl';
	    var synchronous = true;
	    var data = ktjapi.retrieve(func, params, this.persistentDataCacheTimeout);
	    var response = data.data.downloadUrl;
        var list = jQuery.parseJSON(response);

        self.showUrlWin(list.url, 'downurldispwin', 'Download URL');
	}

    this.showUrlWin = function(url, winId, title) {
	    var html = '<div><input type="text" size="30" value="' + url + '" /></div>';

        var pageUrlWin = new Ext.Window({
            id              : winId,
            layout          : 'fit',
            width           : 300,
            resizable       : false,
            closable        : true,
            closeAction     : 'destroy',
            y               : 50,
            autoScroll      : false,
            bodyCssClass    : 'ul_win_body',
            cls             : 'ul_win',
            shadow          : true,
            modal           : true,
            title           : title,
            html            : html
        });

        pageUrlWin.show();
	}

	this.getQueryVariable = function (ji) {
		hu = window.location.search.substring(1);
		gy = hu.split("&");
		for (i=0;i<gy.length;i++) {
			ft = gy[i].split("=");
			if (ft[0] == ji) {
				return ft[1];
			}
		}
		return '';
	}

    // Call the initialization function at object instantiation.
    this.init();
}