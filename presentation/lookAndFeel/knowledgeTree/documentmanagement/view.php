<?php

// boilerplate.
require_once("../../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");

// document related includes
require_once(KT_LIB_DIR . "/documentmanagement/Document.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentType.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentFieldLink.inc");
require_once(KT_LIB_DIR . "/metadata/fieldset.inc.php");
require_once(KT_LIB_DIR . "/security/Permission.inc");

// widget includes.
require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/actions/documentaction.inc.php");


class ViewDocumentDispatcher extends KTStandardDispatcher {

    var $sSection = "view_details";
    // Breadcrumbs base - added to in methods
    var $aBreadcrumbs = array(
        array('action' => 'browse', 'name' => 'Browse'),
    );
    
    // FIXME identify the current location somehow.
    function addPortlets($currentaction = null) {
	    $actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser);
		$oPortlet = new KTActionPortlet("Document Actions"); // FIXME i18n
		$oPortlet->setActions($actions, $currentaction);
		$this->oPage->addPortlet($oPortlet);
	}
    
    function do_main() {
	    // fix legacy, broken items.   
	    if (KTUtil::arrayGet($_REQUEST, "fDocumentID", true) !== true) {
            $_REQUEST["fDocumentId"] = KTUtil::arrayGet($_REQUEST, "fDocumentID");
			unset($_REQUEST["fDocumentID"]);
		}
	    
		$document_data = array();
		$document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
		if ($document_id === null) { 
		    $this->oPage->addError('No document was requested.  Please <a href="/presentation/lookAndFeel/knowledgeTree/browse.php">browse</a> for one.');
			return $this->do_error();
		}
		$document_data["document_id"] = $document_id;
		
		// try get the document.
		$oDocument =& Document::get($document_id);
		if (PEAR::isError($oDocument)) {
		    $this->oPage->addError('The document you attempted to retrieve is invalid.   Please <a href="/presentation/lookAndFeel/knowledgeTree/browse.php">browse</a> for one.');
			return $this->do_error();		
		}
		if (!Permission::userHasDocumentReadPermission($oDocument)) {
		    // FIXME inconsistent.
		    $this->oPage->addError('You are not allowed to view this document');
		    return $this->do_error();
		}
		
		$this->oDocument =& $oDocument;
        $this->aBreadcrumbs += KTBrowseUtil::breadcrumbsForDocument($oDocument);
		$this->oPage->setBreadcrumbDetails("document details");
		$this->addPortlets("Document Details");
		
		$document_data["document"] = $oDocument;
		$document_data["document_type"] =& DocumentType::get($oDocument->getDocumentTypeID());
		$is_valid_doctype = true;
		
		if (PEAR::isError($document_data["document_type"])) {
			$this->oPage->addError('The document you requested has an invalid <strong>document type</strong>.  Unfortunately, this means that we cannot effectively display it.');
			$is_valid_doctype = false;
		}
		
		// we want to grab all the md for this doc, since its faster that way.
		$mdlist =& DocumentFieldLink::getList(array('document_id = ?', array($document_id)));

		$field_values = array();
		foreach ($mdlist as $oFieldLink) {
            $field_values[$oFieldLink->getDocumentFieldID()] = $oFieldLink->getValue();
		}
		
		//var_dump($field_values);
		
		$document_data["field_values"] = $field_values;
		
		
		// FIXME generate portlets
		// FIXME generate breadcrumb
		
		// Fieldset generation.
		// 
		//   we need to create a set of FieldsetDisplay objects
		//   that adapt the Fieldsets associated with this lot
		//   to the view (i.e. ZX3).   Unfortunately, we don't have
		//   any of the plumbing to do it, so we handle this here.
		$fieldsets = array();
		
		// we always have a generic.
		array_push($fieldsets, new GenericFieldsetDisplay());
		
        $fieldsetDisplayReg =& KTFieldsetDisplayRegistry::getSingleton();

        foreach (KTMetadataUtil::fieldsetsForDocument($oDocument) as $oFieldset) {
            $displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
            array_push($fieldsets, new $displayClass($oFieldset));
        }
		
        $oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("kt3/view_document");
		$aTemplateData = array(
              "context" => $this,
			  "document_id" => $document_id,
			  "document" => $oDocument,
			  "document_data" => $document_data,
			  "fieldsets" => $fieldsets,
		);
		//return '<pre>' . print_r($aTemplateData, true) . '</pre>';
		return $oTemplate->render($aTemplateData);
    }
	
    function do_history() {	 
		$document_data = array();
		$document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
		if ($document_id === null) { 
		    $this->oPage->addError('No document was requested.  Please <a href="/presentation/lookAndFeel/knowledgeTree/browse.php">browse</a> for one.');
			return $this->do_error();
		}
		$document_data["document_id"] = $document_id;
		
		// try get the document.
		$oDocument =& Document::get($document_id);
		if (PEAR::isError($oDocument)) {
		    $this->oPage->addError('The document you attempted to retrieve is invalid.   Please <a href="/presentation/lookAndFeel/knowledgeTree/browse.php">browse</a> for one.');
			return $this->do_error();		
		}
		// fixme check perms
		
		$this->oDocument =& $oDocument;
        $this->aBreadcrumbs += KTBrowseUtil::breadcrumbsForDocument($oDocument);
		$this->oPage->setBreadcrumbDetails("history");
		$this->addPortlets("History");
		
		$aTransactions = array();
		// FIXME create a sane "view user information" page somewhere.
		// FIXME do we really need to use a raw db-access here?  probably...
		$sQuery = "SELECT DTT.name AS transaction_name, U.name AS user_name, DT.version AS version, DT.comment AS comment, DT.datetime AS datetime " .
			"FROM " . KTUtil::getTableName("document_transactions") . " AS DT INNER JOIN " . KTUtil::getTableName("users") . " AS U ON DT.user_id = U.id " .
			"INNER JOIN " . KTUtil::getTableName("transaction_types") . " AS DTT ON DTT.id = DT.transaction_id " . 
			"WHERE DT.document_id = ? ORDER BY DT.datetime DESC";
        $aParams = array($document_id);
		$res = DBUtil::getResultArray(array($sQuery, $aParams));
		if (PEAR::isError($res)) {
		   var_dump($res); // FIXME be graceful on failure.
		   exit(0);
		}
		
		// FIXME roll up view transactions
		$aTransactions = $res; 
		
		
		// render pass.
		$this->oPage->title = "Document History : " . $oDocument->getName();
        $oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("kt3/view_document_history");
		$aTemplateData = array(
              "context" => $this,
			  "document_id" => $document_id,
			  "document" => $oDocument,
			  "transactions" => $aTransactions,
		);
		return $oTemplate->render($aTemplateData);		
	}
	
	
	// FIXME refactor out the document-info creation into a single utility function.
	// this gets in:
	//   fDocumentId (document to compare against)
	//   fComparisonVersion (the metadata_version of the appropriate document)
	function do_viewComparison() {	    
		$document_data = array();
		$document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
		if ($document_id === null) { 
		    $this->oPage->addError('No document was requested.  Please <a href="/presentation/lookAndFeel/knowledgeTree/browse.php">browse</a> for one.');
			return $this->do_error();
		}
		$document_data["document_id"] = $document_id;
		
		// try get the document.
		$oDocument =& Document::get($document_id);
		if (PEAR::isError($oDocument)) {
		    $this->oPage->addError('The document you attempted to retrieve is invalid.   Please <a href="/presentation/lookAndFeel/knowledgeTree/browse.php">browse</a> for one.');
			return $this->do_error();		
		}
		if (!Permission::userHasDocumentReadPermission($oDocument)) {
		    // FIXME inconsistent.
		    $this->oPage->addError('You are not allowed to view this document');
		    return $this->do_error();
		}
		$this->oDocument =& $oDocument;
        $this->aBreadcrumbs += KTBrowseUtil::breadcrumbsForDocument($oDocument);
		$this->oPage->setBreadcrumbDetails("compare versions");
		
		$comparison_version = KTUtil::arrayGet($_REQUEST, 'fComparisonVersion');
		if ($comparison_version=== null) { 
		    $this->oPage->addError('No comparison version was requested.  Please <a href="?action=history&fDocumentId='.$document_id.'">select a version</a>.');
			return $this->do_error();
		}
		
		// FIXME when transaction history accurately stores metadata_version, this is no longer required.
		// FIXME detect that the metadata_version was "manufactured"
		// <testing>
		$oComparison =& $oDocument;
		$comparison_data =& $document_data; // no copy.
		
		// </testing>
		
		
		$document_data["document"] = $oDocument;
		$document_data["document_type"] =& DocumentType::get($oDocument->getDocumentTypeID());
		$is_valid_doctype = true;
		
		if (PEAR::isError($document_data["document_type"])) {
			$this->oPage->addError('The document you requested has an invalid <strong>document type</strong>.  Unfortunately, this means that we cannot effectively display it.');
			$is_valid_doctype = false;
		}
		
		// we want to grab all the md for this doc, since its faster that way.
		$mdlist =& DocumentFieldLink::getList(array('document_id = ?', array($document_id)));

		$field_values = array();
		foreach ($mdlist as $oFieldLink) {
            $field_values[$oFieldLink->getDocumentFieldID()] = $oFieldLink->getValue();
		}
		
		$document_data["field_values"] = $field_values;
		
		
		// FIXME generate portlets
		// FIXME generate breadcrumb
		
		// Fieldset generation.
		// 
		//   we need to create a set of FieldsetDisplay objects
		//   that adapt the Fieldsets associated with this lot
		//   to the view (i.e. ZX3).   Unfortunately, we don't have
		//   any of the plumbing to do it, so we handle this here.
		$fieldsets = array();
		
		// we always have a generic.
		array_push($fieldsets, new GenericFieldsetDisplay());
		
		// FIXME can we key this on fieldset namespace?  or can we have duplicates?
		// now we get the other fieldsets, IF there is a valid doctype.
		if ($is_valid_doctype) {
		    // these are the _actual_ fieldsets.
			$fieldsetDisplayReg =& KTFieldsetDisplayRegistry::getSingleton();
			
			// and the generics			
			$activesets = KTFieldset::getGenericFieldsets();
			foreach ($activesets as $oFieldset) {
			    $displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
				array_push($fieldsets, new $displayClass($oFieldset));
			}			
			
		    $activesets = KTFieldset::getForDocumentType($oDocument->getDocumentTypeID()); 
		    foreach ($activesets as $oFieldset) {
			    $displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
				array_push($fieldsets, new $displayClass($oFieldset));
			}
		}
		
		// <testing>
		$document_data["is_manufactured"] = 1;
		// </testing>
		
		// FIXME handle ad-hoc fieldsets.
		
        $oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("kt3/compare_document");
		$aTemplateData = array(
              "context" => $this,
			  "document_id" => $document_id,
			  "document" => $oDocument,
			  "document_data" => $document_data,
			  "comparison_data" => $comparison_data,
			  "comparison_document" => $oComparison,
			  "fieldsets" => $fieldsets,
		);
		//var_dump($aTemplateData["comparison_data"]);
		return $oTemplate->render($aTemplateData);
    }
	
	/* we have a lot of error handling.  this one is the absolute final failure. */
	function do_error() { 
	    return ''; // allow normal rendering of errors.
		// FIXME show something useful / generic.
	}
}

$oDispatcher = new ViewDocumentDispatcher;
$oDispatcher->dispatch();

?>
