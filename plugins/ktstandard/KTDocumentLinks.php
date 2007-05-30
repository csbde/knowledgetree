<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
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
 *
 */
require_once(KT_LIB_DIR . "/actions/documentviewlet.inc.php");
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentLink.inc');
require_once(KT_LIB_DIR . '/documentmanagement/LinkType.inc');

require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");
require_once(KT_LIB_DIR . "/browse/browseutil.inc.php");

require_once(KT_LIB_DIR . "/browse/columnregistry.inc.php");


class KTDocumentLinks extends KTPlugin {
    var $sNamespace = "ktstandard.documentlinks.plugin";
    
    function KTDocumentLinks($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Inter-document linking');
        return $res;
    }          

    function setup() {
        $this->registerAction('documentaction', 'KTDocumentLinkAction', 'ktcore.actions.document.link');
        $this->registerAction('documentviewlet', 'KTDocumentLinkViewlet', 'ktcore.viewlets.document.link');        
        $this->registerColumn(_kt('Link Title'), 'ktdocumentlinks.columns.title', 'KTDocumentLinkTitle', 
                              dirname(__FILE__) . '/KTDocumentLinksColumns.php');
        $this->registerAdminPage("linkmanagement", 'KTDocLinkAdminDispatcher', 'documents',
            _kt('Link Type Management'),
            _kt('Manage the different ways documents can be associated with one another.'),
            __FILE__, null);                              
    }
}



class KTDocumentLinkViewlet extends KTDocumentViewlet {
    var $sName = 'ktcore.viewlets.document.link';
    
    function display_viewlet() {
        $oKTTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oKTTemplating->loadTemplate("ktstandard/links/links_viewlet");
        if (is_null($oTemplate)) { return ""; }

        $temp_links_from = DocumentLink::getLinksFromDocument($this->oDocument->getId());
        $temp_links_to = DocumentLink::getLinksToDocument($this->oDocument->getId());

        $links_to = array();
        $links_from = array();        
        
        foreach ($temp_links_from as $link) {
            $oDoc = $link->getChildDocument();
            if (PEAR::isError($oDoc)) {
                continue;
            }
            
            if (KTPermissionUtil::userHasPermissionOnItem($this->oUser, 'ktcore.permissions.read', $oDoc)) {
                $type = $link->getLinkType();
                $aInfo = array(
                    'url' => KTBrowseUtil::getUrlForDocument($oDoc),
                    'name' => $oDoc->getName(),
                    'type' => $type->getName(),
                    'description' => $type->getDescription(),
                );
                
                $links_from[] = $aInfo;
            }
        }
        
        foreach ($temp_links_to as $link) {
            $oDoc = $link->getParentDocument();
            if (PEAR::isError($oDoc)) {
                continue;
            }
            
            if (KTPermissionUtil::userHasPermissionOnItem($this->oUser, 'ktcore.permissions.read', $oDoc)) {
                $type = $link->getLinkType();
                $aInfo = array(
                    'url' => KTBrowseUtil::getUrlForDocument($oDoc),
                    'name' => $oDoc->getName(),
                    'type' => $type->getName(),
                    'description' => $type->getDescription(),
                );
                
                $links_to[] = $aInfo;
            }
        }        

        if (empty($links_from) && empty($links_to)) {
            return "";
        }
        
        $oTemplate->setData(array(
            'context' => $this,
            'links_from' => $links_from,
            'links_to' => $links_to,
        ));
        return $oTemplate->render();
    }

}

class KTDocumentLinkAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.link';

    function getDisplayName() {
        return _kt('Links');
    }

    // display existing links
    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/document_links');
        $this->oPage->setBreadcrumbDetails(_kt("Links"));
        $this->oPage->setTitle(_kt("Links"));

        $oDocument = Document::get(
                KTUtil::arrayGet($_REQUEST, 'fDocumentId', 0)
        );

        $oReadPermission =& KTPermission::getByName('ktcore.permissions.read');
        $oWritePermission =& KTPermission::getByName('ktcore.permissions.write');
        

        $aTemplateData = array(
              'context' => $this,
              'links_from' => DocumentLink::getLinksFromDocument($oDocument->getId()),
              'links_to' => DocumentLink::getLinksToDocument($oDocument->getId()),
              'read_permission' => KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oReadPermission, $this->oDocument),
              'write_permission' => KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oWritePermission, $this->oDocument),
        );
        
        
        return $oTemplate->render($aTemplateData);                  
    }




    // select a target for the link
    function do_new() {
        $this->oPage->setBreadcrumbDetails(_kt("New Link"));
        $this->oPage->setTitle(_kt("New Link"));

        $oPermission =& KTPermission::getByName('ktcore.permissions.write');
        if (PEAR::isError($oPermission) || 
            !KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument)) {
            $this->errorRedirectToMain(_kt('You do not have sufficient permissions to add a document link'), sprintf("fDocumentId=%d", $this->oDocument->getId()));
            exit(0);
        }

        $oParentDocument =& $this->oDocument;
        
        if (PEAR::isError($oParentDocument)) { 
            $this->errorRedirectToMain(_kt('Invalid parent document selected.'));
            exit(0);
        }

        $oFolder = Folder::get(KTUtil::arrayGet($_REQUEST, 'fFolderId', $oParentDocument->getFolderID()));
        if (PEAR::isError($oFolder) || ($oFolder == false)) { 
            $this->errorRedirectToMain(_kt('Invalid folder selected.'));
            exit(0);
        }
        $iFolderId = $oFolder->getId();
        
        // Setup the collection for move display.
        
        $collection = new AdvancedCollection();
        $aBaseParams = array('fDocumentId'=>$oParentDocument->getId());


        $oCR =& KTColumnRegistry::getSingleton();

        $col = $oCR->getColumn('ktcore.columns.singleselection');
        $col->setOptions(array('qs_params'=>kt_array_merge($aBaseParams, array('fFolderId'=>$oFolder->getId()))));
        $collection->addColumn($col);        
        
        $col = $oCR->getColumn('ktdocumentlinks.columns.title');
        $col->setOptions(array('qs_params'=>kt_array_merge($aBaseParams, array('fFolderId'=>$oFolder->getId()))));
        $collection->addColumn($col);
        
        $qObj = new BrowseQuery($iFolderId);
        $collection->setQueryObject($qObj);

        $aOptions = $collection->getEnvironOptions();
        $aOptions['result_url'] = KTUtil::addQueryString($_SERVER['PHP_SELF'], 
                                                         array(kt_array_merge($aBaseParams, array('fFolderId' => $oFolder->getId()))));

        $collection->setOptions($aOptions);

	$oWF =& KTWidgetFactory::getSingleton();
	$oWidget = $oWF->get('ktcore.widgets.collection', 
			     array('label' => _kt('Target Document'),
				   'description' => _kt('Use the folder collection and path below to browse to the document you wish to link to.'),
				   'required' => true,
				   'name' => 'browse',
                                   'folder_id' => $oFolder->getId(),
                                   'bcurl_params' => $aBaseParams,
				   'collection' => $collection));


        
        $aTemplateData = array(
              'context' => $this,
              'folder' => $oFolder,
              'parent' => $oParentDocument,
              'breadcrumbs' => $aBreadcrumbs,
              'collection' => $oWidget,
              'link_types' => LinkType::getList("id > 0"),
        );
        
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/link');
        return $oTemplate->render($aTemplateData);                  
    }

    // select a type for the link
    function do_type_select() {
        $this->oPage->setBreadcrumbDetails(_kt("link"));

        $oParentDocument = Document::get(KTUtil::arrayGet($_REQUEST, 'fDocumentId'));
        if (PEAR::isError($oParentDocument)) { 
            $this->errorRedirectToMain(_kt('Invalid parent document selected.'));
            exit(0);
        }

        /*
        print '<pre>';
        var_dump($_REQUEST);
        exit(0);
        */


        $oTargetDocument = Document::get(KTUtil::arrayGet($_REQUEST, '_d'));
        if (PEAR::isError($oTargetDocument)) { 
            $this->errorRedirectToMain(_kt('Invalid target document selected.'));
            exit(0);
        }


        // form fields
        $aFields = array();
        
        $aVocab = array();
        foreach(LinkType::getList("id > 0") as $oLinkType) {
            $aVocab[$oLinkType->getID()] = $oLinkType->getName();
        }        

        $aOptions = array('vocab' => $aVocab);
        $aFields[] = new KTLookupWidget(
                _kt('Link Type'), 
                _kt('The type of link you wish to use'), 
                'fLinkTypeId', 
                null,
                $this->oPage,
                true,
                null,
                null,
                $aOptions);
                
        $aTemplateData = array(
              'context' => $this,
              'parent_id' => $oParentDocument->getId(),
              'target_id' => $oTargetDocument->getId(),
              'fields' => $aFields,
        );
        
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/link_type_select');
        return $oTemplate->render($aTemplateData);                  


    }



    // make the link
    function do_make_link() {
        $this->oPage->setBreadcrumbDetails(_kt("link"));

        // check validity of things
        $oParentDocument = Document::get(KTUtil::arrayGet($_REQUEST, 'fDocumentId'));
        if (PEAR::isError($oParentDocument)) { 
            $this->errorRedirectToMain(_kt('Invalid parent document selected.'));
            exit(0);
        }

        $oTargetDocument = Document::get(KTUtil::arrayGet($_REQUEST, 'fTargetDocumentId'));
        if (PEAR::isError($oTargetDocument)) { 
            $this->errorRedirectToMain(_kt('Invalid target document selected.'));
            exit(0);
        }

        $oLinkType = LinkType::get(KTUtil::arrayGet($_REQUEST, 'fLinkTypeId'));
        if (PEAR::isError($oLinkType)) { 
            $this->errorRedirectToMain(_kt('Invalid link type selected.'));
            exit(0);
        }


        // create document link
        $this->startTransaction();
        
        $oDocumentLink =& DocumentLink::createFromArray(array(
            'iParentDocumentId' => $oParentDocument->getId(),
            'iChildDocumentId'  => $oTargetDocument->getId(),
            'iLinkTypeId'       => $oLinkType->getId(),
        ));

        if (PEAR::isError($oDocumentLink)) {
            $this->errorRedirectToMain(_kt('Could not create document link'), sprintf('fDocumentId=%d', $oParentDocument->getId()));
            exit(0);
        }

        $this->commitTransaction();

        $this->successRedirectToMain(_kt('Document link created'), sprintf('fDocumentId=%d', $oParentDocument->getId()));
        exit(0);
    }


    // delete a link
    function do_delete() {
        $this->oPage->setBreadcrumbDetails(_kt("link"));

        // check security
        $oPermission =& KTPermission::getByName('ktcore.permissions.write');
        if (PEAR::isError($oPermission) || 
            !KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument)) {
            $this->errorRedirectToMain(_kt('You do not have sufficient permissions to delete a link'), sprintf("fDocumentId=%d", $this->oDocument->getId()));
            exit(0);
        }


        // check validity of things
        $oDocumentLink = DocumentLink::get(KTUtil::arrayGet($_REQUEST, 'fDocumentLinkId'));
        if (PEAR::isError($oDocumentLink)) { 
            $this->errorRedirectToMain(_kt('Invalid document link selected.'));
            exit(0);
        }
        $oParentDocument = Document::get(KTUtil::arrayGet($_REQUEST, 'fDocumentId'));
        if (PEAR::isError($oParentDocument)) { 
            $this->errorRedirectToMain(_kt('Invalid document selected.'));
            exit(0);
        }
        
        // do deletion
        $this->startTransaction();
        
        $res = $oDocumentLink->delete();
        
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(_kt('Could not delete document link'), sprintf('fDocumentId=%d', $oParentDocument->getId()));
            exit(0);
        }

        $this->commitTransaction();

        $this->successRedirectToMain(_kt('Document link deleted'), sprintf('fDocumentId=%d', $oParentDocument->getId()));
        exit(0);
    }
}
class KTDocLinkAdminDispatcher extends KTAdminDispatcher {
    var $sHelpPage = 'ktcore/admin/link type management.html';

   // Breadcrumbs base - added to in methods
    function check() {
        return true;
    }

    function do_main() {
        $this->aBreadcrumbs[] = array('name' => _kt('Document Links'));
        $this->oPage->setBreadcrumbDetails(_kt("view"));
        
        $aLinkTypes =& LinkType::getList('id > 0');
        
        $addLinkForm = array();
        // KTBaseWidget($sLabel, $sDescription, $sName, $value, $oPage, $bRequired = false, $sId = null, $aErrors = null, $aOptions = null) 
        $addLinkForm[] = new KTStringWidget(_kt('Name'), _kt('A short, human-readable name for the link type.'), 'fName', null, $this->oPage, true);
        $addLinkForm[] = new KTStringWidget(_kt('Description'), _kt('A short brief description of the relationship implied by this link type.'), 'fDescription', null, $this->oPage, true);
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/linktypesadmin');       
        $oTemplate->setData(array(
            "context" => $this,
            "add_form" => $addLinkForm,
            "links" => $aLinkTypes,
        ));
        return $oTemplate;
    }
    
    function do_edit() {
        $link_id = KTUtil::arrayGet($_REQUEST, 'fLinkTypeId', null, false);
        if ($link_id === null) {
           $this->errorRedirectToMain(_kt("Please specify a link type to edit."));
        }
        
        $oLinkType =& LinkType::get($link_id);
        
        $this->aBreadcrumbs[] = array('name' => _kt('Document Links'));
        $this->oPage->setBreadcrumbDetails(_kt("view"));
        
        $aLinkTypes =& LinkType::getList('id > 0');
        
        $editLinkForm = array();
        // KTBaseWidget($sLabel, $sDescription, $sName, $value, $oPage, $bRequired = false, $sId = null, $aErrors = null, $aOptions = null) 
        $editLinkForm[] = new KTStringWidget(_kt('Name'), _kt('A short, human-readable name for the link type.'), 'fName', $oLinkType->getName(), $this->oPage, true);
        $editLinkForm[] = new KTStringWidget(_kt('Description'), _kt('A short brief description of the relationship implied by this link type.'), 'fDescription', $oLinkType->getDescription(), $this->oPage, true);
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/linktypesadmin');       
        $oTemplate->setData(array(
            "context" => $this,
            "edit_form" => $editLinkForm,
            "old_link" => $oLinkType,
            "links" => $aLinkTypes,
        ));
        return $oTemplate;
    }    
    

    function do_update() {
        $link_id = KTUtil::arrayGet($_REQUEST, 'fLinkTypeId', null, false);
        if ($link_id === null) {
            $this->errorRedirectToMain(_kt("Please specify a link type to update."));
        }
        
        $name = KTUtil::arrayGet($_REQUEST, 'fName');        
        $description = KTUtil::arrayGet($_REQUEST, 'fDescription');

        if (empty($name) || empty($description)) { // for bonus points, make this go to edit, and edit catch it.
            $this->errorRedirectToMain(_kt('Please enter information for all fields.'));
        }
        
        $oLinkType =& LinkType::get($link_id);
        
        $oLinkType->setName($name);
        $oLinkType->setDescription($description);
        $oLinkType->update();
        
        $this->successRedirectToMain(_kt("Link Type updated."));
    }
    
    function do_add() {
        $name = KTUtil::arrayGet($_REQUEST, 'fName');        
        $description = KTUtil::arrayGet($_REQUEST, 'fDescription');

        if (empty($name) || empty($description)) {
            $this->errorRedirectToMain(_kt('Please enter information for all fields.'));
        }
        
        $oLinkType = new LinkType($name, $description);
        $oLinkType->create();
             
        //$oLinkType =& LinkType::createFromArray(array("sName" => $name, "sDescription" => $description));
        
        $this->successRedirectToMain(_kt("Link Type created."));
    }
    
    function do_delete() {
        $types_to_delete = KTUtil::arrayGet($_REQUEST, 'fLinksToDelete');         // is an array.

        if (empty($types_to_delete)) {
            $this->errorRedirectToMain(_kt('Please select one or more link types to delete.'));
        }
        
        $count = 0;
        foreach ($types_to_delete as $link_id) {
            $oLinkType = LinkType::get($link_id);

            foreach(DocumentLink::getList(sprintf("link_type_id = %d", $link_id)) as $oLink) {
                $oLink->delete();
            }
            
            $oLinkType->delete(); // technically, this is a bad thing
            $count += 1; 
        }
        
        //$oLinkType =& LinkType::createFromArray(array("sName" => $name, "sDescription" => $description));
        
        $this->successRedirectToMain($count . " " . _kt("Link types deleted."));
    }


}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTDocumentLinks', 'ktstandard.documentlinks.plugin', __FILE__);



?>
