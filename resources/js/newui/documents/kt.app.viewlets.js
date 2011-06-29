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
	
	this.update_instantview = function(documentId) {
		self.documentId = documentId;
		var params = {};
		params.documentId = documentId;
		
		var synchronous = false;
		var func = 'documentViewletServices.getInstaView';
	    var response = ktjapi.retrieve(func, params);
		
		if (jQuery('#documentpreview') && response.data.previewCode != undefined) {
			
			if (response.data.previewCode == '') {
				jQuery('#documentpreview').html('<div class="no-preview"><span class="no-preview-text">Preview Not Available</span></div>');
			} else {
				jQuery('#documentpreview').html('<div style="margin-top:1em;">'+response.data.previewCode+'</div>');
			}
		}
		
		return null;
	}
	
	this.update_filename_version = function(documentId) {
		self.documentId = documentId;
		var params = {};
		params.documentId = documentId;
		var synchronous = false;
		var func = 'documentViewletServices.versionAndFileName';
	    var response = ktjapi.retrieve(func, params);
		
		if (jQuery('#value-filename') && response.data.filename) {
			jQuery('#value-filename').html(response.data.filename);
		}
		
		if (jQuery('#value_versionhistory') && response.data.version) {
			jQuery('#value_versionhistory').html(response.data.version);
		}
		
		if (jQuery('#value-lastmodifiedby') && response.data.lastupdatedstring) {
			jQuery('#value-lastmodifiedby').html(response.data.lastupdatedstring);
		}
		
		if (jQuery('#value-filetype') && response.data.filetype) {
			jQuery('#value-filetype').html(response.data.filetype);
		}
		
		if (jQuery('#value-checkedoutby') && response.data.checkoutuser) {
			jQuery('#value-checkedoutby').html(response.data.checkoutuser);
		}
		
		if (jQuery('#value-documentowner') && response.data.docowner) {
			jQuery('#value-documentowner').html(response.data.docowner);
		}
		
		
		
		if (jQuery('span#docItem_'+self.documentId+' span.docupdatedinfo span.date') && response.data.lastupdateddate){
			jQuery('span#docItem_'+self.documentId+' span.docupdatedinfo span.date').html(response.data.lastupdateddate);
		}
		
		if (jQuery('span#docItem_'+self.documentId+' span.docupdatedinfo span.user') && response.data.lastupdatedby){
			jQuery('span#docItem_'+self.documentId+' span.docupdatedinfo span.user').html(response.data.lastupdatedby);
		}
		
		if (jQuery('span#docItem_'+self.documentId+' span.docupdatedinfo span.filesize') && response.data.filesize){
			jQuery('span#docItem_'+self.documentId+' span.filesize').html(response.data.filesize);
		}
		
		if (jQuery('span#docItem_'+self.documentId+' span.docupdatedinfo span.docowner') && response.data.docowner){
			jQuery('span#docItem_'+self.documentId+' span.docowner').html(response.data.docowner);
		}
		
		
		
		
		// Need a better animation to highlight background color
		if (jQuery('span#docItem_'+self.documentId+' table.doc.item')){
			jQuery('span#docItem_'+self.documentId+' table.doc.item').css({'opacity': 0.2}).fadeTo("slow", 1);
			
		}
	    
	    return null;
	}
	
	this.error  = function() {
		console.log('error');
	}
	
    this.init();
}