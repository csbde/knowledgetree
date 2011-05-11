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
		console.log('refresh_comments');
		this.documentId = documentId;
		var params = {};
		params.documentId = documentId;
		var synchronous = false;
		var func = 'documentViewletServices.comments';
		var callback = self.refresh;
	    
	    return null;
	}
	
	this.error  = function() {
		console.log('error');
	}
	
	this.refresh = function() {
		console.log('refresh');
		
	    return null;
	}
	
    this.init();
}