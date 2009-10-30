function swapInItem(docId, elementId, req) {

    var cp = getElement(elementId);

    cp.innerHTML = req.responseText;

    //Rendering the AJAX MCE Editors
    //Sample  { "htmlId" : {'metadata_7' : 'metadata_7','metadata_9' : 'metadata_9'}}
    jQuery.getJSON('presentation/lookAndFeel/knowledgeTree/documentmanagement/getHtmlFields.php?fDocumentTypeID=' + docId, 
	    function(json){
        jQuery.each(json.htmlId, function(id) {
        	//Envoking the MCE editor for each html id 
            jQuery('#' + id).tinymce(kt_TinyMCEOptions);
	    });
    });

    //Rendering all the AJAX loaded Date Fields
    
    //Need to compare against fields from generic fieldsets to 
    //prevent duplicate date field instanciation.
    var genericFields = '';
    //Sample  { "genericId" : {'metadata_7' : 'metadata_7','metadata_9' : 'metadata_9'}}
    jQuery.getJSON('presentation/lookAndFeel/knowledgeTree/documentmanagement/getHtmlFields.php?fDocumentTypeID=' + docId + '&type=generic',
	    function(json){
        jQuery.each(json.genericId, function(id) {
        	//Building a list of generic fields  
            genericFields += id + ',';
	    });
        
        var elems = jQuery(document).find(".kt_date_field");
    	for (i = 0; i < elems.length; i++) {
    		var fieldName = elems[i].id;
    		
    		isGeneric = false;
    		if (genericFields.indexOf(fieldName.match('metadata_[0-9]+')) >= 0){
    			isGeneric = true;
    		}
    		
    		if (!isGeneric){
    	        var dp = new Ext.form.DateField({
    		        name: fieldName.replace('div_', ''),
    		        allowBlank:false,
    		        size:10,
    		        format: 'Y-m-d',
    		        invalidText : "{0} is not a valid date - it must be in the format YYYY-MM-DD",
    		        fieldClass: 'metadatadate'
    	        });
    	
    	        dp.render(fieldName);
    		}

    	}        
        
    });
	
    
    initialiseConditionalFieldsets();
}

function xmlFailure(err) {
    alert('failed');
}

function swapElementFromRequest(elementId, url, docId) {
    var deff = doSimpleXMLHttpRequest(url);
    var cp = getElement(elementId);
    cp.innerHTML=_("loading...");
    deff.addCallback(partial(swapInItem,  docId, elementId));
}

function getMetadataForType(id) {
    swapElementFromRequest('type_metadata_fields','presentation/lookAndFeel/knowledgeTree/documentmanagement/getTypeMetadataFields.php?fDocumentTypeID=' + id, id);
}

function document_type_changed() {
    typeselect = getElement('add-document-type');
    getMetadataForType(typeselect.value);
}

function startupMetadata() {
    typeselect = getElement('add-document-type');
    addToCallStack(typeselect, "onchange", document_type_changed, false);
    document_type_changed();
}

addLoadEvent(startupMetadata);
