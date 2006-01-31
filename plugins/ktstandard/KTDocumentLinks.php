<?php

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentLink.inc');
require_once(KT_LIB_DIR . '/documentmanagement/LinkType.inc');

require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");
require_once(KT_LIB_DIR . "/browse/browseutil.inc.php");


class KTDocumentLinkTitle extends TitleColumn {
    
    function buildDocumentLink($aDataRow) {
        $baseurl = KTUtil::arrayGet($this->aOptions, "folderurl", "");
        $parentDocumentId = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        return sprintf('%s?action=type_select&fDocumentId=%d&fTargetDocumentId=%d', $baseurl, $parentDocumentId, $aDataRow["document"]->getId());
    }

    function buildFolderLink($aDataRow) {
        $baseurl = KTUtil::arrayGet($this->aOptions, "folderurl", "");
        $kt_path_info = KTUtil::arrayGet($_REQUEST, 'kt_path_info');
        $parentDocumentId = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
        
        if (empty($kt_path_info)) {
            return sprintf('%s?action=new&fDocumentId=%d&fFolderId=%d', $baseurl, $parentDocumentId, $aDataRow["folder"]->getId());
        } else {
            return sprintf('%s?kt_path_info=%s&action=new&ftDocumentId=%d&fFolderId=%d', $baseurl, $kt_path_info, $parentDocumentId, $aDataRow["folder"]->getId());
        }
    }
}




class KTDocumentLinks extends KTPlugin {
    var $sNamespace = "ktstandard.documentlinks.plugin";

    function setup() {
        $this->registerAction('documentaction', 'KTDocumentLinkAction', 'ktcore.actions.document.link');
    }
}

class KTDocumentLinkAction extends KTDocumentAction {
    var $sDisplayName = 'Links';
    var $sName = 'ktcore.actions.document.link';

    // display existing links
    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/document_links');
        $this->oPage->setBreadcrumbDetails(_("Links"));
        $this->oPage->setTitle(_("Links"));

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
        $this->oPage->setBreadcrumbDetails(_("New Link"));
        $this->oPage->setTitle(_("New Link"));

        $oPermission =& KTPermission::getByName('ktcore.permissions.write');
        if (PEAR::isError($oPermission) || 
            !KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument)) {
            $this->errorRedirectToMain(_('You do not have sufficient permissions to add a document link'), sprintf("fDocumentId=%d", $this->oDocument->getId()));
            exit(0);
        }

        $oParentDocument =& $this->oDocument;
        
        if (PEAR::isError($oParentDocument)) { 
            $this->errorRedirectToMain(_('Invalid parent document selected.'));
            exit(0);
        }

        $oFolder = Folder::get(KTUtil::arrayGet($_REQUEST, 'fFolderId', $oParentDocument->getFolderID()));
        if (PEAR::isError($oFolder) || ($oFolder == false)) { 
            $this->errorRedirectToMain(_('Invalid folder selected.'));
            exit(0);
        }
        $iFolderId = $oFolder->getId();
        
        // Setup the collection for move display.
        
        $collection = new DocumentCollection();
        $collection->addColumn(new KTDocumentLinkTitle("Target Documents","title"));        
        
        $qObj = new BrowseQuery($iFolderId);
        $collection->setQueryObject($qObj);

        $batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
        $batchSize = 20;

        $resultURL = sprintf("?action=new&fDocumentId=%d", $oParentDocument->getId());
        $collection->setBatching($resultURL, $batchPage, $batchSize);

        // ordering. (direction and column)
        $displayOrder = KTUtil::arrayGet($_REQUEST, 'sort_order', "asc");
        if ($displayOrder !== "asc") { $displayOrder = "desc"; }
        $displayControl = KTUtil::arrayGet($_REQUEST, 'sort_on', "title");

        $collection->setSorting($displayControl, $displayOrder);

        $collection->getResults();    
        
        $aBreadcrumbs = array();
        $folder_path_names = $oFolder->getPathArray();
        $folder_path_ids = explode(',', $oFolder->getParentFolderIds());
        
        if ($folder_path_ids[0] == 0) {
            $folder_path_ids = array();
        }
        $folder_path_ids[] = $oFolder->getId();

        foreach (range(0, count($folder_path_ids) - 1) as $index) {
            $id = $folder_path_ids[$index];
            $url = sprintf("?action=new&fDocumentId=%d&fFolderId=%d", $oParentDocument->getId(), $id);
            $aBreadcrumbs[] = array("url" => $url, "name" => $folder_path_names[$index]);
        }
        
        $aTemplateData = array(
              'context' => $this,
              'folder' => $oFolder,
              'breadcrumbs' => $aBreadcrumbs,
              'collection' => $collection,
              'collection_breadcrumbs' => $aBreadcrumbs,
              'link_types' => LinkType::getList("id > 0"),
        );
        
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/link');
        return $oTemplate->render($aTemplateData);                  
    }

    // select a type for the link
    function do_type_select() {
        $this->oPage->setBreadcrumbDetails(_("link"));

        $oParentDocument = Document::get(KTUtil::arrayGet($_REQUEST, 'fDocumentId'));
        if (PEAR::isError($oParentDocument)) { 
            $this->errorRedirectToMain(_('Invalid parent document selected.'));
            exit(0);
        }

        $oTargetDocument = Document::get(KTUtil::arrayGet($_REQUEST, 'fTargetDocumentId'));
        if (PEAR::isError($oTargetDocument)) { 
            $this->errorRedirectToMain(_('Invalid target document selected.'));
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
                _('Link Type'), 
                _('The type of link you wish to use'), 
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
        $this->oPage->setBreadcrumbDetails(_("link"));

        // check validity of things
        $oParentDocument = Document::get(KTUtil::arrayGet($_REQUEST, 'fDocumentId'));
        if (PEAR::isError($oParentDocument)) { 
            $this->errorRedirectToMain(_('Invalid parent document selected.'));
            exit(0);
        }

        $oTargetDocument = Document::get(KTUtil::arrayGet($_REQUEST, 'fTargetDocumentId'));
        if (PEAR::isError($oTargetDocument)) { 
            $this->errorRedirectToMain(_('Invalid target document selected.'));
            exit(0);
        }

        $oLinkType = LinkType::get(KTUtil::arrayGet($_REQUEST, 'fLinkTypeId'));
        if (PEAR::isError($oLinkType)) { 
            $this->errorRedirectToMain(_('Invalid link type selected.'));
            exit(0);
        }


        // create document link
        $this->startTransaction();
        
        $oDocumentLink =& DocumentLink::createFromArray(array(
/*            'parent_document_id' => $oParentDocument->getId(),
            'child_document_id'  => $oTargetDocument->getId(),
            'link_type_id'       => $oLinkType->getId(),*/
            'iParentDocumentId' => $oParentDocument->getId(),
            'iChildDocumentId'  => $oTargetDocument->getId(),
            'iLinkTypeId'       => $oLinkType->getId(),
        ));

        if (PEAR::isError($oDocumentLink)) {
            $this->errorRedirectToMain(_('Could not create document link'), sprintf('fDocumentId=%d', $oParentDocument->getId()));
            exit(0);
        }

        $this->commitTransaction();

        $this->successRedirectToMain(_('Document link created'), sprintf('fDocumentId=%d', $oParentDocument->getId()));
        exit(0);
    }


    // delete a link
    function do_delete() {
        $this->oPage->setBreadcrumbDetails(_("link"));

        // check security
        $oPermission =& KTPermission::getByName('ktcore.permissions.write');
        if (PEAR::isError($oPermission) || 
            !KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument)) {
            $this->errorRedirectToMain(_('You do not have sufficient permissions to delete a link'), sprintf("fDocumentId=%d", $this->oDocument->getId()));
            exit(0);
        }


        // check validity of things
        $oDocumentLink = DocumentLink::get(KTUtil::arrayGet($_REQUEST, 'fDocumentLinkId'));
        if (PEAR::isError($oDocumentLink)) { 
            $this->errorRedirectToMain(_('Invalid document link selected.'));
            exit(0);
        }
        $oParentDocument = Document::get(KTUtil::arrayGet($_REQUEST, 'fDocumentId'));
        if (PEAR::isError($oParentDocument)) { 
            $this->errorRedirectToMain(_('Invalid document selected.'));
            exit(0);
        }
        
        // do deletion
        $this->startTransaction();
        
        $res = $oDocumentLink->delete();
        
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(_('Could not delete document link'), sprintf('fDocumentId=%d', $oParentDocument->getId()));
            exit(0);
        }

        $this->commitTransaction();

        $this->successRedirectToMain(_('Document link deleted'), sprintf('fDocumentId=%d', $oParentDocument->getId()));
        exit(0);
    }


}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTDocumentLinks', 'ktstandard.documentlinks.plugin', __FILE__);



?>