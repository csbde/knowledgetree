<?php
/*
 * Ruthlessly gutted from addDocument.php
 *
 * @version $Revision$
 * @author Brad Shuttleworth, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/dispatcher.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentLink.inc');

require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

// widget includes.
require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/actions/documentaction.inc.php");
require_once(KT_LIB_DIR . "/browse/browseutil.inc.php");

require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');


require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

class KTEditDocumentDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;
    var $oDocument = null;
    var $oFolder = null;
    var $sSection = "view_details";
    var $sHelpPage = 'ktcore/browse.html';	

	function check() {
		if (!parent::check()) { return false; }
		
		$document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        if (empty($document_id)) {
            $this->errorPage(_("No document specified for editing."));
        }
        $oDocument = Document::get($document_id);
        if (PEAR::isError($oDocument)) {
            $this->errorPage(_("Invalid Document."));
        }
        
        $this->oDocument = $oDocument;
		$oPerm = KTPermission::getByName('ktcore.permissions.write');	
		
		if (!KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPerm, $this->oDocument)) { return false; }
		
		
		
		
		return true;
	}

    function KTEditDocumentDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _('Browse')),
        );
        return parent::KTStandardDispatcher();
    }
    
    // FIXME identify the current location somehow.
    function addPortlets($currentaction = null) {
        $actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser);
        $oPortlet = new KTActionPortlet(_("Document Actions"));
        $oPortlet->setActions($actions, $currentaction);
		
        $this->oPage->addPortlet($oPortlet);
    }
    
    function addBreadcrumbs() {
        
		$aOptions = array(
            "documentaction" => "editDocument",
            "folderaction" => "browse",
        );        
        
		$this->aBreadcrumbs = array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForDocument($this->oDocument, $aOptions));
		
		if (!is_null($this->oDocument)) { 
			$this->oPage->setSecondaryTitle($this->oDocument->getName());
		}
		
    }

    function errorPage($errorMessage) {
        $this->handleOutput($errorMessage);
        exit(0);
    }

	function do_selectType() {
		
	    $document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        if (empty($document_id)) {
            $this->errorPage(_("No document specified for editing."));
        }
        $oDocument = Document::get($document_id);
        if (PEAR::isError($oDocument)) {
            $this->errorPage(_("Invalid Document."));
        }
		
		$this->oDocument = $oDocument;
		$this->addPortlets("Edit");
		$this->addBreadcrumbs();
		$this->oPage->setBreadcrumbDetails(_('Change Document Type'));
		
		$aDocTypes = DocumentType::getList();


        $aDocTypes = array();
        foreach (DocumentType::getList() as $oDocumentType) {
            if(!$oDocumentType->getDisabled()) {
                $aDocTypes[] = $oDocumentType;
            }
        }

	    $oDocumentType = DocumentType::get($oDocument->getDocumentTypeID());
		
	    $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate("ktcore/document/change_type");       
        $aTemplateData = array(
            'context' => $this,
            'document' => $oDocument,
			'document_type' => $oDocumentType,
			'doctypes' => $aDocTypes,
        );
        $oTemplate->setData($aTemplateData);
        return $oTemplate->render();
	}

	function do_changeType() {
		// FIXME this could do with more postTriggers, etc.
		
		/* The basic procedure is:
		 *
		 *   1. find out what fieldsets we _have_
		 *   2. find out what fieldsets we _should_ have.
		 *   3. actively delete fieldsets we need to lose.
		 *   4. run the edit script.
		 */
		$newType = KTUtil::arrayGet($_REQUEST, 'fDocType');
		$oType = DocumentType::get($newType);
		if (PEAR::isError($oType) || ($oType == false)) {
		    $this->errorRedirectToMain(_("Invalid type selected."));
		}
		
		$_SESSION['KTInfoMessage'][] = _('Document Type Changed.  Please review the information below, and update as appropriate.');
		
		$_REQUEST['setType'] = $newType;
		
		return $this->do_main($newType);
	}

	// "standard document editing"
    function do_main($newType=false) {
	    $this->oPage->setBreadcrumbDetails("edit");

        $document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        if (empty($document_id)) {
            $this->errorPage(_("No document specified for editing."));
        }
        $oDocument = Document::get($document_id);
        if (PEAR::isError($oDocument)) {
            $this->errorPage(_("Invalid Document."));
        }
        
        $this->oDocument = $oDocument;
        $this->oFolder = Folder::get($oDocument->getFolderId()); // FIXME do we need to check that this is valid?
        $this->addBreadcrumbs();
        $this->addPortlets("Edit");
        
        $fieldsets = array();        
        
        // we always have a generic.
        array_push($fieldsets, new GenericFieldsetDisplay());
        $fieldsetDisplayReg =& KTFieldsetDisplayRegistry::getSingleton();
        
        $activesets = KTFieldset::getGenericFieldsets();
        foreach ($activesets as $oFieldset) {
            $displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
            array_push($fieldsets, new $displayClass($oFieldset));
        }
        
        // FIXME can we key this on fieldset namespace?  or can we have duplicates?
        // now we get the other fieldsets, IF there is a valid doctype.
		
		// quick solution to the change issue...
		
		if ($newType !== false) {
            $activesets = KTFieldset::getForDocumentType($newType); 
		} else {
		    $activesets = KTFieldset::getForDocumentType($oDocument->getDocumentTypeID()); 
		}
        foreach ($activesets as $oFieldset) {
            $displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
            array_push($fieldsets, new $displayClass($oFieldset));		
        }
        
        $document_data = array();
		$document_data["document"] =& $this->oDocument;
        
        // we want to grab all the md for this doc, since its faster that way.
		$mdlist =& DocumentFieldLink::getByDocument($document_id);

		$field_values = array();
		foreach ($mdlist as $oFieldLink) {
            $field_values[$oFieldLink->getDocumentFieldID()] = $oFieldLink->getValue();
		}
		
		$document_data["field_values"] = $field_values;
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate("kt3/document/edit");       
        $aTemplateData = array(
            'context' => $this,
            'document' => $this->oDocument,
            'document_data' => $document_data, // FIXME what do we need here?
            'fieldsets' => $fieldsets,
		    'has_error' => false,
			'newType' => $newType,
        );
        $oTemplate->setData($aTemplateData);
        return $oTemplate->render();
    }

	function do_update() {
        $document_id = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        if (empty($document_id)) {
            $this->errorPage(_("No document specified for editing."));
        }
        $oDocument = Document::get($document_id);
        if (PEAR::isError($oDocument)) {
            $this->errorPage(_("Invalid Document."));
        }

        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fDocumentId=%d', $oDocument->getId())),
            'message' => _('No name given'),
        );
        $title = KTUtil::arrayGet($_REQUEST, 'generic_title');
        $title = $this->oValidator->validateString($title,
                $aErrorOptions);
        
		$newType = KTUtil::arrayGet($_REQUEST, 'newType');
		if ($newType !== null) {
		    $oDT = DocumentType::get($newType);
			if (PEAR::isError($oDT) || ($oDT == false)) {
				$this->errorRedirectToMain(_('Invalid document type specified for change.'));
			}
		} else {
		    $oDT = null;
		}
        
        $this->oDocument = $oDocument;
        $this->oFolder = Folder::get($oDocument->getFolderId()); // FIXME do we need to check that this is valid?
        $this->addBreadcrumbs();
        $this->addPortlets("Edit");
        
        $fieldsets = array();        

		// FIXME handle true generic document DATA (e.g. title).

        // $activesets = KTFieldset::getGenericFieldsets();
        $activesets =& KTMetadataUtil::fieldsetsForDocument($oDocument);
        foreach ($activesets as $oFieldset) {
            array_push($fieldsets, $oFieldset);
        }
        
        if ($newType == null) {
            $activesets = KTFieldset::getForDocumentType($oDocument->getDocumentTypeID()); 
		} else {
		    $activesets = KTFieldset::getForDocumentType($newType); 
		}
        
        
        // FIXME use array_merge
        foreach ($activesets as $oFieldset) {
            array_push($fieldsets, $oFieldset);		
        }
        
        // erk.  we need all the items that the document _does_ need, _and_ what they have,
		// _and_ what they don't ...
        // we want to grab all the md for this doc, since its faster that way.
		$current_md =& DocumentFieldLink::getByDocument($document_id);
		
		// to get all fields, we merge repeatedly from KTFieldset::get
		
		$field_values = array();
		foreach ($fieldsets as $oFieldSet) {
			$fields =& $oFieldSet->getFields();
			
			// FIXME this doesn't handle multi-fieldset fields - are they possible/meaningful?
			foreach ($fields as $oField) {
				$field_values[$oField->getID()] = array($oField, null);
			}
		}
		
		
		foreach ($current_md as $oFieldLink) {
            $field_values[$oFieldLink->getDocumentFieldID()][1] = $oFieldLink->getValue();
		}
		
		// now, we need the full set of items that this document could contain.
		
		// FIXME this DOES NOT WORK for date items.
		// FIXME that's fine - we don't use range items here...
		$expect_vals = KTUtil::arrayGet($_REQUEST,'kt_core_fieldsets_expect');
		
		foreach ($field_values as $key => $val) {
		    $newVal = KTUtil::arrayGet($_REQUEST, 'metadata_' . $key, null);			
			$wantVal = KTUtil::arrayGet($expect_vals, 'metadata_' . $key, false);
			
			// FIXME this leaves no way to check if someone has actually removed the item.
			// FIXME we probably want to _not_ set anything that could be set ... but then how do you
			// FIXME know about managed values ...
			
			if ($newVal !== null) {
			   $field_values[$key][1] = $newVal; // need the items themselves.
			} else if ($wantVal !== false) {
			   // we sent it out, delete it.
			   
			   unset($field_values[$key]);
			}
		}
		
		
		// finally, we need to pass through and remove null entries (sigh)
		// FIXME alternatively we could build a new set, but that might break stuff?
		
		$final_values = array();
		foreach ($field_values as $aMDPack) {
		    if ($aMDPack[1] !== null) {
			    $final_values[] = $aMDPack;
			}
		}
		$field_values = $final_values;
		
		// FIXME handle md versions.
		//return '<pre>' . print_r($field_values, true) . '</pre>';
		$this->startTransaction();
		$iPreviousMetadataVersionId = $oDocument->getMetadataVersionId();
		$oDocument->startNewMetadataVersion($this->oUser);
		if (PEAR::isError($res)) {
		     $this->errorRedirectToMain('Unable to create a metadata version of the document.');
		}
		
		$oDocument->setName($title);
		$oDocument->setLastModifiedDate(getCurrentDateTime());
		$oDocument->setModifiedUserId($this->oUser->getId());

		// FIXME refactor this into documentutil.
		// document type changing semantics
		if ($newType != null) {
		    $oldType = DocumentType::get($oDocument->getDocumentTypeID());
		    $oDocument->setDocumentTypeID($newType);
			
			// we need to find fieldsets that _were_ in the old one, and _delete_ those.
			$for_delete = array();
			
			$oldFieldsets = KTFieldset::getForDocumentType($oldType);
			$newFieldsets = KTFieldset::getForDocumentType($newType);
			
			// prune from MDPack.
			foreach ($oldFieldsets as $oFieldset) {
				$old_fields = $oFieldset->getFields();
				foreach ($old_fields as $oField) {
					$for_delete[$oField->getId()] = 1;
				}
			}
			
			foreach ($newFieldsets as $oFieldset) {
			    $new_fields = $oFieldset->getFields();
				foreach ($new_fields as $oField) {
				    unset($for_delete[$oField->getId()]);
				}
			}
			
			$newPack = array();
			foreach ($field_values as $MDPack) {
				if (!array_key_exists($MDPack[0]->getId(), $for_delete)) {
					$newPack[] = $MDPack;
				}
			}
			$field_values = $newPack;
			
			
			//var_dump($field_values);
			//exit(0);
		}
		
        $oDocumentTransaction = & new DocumentTransaction($oDocument, 'update metadata.', 'ktcore.transactions.update');
		
        $res = $oDocumentTransaction->create();
		if (PEAR::isError($res)) {
		     $this->errorRedirectToMain('Failed to create transaction.');
		}
		
		$res = $oDocument->update();
		if (PEAR::isError($res)) {
		     $this->errorRedirectToMain('Failed to change basic details about the document..');
		}
		$res = KTDocumentUtil::saveMetadata($oDocument, $field_values);
		
		if (PEAR::isError($res)) {
            var_dump($res);
            exit(0);
		   $this->rollbackTransaction();		
		   
		   // right.  we're rolled out.  now we want to regenerate the page, + errors.
		   
		   // fixme use map (urgh)
		   $new_field_values = array();
		   
		   foreach ($field_values as $aMDSet) {
		       $new_field_values[$aMDSet[0]->getId()] = $aMDSet[1];
			   
		   }
		   
		   // we need to do the displayAdaption.

		   $fieldsetDisplayReg =& KTFieldsetDisplayRegistry::getSingleton();
        		   
		   $new_fieldsets = array();
		   array_push($new_fieldsets, new GenericFieldsetDisplay());
		   foreach ($fieldsets as $oFieldset) {
              $displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
              array_push($new_fieldsets, new $displayClass($oFieldset));		
		   }
			  
           $document_data['document'] = $oDocument;
		   $document_data["field_values"] = $new_field_values;	
		   
		   //print '<pre>' . print_r($document_data['field_values'], true) . '</pre>';
		   
		   $document_data['errors'] = $res->aFailed['field'];
		   
		   $oTemplating =& KTTemplating::getSingleton();
		   $oTemplate =& $oTemplating->loadTemplate("kt3/document/edit");       
		   $aTemplateData = array(
			   'context' => $this,
			   'document' => $this->oDocument,
			   'document_data' => $document_data, // FIXME what do we need here?
			   'fieldsets' => $new_fieldsets,
			   'has_error' => true,
		   );
		   $oTemplate->setData($aTemplateData);
		   return $oTemplate->render();
		   
		   
		} else {
		   
		   // post-triggers.
		   $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
		   $aTriggers = $oKTTriggerRegistry->getTriggers('edit', 'postValidate');
                
		   foreach ($aTriggers as $aTrigger) {
		       $sTrigger = $aTrigger[0];
			   $oTrigger = new $sTrigger;
			   $aInfo = array(
			       "document" => $oDocument,
			   );
			   $oTrigger->setInfo($aInfo);
			   $ret = $oTrigger->postValidate();
		   }
		   
		   $this->commitTransaction();
		   
		   // now we need to say we're ok.
		   // this involves a redirect to view, with a message.
		   // FIXME do not hard-code URLs
		   
		   redirect(KTBrowseUtil::getUrlForDocument($document_id));
		}
		
		
		
	}

}
$d =& new KTEditDocumentDispatcher;
$d->dispatch();


?>
