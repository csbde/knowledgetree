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

require_once(KT_LIB_DIR . "/actions/folderaction.inc.php");
require_once(KT_LIB_DIR . "/import/fsimportstorage.inc.php");
require_once(KT_LIB_DIR . "/import/bulkimport.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/observers.inc.php");


require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");

require_once(KT_LIB_DIR . "/validation/dispatchervalidation.inc.php");

class KTBulkImportFolderAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.bulkImport';

    var $_sShowPermission = "ktcore.permissions.write";
    var $bAutomaticTransaction = false;

    function getDisplayName() {
        return _kt('Import from Server Location');
    }

    function getInfo() {
        global $default;
        if($default->disableBulkImport){
        	return null;
        }

        if (!Permission::userIsSystemAdministrator($this->oUser->getId())) {
            return null;

        }
        return parent::getInfo();
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("bulk import"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/folder/bulkImport');
        $add_fields = array();
        $add_fields[] = new KTStringWidget(_kt('Path'), _kt('The path containing the documents to be added to the document management system.'), 'path', "", $this->oPage, true);

        $aVocab = array('' => _kt('- Please select a document type -'));
        foreach (DocumentType::getListForUserAndFolder($this->oUser, $this->oFolder) as $oDocumentType) {
            if(!$oDocumentType->getDisabled()) {
                $aVocab[$oDocumentType->getId()] = $oDocumentType->getName();
            }
        }

        $fieldOptions = array("vocab" => $aVocab);
        $add_fields[] = new KTLookupWidget(_kt('Document Type'), _kt('Document Types, defined by the administrator, are used to categorise documents. Please select a Document Type from the list below.'), 'fDocumentTypeId', null, $this->oPage, true, "add-document-type", $fieldErrors, $fieldOptions);

        $fieldsets = array();
        $fieldsetDisplayReg =& KTFieldsetDisplayRegistry::getSingleton();
        $activesets = KTFieldset::getGenericFieldsets();
        foreach ($activesets as $oFieldset) {
            $displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
            array_push($fieldsets, new $displayClass($oFieldset));
        }

        // Implement an electronic signature for accessing the admin section, it will appear every 10 minutes
        global $default;
        $iFolderId = $this->oFolder->getId();
        if($default->enableESignatures){
            $sUrl = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
            $heading = _kt('You are attempting to perform a bulk import');
            $submit['type'] = 'button';
            $submit['onclick'] = "javascript: showSignatureForm('{$sUrl}', '{$heading}', 'ktcore.transactions.bulk_import', 'bulk', 'bulk_import_form', 'submit', {$iFolderId});";
        }else{
            $submit['type'] = 'submit';
            $submit['onclick'] = '';
        }

        $oTemplate->setData(array(
            'context' => &$this,
            'submit' => $submit,
            'add_fields' => $add_fields,
            'generic_fieldsets' => $fieldsets,
        ));
        return $oTemplate->render();
    }

    function do_import() {

        set_time_limit(0);

        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );

        $aErrorOptions['message'] = _kt('Invalid document type provided');
        $oDocumentType = $this->oValidator->validateDocumentType($_REQUEST['fDocumentTypeId'], $aErrorOptions);

        $aErrorOptions['message'] = _kt('Invalid path provided');
        $sPath = $this->oValidator->validateString($_REQUEST['path'], $aErrorOptions);

        $matches = array();
        $aFields = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('/^metadata_(\d+)$/', $k, $matches)) {
                $aFields[] = array(DocumentField::get($matches[1]), $v);
            }
        }

        $aOptions = array(
            'documenttype' => $oDocumentType,
            'metadata' => $aFields,
            'copy_upload' => 'true',
        );

        $po =& new JavascriptObserver($this);
        $po->start();
        $oUploadChannel =& KTUploadChannel::getSingleton();
        $oUploadChannel->addObserver($po);

        $fs =& new KTFSImportStorage($sPath);
        $bm =& new KTBulkImportManager($this->oFolder, $fs, $this->oUser, $aOptions);
        DBUtil::startTransaction();
        $res = $bm->import();
        if (PEAR::isError($res)) {
            DBUtil::rollback();
            $_SESSION["KTErrorMessage"][] = _kt("Bulk import failed") . ": " . $res->getMessage();
        } else {
            DBUtil::commit();
            $this->addInfoMessage(_kt("Bulk import succeeded"));
        }

        $po->redirectToFolder($this->oFolder->getId());
        exit(0);
    }
}
