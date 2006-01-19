<?php

/**
 * $Id$
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Brad Shuttleworth <brad@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 */

// main library routines and defaults
require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");
require_once(KT_LIB_DIR . "/browse/browseutil.inc.php");

require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentType.inc");
require_once(KT_LIB_DIR . "/documentmanagement/Document.inc");

require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");
require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_DIR . '/plugins/ktcore/KTFolderActions.php');


$sectionName = "browse";

class BrowseDispatcher extends KTStandardDispatcher {

    var $oFolder = null;
    var $sSection = "browse";
    var $browse_mode = null;
    var $query = null;
    var $resultURL;
    var $sHelpPage = 'ktcore/browse/browse.html';

    function BrowseDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _('Browse')),
        );
        return parent::KTStandardDispatcher();
    }
    
    function check() {
        $this->browse_mode = KTUtil::arrayGet($_REQUEST, 'fBrowseMode', "folder"); 
        $action = KTUtil::arrayGet($_REQUEST, $this->event_var, 'main');
        
        // catch the alternative actions.
        if ($action != 'main') {
            return true;
        } 
        
        // if we're going to main ...
        if ($this->browse_mode == 'folder') {
            // which folder.
            $in_folder_id = KTUtil::arrayGet($_REQUEST, "fFolderId", 1);
            $folder_id = (int) $in_folder_id; // conveniently, will be 0 if not possible.
            if ($folder_id == 0) {
                $folder_id = 1;
            }

            // here we need the folder object to do the breadcrumbs.
            $oFolder =& Folder::get($folder_id);
            $this->oFolder =& $oFolder;
            if (PEAR::isError($oFolder)) {
                $this->oPage->addError(_("invalid folder"));
                $folder_id = 1;
                $oFolder =& Folder::get($folder_id);
            }
            
            // we now have a folder, and need to create the query.
            $this->oQuery =  new BrowseQuery($oFolder->getId());
            
            $this->aBreadcrumbs = array_merge($this->aBreadcrumbs,
                KTBrowseUtil::breadcrumbsForFolder($oFolder));

            $portlet = new KTActionPortlet(_("Folder Actions"));
            $aActions = KTFolderActionUtil::getFolderActionsForFolder($oFolder, $this->oUser);        
            $portlet->setActions($aActions,null);
            $this->oPage->addPortlet($portlet);
            $this->resultURL = "?fFolderId=" . $oFolder->getId();        
            
        } else if ($this->browse_mode == 'category') {
            return false;
        } else if ($this->browse_mode == 'document_type') {
            // FIXME implement document_type browsing.
            $doctype = KTUtil::arrayGet($_REQUEST, 'fType',null);
            $oDocType = DocumentType::get($doctype);
            if (PEAR::isError($oDocType) || ($oDocType == false)) {
                $this->errorRedirectToMain('No Document Type selected.');
                exit(0);
            }
            
            $this->oQuery =  new TypeBrowseQuery($oDocType);
            
            // FIXME probably want to redirect to self + action=selectType
            $this->aBreadcrumbs[] = array('name' => _('Document Types')); 
            
            $this->resultURL = "?fType=" . $doctype . "&fBrowseMode=document_type";        
            
            
            
        } else {
            // FIXME what should we do if we can't initiate the browse?  we "pretend" to have no perms.
            return false;
        }

        return true;
    }

    function do_main() {
        $collection = new DocumentCollection;
        
        
        $collection->addColumn(new SelectionColumn("Browse Selection","selection"));
        $collection->addColumn(new TitleColumn("Test 1 (title)","title"));
        $collection->addColumn(new DateColumn(_("Created"),"created", "getCreatedDateTime"));
        $collection->addColumn(new DateColumn(_("Last Modified"),"modified", "getLastModifiedDate"));
        $collection->addColumn(new UserColumn(_('Creator'),'creator_id','getCreatorID'));
        
        
        // setup the folderside add actions
        // FIXME do we want to use folder actions?
        
        $batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
        $batchSize = 20;
        
        
        $collection->setBatching($this->resultURL, $batchPage, $batchSize); 
        
        
        // ordering. (direction and column)
        $displayOrder = KTUtil::arrayGet($_REQUEST, 'sort_order', "asc");		
        if ($displayOrder !== "asc") { $displayOrder = "desc"; }
        $displayControl = KTUtil::arrayGet($_REQUEST, 'sort_on', "title");		
        
        
        $collection->setSorting($displayControl, $displayOrder);
        
        // add in the query object.
        $qObj = $this->oQuery;
        $collection->setQueryObject($qObj);
        
        // breadcrumbs
        // FIXME handle breadcrumbs
        $collection->getResults();
        
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/browse");
        $aTemplateData = array(
              "context" => $this,
              "collection" => $collection,
              'browse_mode' => $this->browse_mode,
        );
        return $oTemplate->render($aTemplateData);
    }   
    
    function do_selectCategory() {
        $this->errorRedirectToMain('category browsing is not yet implemented.');
            
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/browse_category");
        $aTemplateData = array(
              "context" => $this,
        );
        return $oTemplate->render($aTemplateData);
    }
    
    function do_selectType() {
        $aTypes = DocumentType::getList();
        // FIXME what is the error message?
        
        if (empty($aTypes)) {
            $this->errorRedirectToMain('No document types available.');
            exit(0);
        } 
        
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/browse_types");
        $aTemplateData = array(
              "context" => $this,
              "document_types" => $aTypes,
        );
        return $oTemplate->render($aTemplateData);
    }
    
    function do_startDelete() {
        $this->oPage->setTitle('Delete Files and Folders');
        $this->oPage->setBreadcrumbDetails('Delete Files and Folders');
    
        $aFolderSelection = KTUtil::arrayGet($_REQUEST, 'selection_f' , array());
        $aDocumentSelection = KTUtil::arrayGet($_REQUEST, 'selection_d' , array());
        
        $aFields = array();
        $aFields[] = new KTStringWidget(_('Reason'), _('The reason for the deletion of these documents and folders for historical purposes.'), 'sReason', "", $this->oPage, true);
        
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/folder/mass_delete");
        $aTemplateData = array(
              "context" => $this,
              'form_fields' => $aFields,
              'folders' => $aFolderSelection,
              'documents' => $aDocumentSelection,
        );
        return $oTemplate->render($aTemplateData);        
    }

    function do_doDelete() {
        $aFolderSelection = KTUtil::arrayGet($_REQUEST, 'selection_f' , array());
        $aDocumentSelection = KTUtil::arrayGet($_REQUEST, 'selection_d' , array());
        
        $aErrorOptions = array('message' => _('You must supply a reason'));
        
        $res = KTUtil::arrayGet($_REQUEST,'sReason');
        $sReason = $this->oValidator->notEmpty($res, $aErrorOptions);
        
        // FIXME we need to sort out the (inconsistent) use of transactions here.
        $aFolders = array();
        $aDocuments = array();
        foreach ($aFolderSelection as $id) {
            $oF = Folder::get($id);
            if (PEAR::isError($oF) || ($oF == false)) {
                return $this->errorRedirectToMain(_('Invalid Folder selected.'));
            } else {
                $aFolders[] = $oF;
            }
        }
        foreach ($aDocumentSelection as $id) {
            $oD = Document::get($id);
            if (PEAR::isError($oD) || ($oD == false)) {
                return $this->errorRedirectToMain(_('Invalid Document selected.'));
            } else {
                $aDocuments[] = $oD;
            }
        }
        
        foreach ($aFolders as $oFolder) {
            $res = KTFolderUtil::delete($oFolder, $this->oUser, $sReason);
            if (PEAR::isError($res)) {
                return $this->errorRedirectToMain($res->getMessage());
            }
        }
        foreach ($aDocuments as $oDocument) {
            $res = KTDocumentUtil::delete($oDocument, $sReason);
            if (PEAR::isError($res)) {
                return $this->errorRedirectToMain($res->getMessage());
            }
        }
        
        if ($this->oFolder == null) {
            $oFI = 1;
        } else { $oFI = $this->oFolder->getId(); }
        $this->successRedirectToMain('Folders and Documents Deleted.',sprintf('fFolderId=%d', $oFI));
    }
}

$oDispatcher = new BrowseDispatcher();
$oDispatcher->dispatch();

?>

