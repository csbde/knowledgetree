<?php

// boilerplate.
require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");

// document related includes
require_once(KT_LIB_DIR . "/documentmanagement/Document.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentType.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentFieldLink.inc");
require_once(KT_LIB_DIR . "/documentmanagement/documentmetadataversion.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/documentcontentversion.inc.php");
require_once(KT_LIB_DIR . "/metadata/fieldset.inc.php");
require_once(KT_LIB_DIR . "/security/Permission.inc");

// widget includes.
require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/actions/documentaction.inc.php");
require_once(KT_LIB_DIR . "/browse/browseutil.inc.php");


class ViewDocumentDispatcher extends KTStandardDispatcher {

    var $sSection = "view_details";
    var $sHelpPage = 'ktcore/browse.html';	

    function ViewDocumentDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _('Browse')),
        );

        parent::KTStandardDispatcher();
    }
    
    // FIXME identify the current location somehow.
    function addPortlets($currentaction = null) {
	    $actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser);
		$oPortlet = new KTActionPortlet(_("Document Actions"));
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
		    $this->oPage->addError('No document was requested.  Please <a href="' . KTBrowseUtil::getBrowseBaseUrl() . '">browse</a> for one.');
			return $this->do_error();
		}
		$document_data["document_id"] = $document_id;
		
		// try get the document.
		$oDocument =& Document::get($document_id);
		if (PEAR::isError($oDocument)) {
		    $this->oPage->addError('The document you attempted to retrieve is invalid.   Please <a href="' . KTBrowseUtil::getBrowseBaseUrl() . '">browse</a> for one.');
			return $this->do_error();		
		}

        if (!KTBrowseUtil::inAdminMode($this->oUser, $oDocument->getFolderId())) {
            if (!Permission::userHasDocumentReadPermission($oDocument)) {
                $this->oPage->addError(_('You are not allowed to view this document'));
                return $this->do_error();
            }
        }

		$this->oPage->setSecondaryTitle($oDocument->getName());

        $aOptions = array(
            "documentaction" => "viewDocument",
            "folderaction" => "browse",
        );
		
		$this->oDocument =& $oDocument;
        $this->aBreadcrumbs = array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForDocument($oDocument, $aOptions));
		$this->oPage->setBreadcrumbDetails(_("document details"));
		$this->addPortlets("Document Details");
		
		$document_data["document"] = $oDocument;
		$document_data["document_type"] =& DocumentType::get($oDocument->getDocumentTypeID());
		$is_valid_doctype = true;
		
		if (PEAR::isError($document_data["document_type"])) {
			$this->oPage->addError('The document you requested has an invalid <strong>document type</strong>.  Unfortunately, this means that we cannot effectively display it.');
			$is_valid_doctype = false;
		}
		
		// we want to grab all the md for this doc, since its faster that way.
		$mdlist =& DocumentFieldLink::getByDocument($oDocument);

		$field_values = array();
		foreach ($mdlist as $oFieldLink) {
            $field_values[$oFieldLink->getDocumentFieldID()] = $oFieldLink->getValue();
		}
		
		//var_dump($field_values);
		
		$document_data["field_values"] = $field_values;
		
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
		
		
		$checkout_user = 'Unknown user';
		if ($oDocument->getIsCheckedOut() == 1) {
		    $oCOU = User::get($oDocument->getCheckedOutUserId());
			if (!(PEAR::isError($oCOU) || ($oCOU == false))) {
				$checkout_user = $oCOU->getName();
			}
		}
		
		
        $oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("kt3/view_document");
		$aTemplateData = array(
              "context" => $this,
			  "sCheckoutUser" => $checkout_user,
			  "isCheckoutUser" => ($this->oUser->getId() == $oDocument->getCheckedOutUserId()),
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
		    $this->oPage->addError('No document was requested.  Please <a href="' . KTBrowseUtil::getBrowseBaseUrl() . '">browse</a> for one.');
			return $this->do_error();
		}
		$document_data["document_id"] = $document_id;
		
		// try get the document.
		$oDocument =& Document::get($document_id);
		if (PEAR::isError($oDocument)) {
		    $this->oPage->addError('The document you attempted to retrieve is invalid.   Please <a href="' . KTBrowseUtil::getBrowseBaseUrl() . '">browse</a> for one.');
			return $this->do_error();		
		}
		// fixme check perms
		
		$this->oDocument =& $oDocument;
		
		$this->oPage->setSecondaryTitle($oDocument->getName());
		
        $aOptions = array(
            "documentaction" => "viewDocument",
            "folderaction" => "browse",
        );
        $this->aBreadcrumbs = array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForDocument($oDocument, $aOptions));
		$this->oPage->setBreadcrumbDetails(_("history"));
		$this->addPortlets("History");
		
		$aTransactions = array();
		// FIXME create a sane "view user information" page somewhere.
		// FIXME do we really need to use a raw db-access here?  probably...
		$sQuery = "SELECT DTT.name AS transaction_name, U.name AS user_name, DT.version AS version, DT.comment AS comment, DT.datetime AS datetime " .
			"FROM " . KTUtil::getTableName("document_transactions") . " AS DT INNER JOIN " . KTUtil::getTableName("users") . " AS U ON DT.user_id = U.id " .
			"INNER JOIN " . KTUtil::getTableName("transaction_types") . " AS DTT ON DTT.namespace = DT.transaction_namespace " . 
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
		$this->oPage->title = _("Document History");
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
	
	
    function do_versionhistory() {	 
	    // this is the list of documents past.
		$document_data = array();
		$document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
		if ($document_id === null) { 
		    $this->oPage->addError('No document was requested.  Please <a href="' . KTBrowseUtil::getBrowseBaseUrl() . '">browse</a> for one.');
			return $this->do_error();
		}
		$document_data["document_id"] = $document_id;
		
		// try get the document.
		$oDocument =& Document::get($document_id);
		if (PEAR::isError($oDocument)) {
		    $this->oPage->addError('The document you attempted to retrieve is invalid.   Please <a href="' . KTBrowseUtil::getBrowseBaseUrl() . '">browse</a> for one.');
			return $this->do_error();		
		}
		// fixme check perms
		$this->oPage->setSecondaryTitle($oDocument->getName());
		$this->oDocument =& $oDocument;
		$aOptions = array(
            "documentaction" => "viewDocument",
            "folderaction" => "browse",
        );
        $this->aBreadcrumbs = array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForDocument($oDocument, $aOptions));
		$this->oPage->setBreadcrumbDetails(_("history"));
		$this->addPortlets("History");
		
		$aMetadataVersions = KTDocumentMetadataVersion::getByDocument($oDocument);
        $aVersions = array();
        foreach ($aMetadataVersions as $oVersion) {
            $aVersions[] = Document::get($oDocument->getId(), $oVersion->getId());
        }
		
		// render pass.
		$this->oPage->title = _("Document History");
        $oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("kt3/document/metadata_history");

        $aActions = KTDocumentActionUtil::getDocumentActionsByNames(array('ktcore.actions.document.view'));
        $oAction = $aActions[0];
        $oAction->setDocument($this->oDocument);
        
		$aTemplateData = array(
              "context" => $this,
			  "document_id" => $document_id,
			  "document" => $oDocument,
			  "versions" => $aVersions,
              'downloadaction' => $oAction,
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
		    $this->oPage->addError('No document was requested.  Please <a href="' . KTBrowseUtil::getBrowseBaseUrl() . '">browse</a> for one.');
			return $this->do_error();
		}
		$document_data["document_id"] = $document_id;
		
		
		$base_version = KTUtil::arrayGet($_REQUEST, 'fBaseVersion');
		
		// try get the document.
		$oDocument =& Document::get($document_id, $base_version);
		if (PEAR::isError($oDocument)) {
		    $this->oPage->addError('The base document you attempted to retrieve is invalid.   Please <a href="' . KTBrowseUtil::getBrowseBaseUrl() . '">browse</a> for one.');
			return $this->do_error();		
		}
		if (!Permission::userHasDocumentReadPermission($oDocument)) {
		    // FIXME inconsistent.
		    $this->oPage->addError(_('You are not allowed to view this document'));
		    return $this->do_error();
		}
		$this->oDocument =& $oDocument;
		$this->oPage->setSecondaryTitle($oDocument->getName());
		$aOptions = array(
            "documentaction" => "viewDocument",
            "folderaction" => "browse",
        );
        $this->aBreadcrumbs = array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForDocument($oDocument, $aOptions));
		$this->oPage->setBreadcrumbDetails(_("compare versions"));
		
		$comparison_version = KTUtil::arrayGet($_REQUEST, 'fComparisonVersion');
		if ($comparison_version=== null) { 
		    $this->oPage->addError('No comparison version was requested.  Please <a href="' . KTUtil::addQueryStringSelf('action=history&fDocumentId=' . $document_id) . '">select a version</a>.');
			return $this->do_error();
		}
		
		$oComparison =& Document::get($oDocument->getId(), $comparison_version);
		if (PEAR::isError($oComparison)) {
		    $this->errorRedirectToMain(_('Invalid document to compare against.'));
		}
		$comparison_data = array();
		$comparison_data['document_id'] = $oComparison->getId();

		$document_data["document"] = $oDocument;
		$comparison_data['document'] = $oComparison;
		
		$document_data["document_type"] =& DocumentType::get($oDocument->getDocumentTypeID());
		$comparison_data["document_type"] =& DocumentType::get($oComparison->getDocumentTypeID());
		
		// follow twice:  once for normal, once for comparison.
		$is_valid_doctype = true;
		
		if (PEAR::isError($document_data["document_type"])) {
			$this->oPage->addError('The document you requested has an invalid <strong>document type</strong>.  Unfortunately, this means that we cannot effectively display it.');
			$is_valid_doctype = false;
		}

		// we want to grab all the md for this doc, since its faster that way.
		$mdlist =& DocumentFieldLink::getList(array('metadata_version_id = ?', array($oDocument->getMetadataVersionId())));

		$field_values = array();
		foreach ($mdlist as $oFieldLink) {
            $field_values[$oFieldLink->getDocumentFieldID()] = $oFieldLink->getValue();
		}
		
		$document_data["field_values"] = $field_values;
		
		$mdlist =& DocumentFieldLink::getList(array('metadata_version_id = ?', array($comparison_version)));

		$field_values = array();
		foreach ($mdlist as $oFieldLink) {
            $field_values[$oFieldLink->getDocumentFieldID()] = $oFieldLink->getValue();
		}
		
		$comparison_data["field_values"] = $field_values;

		
		
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
			
		// FIXME handle ad-hoc fieldsets.
		$this->addPortlets();
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
	
	function do_startComparison() {	    
		$document_data = array();
		$document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
		if ($document_id === null) { 
		    $this->oPage->addError('No document was requested.  Please <a href="' . KTBrowseUtil::getBrowseBaseUrl() . '">browse</a> for one.');
			return $this->do_error();
		}
		$document_data["document_id"] = $document_id;

		$comparison_version = KTUtil::arrayGet($_REQUEST, 'fComparisonVersion');
		if ($comparison_version=== null) { 
		    $this->oPage->addError('No comparison version was requested.  Please <a href="' . KTUtil::addQueryStringSelf('action=history&fDocumentId='.$document_id) . '">select a version</a>.');
			return $this->do_error();
		}		
		
		// try get the document.
		$oDocument =& Document::get($document_id, $comparison_version);
		if (PEAR::isError($oDocument)) {
		    $this->oPage->addError('The document you attempted to retrieve is invalid.   Please <a href="' . KTBrowseUtil::getBrowseBaseUrl() . '">browse</a> for one.');
			return $this->do_error();		
		}
		if (!Permission::userHasDocumentReadPermission($oDocument)) {
		    // FIXME inconsistent.
		    $this->oPage->addError(_('You are not allowed to view this document'));
		    return $this->do_error();
		}
		$this->oDocument =& $oDocument;
		$this->oPage->setSecondaryTitle($oDocument->getName());
		$aOptions = array(
            "documentaction" => "viewDocument",
            "folderaction" => "browse",
        );
        $this->aBreadcrumbs = array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForDocument($oDocument, $aOptions));
		$this->oPage->setBreadcrumbDetails(_("Select Document Version to compare against"));
					
		$aMetadataVersions = KTDocumentMetadataVersion::getByDocument($oDocument);
        $aVersions = array();
        foreach ($aMetadataVersions as $oVersion) {
            $aVersions[] = Document::get($oDocument->getId(), $oVersion->getId());
        }
		
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("ktcore/document/comparison_version_select");
		$aTemplateData = array(
              "context" => $this,
			  "document_id" => $document_id,
			  "document" => $oDocument,
			  "versions" => $aVersions,
              'downloadaction' => $oAction,
		);
		return $oTemplate->render($aTemplateData);
    }
	
	function getUserForId($iUserId) {
	    $u = User::get($iUserId);
		if (PEAR::isError($u) || ($u == false)) { return _('User no longer exists'); }
		return $u->getName();
	} 
}

$oDispatcher = new ViewDocumentDispatcher;
$oDispatcher->dispatch();

?>
