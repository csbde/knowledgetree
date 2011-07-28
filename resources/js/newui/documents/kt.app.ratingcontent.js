/* Initializing kt.app if it wasn't initialized before */
if (typeof(kt.app) == 'undefined') { kt.app = {}; }

/* Initializing kt.api if it wasn't initialized before */
if (typeof(kt.api) == 'undefined') { kt.api = {}; }

/**
 * Dialogs to display notifications around new features
 */
kt.app.ratingcontent = new function() {
    var self = this;

    this.init = function() {
    	
    }

    this.likeDocument = function(documentId, fromBrowseView) {
		self._doLikeDocument('likeDocument', documentId, fromBrowseView);
	}
	
	this.unlikeDocument = function(documentId, fromBrowseView) {
		self._doLikeDocument('unlikeDocument', documentId, fromBrowseView);
	}
	
	this._doLikeDocument = function(action, documentId, fromBrowseView) {
		
		var params = {documentId:documentId};
        
		if (fromBrowseView == undefined) {
			fromBrowseView = true;
		}
		
		if (fromBrowseView) {
			var callback = this.updateBrowseView;
		} else {
			var callback = this.updateDocumentView;
		}
		
		var synchronous = false;
        var func = 'RatingContent.'+action;
        
        var synchronous = false;
        var errorCallback = function() {};
        ktjapi.callMethod(func, params, callback);
		return null;
	}
	
	this.updateBrowseView = function(response)
	{
		console.dir(response);
	}
	
	this.updateDocumentView = function(response)
	{
		console.dir(response.data.results);
	}
	
	this.testCollection = function() {
		
		var params = {};
		var callback = this.updateDocumentView;
		
		var synchronous = false;
        var func = 'RatingContent.testCollection';
        
        var synchronous = false;
        var errorCallback = function() {};
        ktjapi.callMethod(func, params, callback);
		return null;
	}


    // Call the initialization function at object instantiation.
    this.init();
}
