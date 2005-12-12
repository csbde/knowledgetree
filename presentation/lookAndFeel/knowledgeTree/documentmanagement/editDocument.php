<?php
/*
 * Ruthlessly gutted from addDocument.php
 *
 * @version $Revision$
 * @author Brad Shuttleworth, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once("../../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/dispatcher.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentLink.inc');

require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/visualpatterns/PatternMetaData.inc');

require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

// widget includes.
require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/actions/documentaction.inc.php");


class KTEditDocumentDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;
    var $oDocument = null;
    var $oFolder = null;
    var $aBreadcrumbs = array(
            array('action' => 'browse', 'name' => 'Browse'),
        );
    var $sSection = "view_details";
    
    // FIXME identify the current location somehow.
    function addPortlets($currentaction = null) {
        $actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser);
        $oPortlet = new KTActionPortlet(_("Document Actions"));
        $oPortlet->setActions($actions, $currentaction);
		
        $this->oPage->addPortlet($oPortlet);
    }
    
    function addBreadcrumbs() {
        $folder_id = $this->oDocument->getFolderId(); // conveniently, will be 0 if not possible.
        if ($folder_id == 0) { $folder_id = 1; }
        
        // here we need the folder object to do the breadcrumbs.
        $oFolder =& Folder::get($folder_id);
        if (PEAR::isError($oFolder)) {
           $this->oPage->addError(_("invalid folder"));
           $folder_id = 1;
           $oFolder =& Folder::get($folder_id);
        }
        
        // do the breadcrumbs.

        // skip root.
        $folder_path_names = array_slice($oFolder->getPathArray(), 1);
        $folder_path_ids = array_slice(explode(',', $oFolder->getParentFolderIds()), 1);
        
        $parents = count($folder_path_ids);
        
        if ($parents != 0) {
            foreach (range(0,$parents) as $index) {
                $this->aBreadcrumbs[] = array("url" => "../browse.php?fFolderId=" . $folder_path_ids[$index], "name" => $folder_path_names[$index]);
            }
        }
        
        // now add this folder, _if we aren't in 1_.
        if ($folder_id != 1) {
            $this->aBreadcrumbs[] = array("url" => "../browse.php?fFolderId=" . $folder_id, "name" => $oFolder->getName());
        }
        
        // now add the document
        $this->aBreadcrumbs[] = array("name" => $this->oDocument->getName());
    }

    function errorPage($errorMessage) {
        $this->handleOutput($errorMessage);
        exit(0);
    }

	// FIXME move the render-setup to be a helper, to avoid the current  two-location-copy-paste in do_main and do_update.

    function do_main() {
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
        $activesets = KTFieldset::getForDocumentType($oDocument->getDocumentTypeID()); 
        foreach ($activesets as $oFieldset) {
            $displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
            array_push($fieldsets, new $displayClass($oFieldset));		
        }
        
        
        $document_data = array();
		$document_data["document"] =& $this->oDocument;
        
        // we want to grab all the md for this doc, since its faster that way.
		$mdlist =& DocumentFieldLink::getList(array('document_id = ?', array($document_id)));

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
        
        $activesets = KTFieldset::getForDocumentType($oDocument->getDocumentTypeID()); 
        
        
        // FIXME use array_merge
        foreach ($activesets as $oFieldset) {
            array_push($fieldsets, $oFieldset);		
        }
        
        // erk.  we need all the items that the document _does_ need, _and_ what they have,
		// _and_ what they don't ...
        // we want to grab all the md for this doc, since its faster that way.
		$current_md =& DocumentFieldLink::getList(array('document_id = ?', array($document_id)));
		
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
		
		$res = KTDocumentUtil::createMetadataVersion($oDocument);
		if (PEAR::isError($res)) {
		     $this->errorRedirectToMain('Unable to create a metadata version of the document.');
		}
		
		$oDocument->setLastModifiedDate(getCurrentDateTime());
		$oDocument->setModifiedUserId($this->oUser->getId());
		$oDocument->setMetadataVersion($oDocument->getMetadataVersion() + 1);
        $oDocumentTransaction = & new DocumentTransaction($oDocument->getID(), 'update metadata.', UPDATE);
		
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
		   $this->commitTransaction();
		   
		   // now we need to say we're ok.
		   // this involves a redirect to view, with a message.
		   // FIXME do not hard-code URLs
		   
		   redirect('view.php?fDocumentId=' . $document_id);
		}
		
		
		
	}

}
$d =& new KTEditDocumentDispatcher;
$d->dispatch();


?>
