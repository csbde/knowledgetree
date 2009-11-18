<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
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
        if (is_null($oTemplate)) { return ''; }

        $iDocId = $this->oDocument->getId();
        $temp_links_from = DocumentLink::getLinksFromDocument($iDocId);
        $temp_links_to = DocumentLink::getLinksToDocument($iDocId);
        $temp_links_external = DocumentLink::getExternalLinks($iDocId);

        $links_to = array();
        $links_from = array();
        $links_external = array();

        if(!empty($temp_links_from)){
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
        }

        if(!empty($temp_links_to)){
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
        }

        if(!empty($temp_links_external)){
            foreach ($temp_links_external as $link) {
                $type = $link->getLinkType();

                $aInfo = array(
                        'url' => $link->getTargetUrl(),
                        'name' => $link->getTargetName(),
                        'type' => $type->getName(),
                        'description' => $type->getDescription(),
                );

                $links_external[] = $aInfo;
            }
        }

        if (empty($links_from) && empty($links_to) && empty($links_external)) {
            return '';
        }

        $oTemplate->setData(array(
            'context' => $this,
            'links_from' => $links_from,
            'links_to' => $links_to,
            'links_external' => $links_external,
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

        $iDocId = $_REQUEST['fDocumentId'];
        $oDocument = Document::get($iDocId);

        $oReadPermission =& KTPermission::getByName('ktcore.permissions.read');
        $oWritePermission =& KTPermission::getByName('ktcore.permissions.write');

        // Add an electronic signature
    	global $default;
    	if($default->enableESignatures){
    	    $signatures = true;
    	    $submit['sUrl'] = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
    	    $submit['heading'] = _kt('You are attempting to delete a document link');
    	}else{
    	    $signatures = false;
    	}

        $aTemplateData = array(
              'context' => $this,
              'iDocId' => $iDocId,
              'links_external' => DocumentLink::getExternalLinks($iDocId),
              'links_from' => DocumentLink::getLinksFromDocument($iDocId),
              'links_to' => DocumentLink::getLinksToDocument($iDocId),
              'read_permission' => KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oReadPermission, $this->oDocument),
              'write_permission' => KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oWritePermission, $this->oDocument),
              'submit' => $submit,
              'signatures' => $signatures
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
        $col = $oCR->getColumn('ktcore.columns.selection');
        $aColOptions = array();
        $aColOptions['qs_params'] = kt_array_merge($aBaseParams, array('fFolderId'=>$oFolder->getId()));
        $aColOptions['show_folders'] = false;
        $aColOptions['show_documents'] = true;
        $aColOptions['rangename'] = 'linkselection[]';
        $col->setOptions($aColOptions);
        $collection->addColumn($col);

        $col = $oCR->getColumn('ktdocumentlinks.columns.title');
        $col->setOptions(array('qs_params'=>kt_array_merge($aBaseParams, array('action' => 'new', 'fFolderId'=>$oFolder->getId()))));
        $collection->addColumn($col);

        $qObj = new BrowseQuery($iFolderId);
        $collection->setQueryObject($qObj);

        $aOptions = $collection->getEnvironOptions();
        //$aOptions['is_browse'] = true;
        $aResultUrl = $aBaseParams;
        $aResultUrl['fFolderId'] = $oFolder->getId();
        $aResultUrl['action'] = 'new';
        $aOptions['result_url'] = KTUtil::addQueryString($_SERVER['PHP_SELF'], $aResultUrl);
        $collection->setOptions($aOptions);

        $aURLParams = $aBaseParams;
        $aURLParams['action'] = 'new';
        $aBreadcrumbs = KTUtil::generate_breadcrumbs($oFolder, $iFolderId, $aURLParams);


        // Add an electronic signature
    	global $default;
    	if($default->enableESignatures){
    	    $sUrl = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
    	    $heading = _kt('You are attempting to add a document link');
    	    $submit['type'] = 'button';
    	    $submit['onclick'] = "javascript: showSignatureForm('{$sUrl}', '{$heading}', 'ktcore.transactions.add_link', 'document', 'document_add_link_form', 'submit', {$oParentDocument->iId});";
    	}else{
    	    $submit['type'] = 'submit';
    	    $submit['onclick'] = '';
    	}


        $aTemplateData = array(
              'context' => $this,
              'folder' => $oFolder,
              'parent' => $oParentDocument,
              'breadcrumbs' => $aBreadcrumbs,
              'collection' => $collection,
              'link_types' => LinkType::getList("id > 0"),
              'submit' => $submit
        );

        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/link');
        return $oTemplate->render($aTemplateData);
    }

    function do_external() {
        $this->oPage->setBreadcrumbDetails(_kt("New External Link"));
        $this->oPage->setTitle(_kt("New External Link"));

        $oPermission =& KTPermission::getByName('ktcore.permissions.write');
        if (PEAR::isError($oPermission) ||
            !KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument)) {
            $this->errorRedirectToMain(_kt('You do not have sufficient permissions to add a document link'), sprintf("fDocumentId=%d", $this->oDocument->getId()));
            exit(0);
        }

        $oParentDocument =& $this->oDocument;
        $iParentId = $oParentDocument->getId();

        // Add an electronic signature
    	global $default;
    	if($default->enableESignatures){
    	    $sUrl = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
    	    $heading = _kt('You are attempting to add an external document link');
    	    $submit['type'] = 'button';
    	    $submit['onclick'] = "javascript: showSignatureForm('{$sUrl}', '{$heading}', 'ktcore.transactions.add_external_link', 'document', 'document_add_ext_link_form', 'submit', {$oParentDocument->iId});";
    	}else{
    	    $submit['type'] = 'submit';
    	    $submit['onclick'] = '';
    	}

        $aTemplateData = array(
              'context' => $this,
              'iDocId' => $iParentId,
              'link_types' => LinkType::getList("id > 0"),
              'submit' => $submit
        );

        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/link_external');
        return $oTemplate->render($aTemplateData);
    }

    // select a type for the link
    function do_type_select() {

        //Checking to see if the document is being linked to itself and returning an error if it is.
        $iTempParentDocId = $_REQUEST['fDocumentId'];
        $aTempDocuments = $_REQUEST['linkselection'];
        if(!empty($aTempDocuments)){
            foreach ($aTempDocuments as $iTempDocId)
            {
    	        if($iTempParentDocId == $iTempDocId)
    	        {
    	        	$this->errorRedirectToMain(_kt('A document cannot be linked to itself.'));
    	        }
            }
        }

        $this->oPage->setBreadcrumbDetails(_kt("link"));

        $sType = (isset($_REQUEST['linktype'])) ? $_REQUEST['linktype'] : 'internal';
        $sTarget = '';
        $aTarget = array();

        if($sType == 'external'){
            $iParentId = $_REQUEST['fDocumentId'];
            $aTarget['url'] = $_REQUEST['target_url'];
            $aTarget['name'] = $_REQUEST['target_name'];
            $aDocuments = array($iParentId);

            $this->oValidator->validateUrl($aTarget['url']);
            if(empty($aTarget['name'])){
                $aTarget['name'] = $aTarget['url'];
            }
        }else{
            $iParentId = $_REQUEST['fDocumentId'];
            $aDocuments = $_REQUEST['linkselection'];
            if(empty($aDocuments)){
                $this->errorRedirectToMain(_kt('No documents have been selected.'));
                exit;
            }
        }
        $sDocuments = serialize($aDocuments);
        $sTarget = serialize($aTarget);

        // form fields
        $aFields = array();

        $aVocab = array();
        $aLinkTypes = LinkType::getList("id > 0");
        foreach($aLinkTypes as $oLinkType) {
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
              'parent_id' => $iParentId,
              'target_id' => $sDocuments,
              'target_url' => $sTarget,
              'fields' => $aFields,
        );

        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/link_type_select');
        return $oTemplate->render($aTemplateData);
    }

    // make the link
    function do_make_link() {
        $this->oPage->setBreadcrumbDetails(_kt("link"));
        $iParentId = $_REQUEST['fDocumentId'];
        $iLinkTypeId = $_REQUEST['fLinkTypeId'];
        $sDocIds = $_REQUEST['fTargetDocumentId'];
        $aDocIds = unserialize($sDocIds);
        $sTarget = $_REQUEST['fTargetUrl'];
        $aTarget = unserialize($sTarget);

        $oLinkType = LinkType::get($iLinkTypeId);
        if (PEAR::isError($oLinkType)) {
            $this->errorRedirectToMain(_kt('Invalid link type selected.'));
            exit(0);
        }

        $sTargetUrl = '';
        $sTargetName = '';
        if(!empty($aTarget)){
            $sTargetUrl = $aTarget['url'];
            $sTargetName = $aTarget['name'];
        }

        // create document links
        $this->startTransaction();

        if(!empty($aDocIds)){
            foreach ($aDocIds as $iDocId){

                $oDocumentLink =& DocumentLink::createFromArray(array(
                    'iParentDocumentId' => $iParentId,
                    'iChildDocumentId'  => $iDocId,
                    'iLinkTypeId'       => $iLinkTypeId,
                    'sTargetUrl'       => $sTargetUrl,
                    'sTargetName'       => $sTargetName,
                ));

                if (PEAR::isError($oDocumentLink)) {
                    $this->rollbackTransaction();
                    $this->errorRedirectToMain(_kt('Could not create document link'), sprintf('fDocumentId=%d', $iParentId));
                    exit(0);
                }
            }
        }

        $this->commitTransaction();

        $this->successRedirectToMain(_kt('Document link created'), sprintf('fDocumentId=%d', $iParentId));
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
        // Cannot call delete directly if no link exists.
        if($oDocumentLink) {
            $res = $oDocumentLink->delete();
            if (PEAR::isError($res)) {
                $this->errorRedirectToMain(_kt('Could not delete document link'), sprintf('fDocumentId=%d', $oParentDocument->getId()));
                exit(0);
            }
        } else {
            $this->successRedirectToMain(_kt('Document link not deleted. Document link does not exists, or previously deleted.'), sprintf('fDocumentId=%d', $oParentDocument->getId()));
        }
        $this->commitTransaction();

        $this->successRedirectToMain(_kt('Document link deleted'), sprintf('fDocumentId=%d', $oParentDocument->getId()));
        exit(0);
    }

    function check() {
        $res = parent::check();
        if ($res !== true) {
            return $res;
        }

        return true;
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

            $aLinks = DocumentLink::getList(sprintf("link_type_id = %d", $link_id));
            if(!empty($aLinks)){
                foreach($aLinks as $oLink) {
                    $oLink->delete();
                }
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
