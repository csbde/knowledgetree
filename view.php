<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 */

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
    var $sName = 'ktcore.actions.document.displaydetails';
    var $sSection = "view_details";
    var $sHelpPage = 'ktcore/browse.html';

    var $actions;

    function ViewDocumentDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _kt('Browse')),
        );

        parent::KTStandardDispatcher();
    }

    function check() {
        if (!parent::check()) { return false; }
        
        $this->persistParams(array('fDocumentId'));
        
        return true;
    }

    // FIXME identify the current location somehow.
    function addPortlets($currentaction = null) {
        $currentaction = $this->sName;
    
    	$actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser, 'documentinfo');
        $oPortlet = new KTActionPortlet(sprintf(_kt('Document info')));
        $oPortlet->setActions($actions, $currentaction);
        $this->oPage->addPortlet($oPortlet);    
    
        $this->actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser);
        $oPortlet = new KTActionPortlet(sprintf(_kt('Document actions'), $this->oDocument->getName()));
        $oPortlet->setActions($this->actions, $currentaction);
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
            $this->oPage->addError(sprintf(_kt("No document was requested.  Please <a href=\"%s\">browse</a> for one."), KTBrowseUtil::getBrowseBaseUrl()));
            return $this->do_error();
        }
        // try get the document.
        $oDocument =& Document::get($document_id);
        if (PEAR::isError($oDocument)) {
            $this->oPage->addError(sprintf(_kt("The document you attempted to retrieve is invalid.   Please <a href=\"%s\">browse</a> for one."), KTBrowseUtil::getBrowseBaseUrl()));
            return $this->do_error();
        }
        $document_id = $oDocument->getId();
        $document_data["document_id"] = $oDocument->getId();


        if (!KTBrowseUtil::inAdminMode($this->oUser, $oDocument->getFolderId())) {
            if ($oDocument->getStatusID() == ARCHIVED) {
                $this->oPage->addError(_kt('This document has been archived.  Please contact the system administrator to have it restored if it is still needed.'));
                return $this->do_error();
            } else if ($oDocument->getStatusID() == DELETED) {
                $this->oPage->addError(_kt('This document has been deleted.  Please contact the system administrator to have it restored if it is still needed.'));
                return $this->do_error();
            } else if (!Permission::userHasDocumentReadPermission($oDocument)) {
                $this->oPage->addError(_kt('You are not allowed to view this document'));
                return $this->permissionDenied();
            }
        }

        if ($oDocument->getStatusID() == ARCHIVED) {
            $this->oPage->addError(_kt('This document has been archived.'));
        } else if ($oDocument->getStatusID() == DELETED) {
            $this->oPage->addError(_kt('This document has been deleted.'));
        }

        $this->oPage->setSecondaryTitle($oDocument->getName());

        $aOptions = array(
            "documentaction" => "viewDocument",
            "folderaction" => "browse",
        );

        $this->oDocument =& $oDocument;
        $this->aBreadcrumbs = array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForDocument($oDocument, $aOptions));
        $this->oPage->setBreadcrumbDetails(_kt("document details"));
        $this->addPortlets("Document Details");

        $document_data["document"] = $oDocument;
        $document_data["document_type"] =& DocumentType::get($oDocument->getDocumentTypeID());
        $is_valid_doctype = true;

        if (PEAR::isError($document_data["document_type"])) {
            $this->oPage->addError(_kt('The document you requested has an invalid <strong>document type</strong>.  Unfortunately, this means that we cannot effectively display it.'));
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

        // is the checkout action active?
        $bCanCheckin = false;
        foreach ($this->actions as $oDocAction) {
            $sActName = $oDocAction->sName;
            if ($sActName == 'ktcore.actions.document.cancelcheckout') {
                if ($oDocAction->getInfo()) {
                    $bCanCheckin = true;
                }
            }
        }

        // viewlets.
        $aViewlets = array();
        $aViewletActions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser, 'documentviewlet');
        foreach ($aViewletActions as $oAction) {
            $aInfo = $oAction->getInfo();
            
            if ($aInfo !== null) {
                $aViewlets[] = $oAction->display_viewlet(); // use the action, since we display_viewlet() later.            
            }
        } 
        
        $viewlet_data = implode(" ", $aViewlets);
        $viewlet_data = trim($viewlet_data);
        
        $content_class = 'view';
        if (!empty($viewlet_data)) {
            $content_class = 'view withviewlets';
        } 
        $this->oPage->setContentClass($content_class);
        

        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/document/view");
        $aTemplateData = array(
              "context" => $this,
              "sCheckoutUser" => $checkout_user,
              "isCheckoutUser" => ($this->oUser->getId() == $oDocument->getCheckedOutUserId()),
              "canCheckin" => $bCanCheckin,
              "document_id" => $document_id,
              "document" => $oDocument,
              "document_data" => $document_data,
              "fieldsets" => $fieldsets,
              'viewlet_data' => $viewlet_data,
        );
        //return '<pre>' . print_r($aTemplateData, true) . '</pre>';
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
            $this->oPage->addError(sprintf(_kt("No document was requested.  Please <a href=\"%s\">browse</a> for one."), KTBrowseUtil::getBrowseBaseUrl()));
            return $this->do_error();
        }
    
        $document_data["document_id"] = $document_id;
    
        $base_version = KTUtil::arrayGet($_REQUEST, 'fBaseVersion');
    
        // try get the document.
        $oDocument =& Document::get($document_id, $base_version);
        if (PEAR::isError($oDocument)) {
            $this->oPage->addError(sprintf(_kt("The base document you attempted to retrieve is invalid.   Please <a href=\"%s\">browse</a> for one."), KTBrowseUtil::getBrowseBaseUrl()));
            return $this->do_error();
        }
    
        if (!Permission::userHasDocumentReadPermission($oDocument)) {
            // FIXME inconsistent.
            $this->oPage->addError(_kt('You are not allowed to view this document'));
            return $this->permissionDenied();
        }
    
        $this->oDocument =& $oDocument;
        $this->oPage->setSecondaryTitle($oDocument->getName());
        $aOptions = array(
                "documentaction" => "viewDocument",
                "folderaction" => "browse",
            );
    
        $this->aBreadcrumbs = array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForDocument($oDocument, $aOptions));
        $this->oPage->setBreadcrumbDetails(_kt("compare versions"));
    
        $comparison_version = KTUtil::arrayGet($_REQUEST, 'fComparisonVersion');
        if ($comparison_version=== null) {
            $this->oPage->addError(sprintf(_kt("No comparison version was requested.  Please <a href=\"%s\">select a version</a>."), KTUtil::addQueryStringSelf('action=history&fDocumentId=' . $document_id)));
            return $this->do_error();
        }
    
        $oComparison =& Document::get($oDocument->getId(), $comparison_version);
        if (PEAR::isError($oComparison)) {
            $this->errorRedirectToMain(_kt('Invalid document to compare against.'));
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
            $this->oPage->addError(_kt('The document you requested has an invalid <strong>document type</strong>.  Unfortunately, this means that we cannot effectively display it.'));
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
        $oTemplate = $this->oValidator->validateTemplate("ktcore/document/compare");
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
    
    function do_error() {
        return '&nbsp;'; // don't actually do anything.
    }


    function getUserForId($iUserId) {
        $u = User::get($iUserId);
        if (PEAR::isError($u) || ($u == false)) { return _kt('User no longer exists'); }
        return $u->getName();
    }
}

$oDispatcher = new ViewDocumentDispatcher;
$oDispatcher->dispatch();

?>
