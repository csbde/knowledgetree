<?

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

require_once(KT_LIB_DIR . "/actions/folderaction.inc.php");
require_once(KT_LIB_DIR . "/import/zipimportstorage.inc.php");
require_once(KT_LIB_DIR . "/import/bulkimport.inc.php");

require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");

require_once(KT_LIB_DIR . "/validation/dispatchervalidation.inc.php");

class KTBulkUploadFolderAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.bulkUpload';

    var $_sShowPermission = "ktcore.permissions.write";
    var $bAutomaticTransaction = true;

    function getDisplayName() {
        return _('Bulk upload');
    }

    function check() {
        $res = parent::check();
        if (empty($res)) {
            return $res;
        }
        $postExpected = KTUtil::arrayGet($_REQUEST, "postExpected");
        $postReceived = KTUtil::arrayGet($_REQUEST, "postReceived");
        if (!empty($postExpected)) {
            $aErrorOptions = array(
                'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
                'message' => 'Upload larger than maximum POST size (max_post_size variable in .htaccess or php.ini)',
            );
            $this->oValidator->notEmpty($postReceived, $aErrorOptions);
        }
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_("bulk upload"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/folder/bulkUpload');
        $add_fields = array();
        $add_fields[] = new KTFileUploadWidget(_('Archive file'), _('The archive file containing the documents you wish to add to the document management system.'), 'file', "", $this->oPage, true);

        $aVocab = array('' => _('&lt;Please select a document type&gt;'));
        foreach (DocumentType::getList() as $oDocumentType) {
            if(!$oDocumentType->getDisabled()) {
                $aVocab[$oDocumentType->getId()] = $oDocumentType->getName();
            }
        }
        $fieldOptions = array("vocab" => $aVocab);
        $add_fields[] = new KTLookupWidget(_('Document Type'), _('Document Types, defined by the administrator, are used to categorise documents. Please select a Document Type from the list below.'), 'fDocumentTypeId', null, $this->oPage, true, "add-document-type", $fieldErrors, $fieldOptions);

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
        ));
        return $oTemplate->render();
    }

    function do_upload() {
        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );

        $aErrorOptions['message'] = _('Invalid document type provided');
        $oDocumentType = $this->oValidator->validateDocumentType($_REQUEST['fDocumentTypeId'], $aErrorOptions);

        unset($aErrorOptions['message']);
        $aFile = $this->oValidator->validateFile($_FILES['file'], $aErrorOptions);

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

        $fs =& new KTZipImportStorage($aFile['tmp_name']);
        $bm =& new KTBulkImportManager($this->oFolder, $fs, $this->oUser, $aOptions);
        $this->startTransaction();
        $res = $bm->import();
        $aErrorOptions['message'] = _("Bulk upload failed");
        $this->oValidator->notError($res, $aErrorOptions);

        $this->addInfoMessage("Bulk upload successful");
        $this->commitTransaction();
        controllerRedirect("browse", 'fFolderId=' . $this->oFolder->getID());
        exit(0);
    }
}
