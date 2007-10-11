<?php

class Search2Portlet extends KTPortlet
{

    function Search2Portlet()
    {
        parent::KTPortlet(_kt("Search"));
        $this->bActive = true;
    }

    function render()
    {
    	$oTemplating =& KTTemplating::getSingleton();
    	$oTemplate = $oTemplating->loadTemplate("ktcore/search2/search_portlet");

    	$iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', 1);
    	$iDocumentId = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
    	if (!$iFolderId && !$iDocumentId) {
    		return null;
    	}

    	$savedSearches = SearchHelper::getSavedSearches($_SESSION['userID']);

    	$aTemplateData = array(
    		'context' => $this,
    		'folder_id' => $iFolderId,
    		'document_id' => $iDocumentId,
    		'savedSearches' =>$savedSearches
    	);

    	return $oTemplate->render($aTemplateData);
    }
}

?>