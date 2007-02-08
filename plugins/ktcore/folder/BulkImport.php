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
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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
    var $bAutomaticTransaction = true;

    function getDisplayName() {
        return _kt('Import from Server Location');
    }

    function getInfo() {
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

        $aVocab = array('' => _kt('&lt;Please select a document type&gt;'));
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

        $oTemplate->setData(array(
            'context' => &$this,
            'add_fields' => $add_fields,
            'generic_fieldsets' => $fieldsets,
            'foldername' => $this->oFolder->getName(),
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
