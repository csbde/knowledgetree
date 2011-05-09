if (typeof(kt.app) == 'undefined') { kt.app = {}; }
if (typeof(kt.api) == 'undefined') { kt.api = {}; }

kt.app.document_actions = new function() { 
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

    // Initializes the upload widget on creation. Currently does preloading of resources.
    this.init = function() {
        kt.api.preload(fragmentPackage, execPackage, true);
    }
    
	this.checkoutActions = function(documentId, type) {
		var params = {};
		params.documentId = documentId;
		var synchronous = false;
		var func;
		switch (type) {
			case 'checkout':
				func = 'documentActionServices.checkout';
			break;
			case 'checkin':
				func = 'documentActionServices.checkin';
			break;
			case 'cancel':
				func = 'documentActionServices.checkout_cancel';
			break;
		}
		
		var callback = self.refresh(documentId);
	    ktjapi.callMethod(func, params, callback, synchronous, null, 200, 30000);
	    
	    return null;
	}
	
	this.error  = function() {
		console.log('error');
	}
	
	this.refresh = function(documentId) {
		var params = {};
		params.documentId = documentId;
		var synchronous = false;
		var func = 'documentActionServices.refresh_top_actions';
		response = ktjapi.retrieve(func, params, 200, 30000);
		console.log(response.data);
		//jQuery('.top_actions').html(response.data);
	    
	    return null;
	}
	
    // Call the initialization function at object instantiation.
    this.init();
}