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

    var documentId;
    // Initializes the upload widget on creation. Currently does preloading of resources.
    this.init = function() {
        kt.api.preload(fragmentPackage, execPackage, true);
    }
    
	this.checkoutActions = function(documentId, type) {
		this.documentId = documentId;
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
		var callback = self.refresh;
	    ktjapi.callMethod(func, params, callback, synchronous, null, 200, 30000);
	    
	    return null;
	}
	
	this.error  = function() {
		console.log('error');
	}
	
	this.refresh = function() {
		self.refresh_top_actions();
		self.refresh_bottom_actions();
		self.refresh_status_indicator();
		
	    return null;
	}
	
	this.refresh_top_actions = function() {
		var params = {};
		params.documentId = self.documentId;
		var synchronous = false;
		var func = 'documentActionServices.refresh_top_actions';
		var response = ktjapi.retrieve(func, params, 200, 30000);
		jQuery('#top_actions').html(response.data.success);
	}
	
	this.refresh_bottom_actions = function() {
		var params = {};
		params.documentId = self.documentId;
		var synchronous = false;
		var func = 'documentActionServices.refresh_bottom_actions';
		var response = ktjapi.retrieve(func, params, 200, 30000);
		jQuery('#bottom_actions').html(response.data.success);
	}
	
	this.refresh_status_indicator = function() {
		jQuery('#indicator').toggle();
	}
	
	this.reason = function() {
		console.log('reason');
	}
	
	this.esig = function() {
		console.log('esig');
	}
	
    this.init();
}