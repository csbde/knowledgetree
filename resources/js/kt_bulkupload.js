function swapInItem(docId, elementId, req) {

    var cp = getElement(elementId);

    cp.innerHTML = req.responseText;

    //Sample  { "htmlId" : {'metadata_7' : 'metadata_7','metadata_9' : 'metadata_9'}}
    jQuery.getJSON('presentation/lookAndFeel/knowledgeTree/documentmanagement/getHtmlFields.php?fDocumentTypeID=' + docId, 
	    function(json){
        jQuery.each(json.htmlId, function(id) {
        	//Envoking the MCE editor for each html id 
            jQuery('#' + id).tinymce(kt_TinyMCEOptions);
	    });
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
