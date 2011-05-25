if (typeof(kt.app) == 'undefined') { kt.app = {}; }
if (typeof(kt.api) == 'undefined') { kt.api = {}; }

kt.app.document_viewlets = new function() { 
	// contains a list of fragments that will get preloaded
    var fragments = this.fragments = [];
    var fragmentPackage = this.fragmentPackage = []

    // contains a list of executable fragments that will get preloaded
    var execs = this.execs = [];
    var execPackage = this.execPackage = [execs];

    // scope protector. inside this object referrals to self happen via 'self' rather than 'this'
    // to make sure we call the functionality within the right scope.
    var self = this;

    var elems = this.elems = {};

    var documentId;
    // Initializes the upload widget on creation. Currently does preloading of resources.
    this.init = function() {
        kt.api.preload(fragmentPackage, execPackage, true);
    }
    
	this.refresh_comments = function(documentId) {
		self.documentId = documentId;
		var params = {};
		params.documentId = documentId;
		var synchronous = false;
		var func = 'documentViewletServices.comments';
	    var response = ktjapi.retrieve(func, params);
	    jQuery('#activityfeed_comments').html(response.data.success);
	    
	    return null;
	}
	
	this.update_filename_version = function(documentId) {
		self.documentId = documentId;
		var params = {};
		params.documentId = documentId;
		var synchronous = false;
		var func = 'documentViewletServices.versionAndFileName';
	    var response = ktjapi.retrieve(func, params);
		
		if (jQuery('#value-filename')) {
			jQuery('#value-filename').html(response.data.filename);
		}
		
		if (jQuery('#value_versionhistory')) {
			jQuery('#value_versionhistory').html(response.data.version);
		}
	    
	    return null;
	}
	
	this.error  = function() {
		console.log('error');
	}
	
    this.init();
}