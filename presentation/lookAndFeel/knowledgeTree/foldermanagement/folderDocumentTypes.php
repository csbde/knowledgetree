<?php

require_once('../../../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/foldermanagement/FolderDocTypeLink.inc');

class KTFolderDocumentTypeDispatcher extends KTStandardDispatcher {
    function check () {
        if (empty($_REQUEST['fFolderId'])) {
            $this->permissionDenied();
            exit(0);
        }
        $oPermission = KTPermission::getByName('ktcore.permissions.write');
        $this->oFolder =& Folder::get($_REQUEST['fFolderId']);
        $oUser =& User::get($_SESSION['userID']);
        if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $this->oFolder)) {
            $this->permissionDenied();
            exit(0);
        }
        return true;
    }

    function do_main() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/documenttypes/folderassign');

        $sTable = KTUtil::getTableName('folder_doctypes');
        $aQuery = array(
            "SELECT document_type_id FROM $sTable WHERE folder_id = ?",
            array($this->oFolder->getId()),
        );
        $aSelectedIds = DBUtil::getResultArrayKey($aQuery, 'document_type_id');

        $oTemplate->setData(array(
            'oFolder' => $this->oFolder,
            'document_types' => DocumentType::getList(),
            'selected_types' => $aSelectedIds,
        ));
        return $oTemplate;
    }

    function do_assign() {
        if (empty($_REQUEST['restricted'])) {
            $this->oFolder->setRestrictDocumentTypes(false);
        } else {
            $this->oFolder->setRestrictDocumentTypes(true);
        }

        $sTable = KTUtil::getTableName('folder_doctypes');
        $res = DBUtil::runQuery(array(
            "DELETE FROM $sTable WHERE folder_id = ?",
            array($this->oFolder->getId()),
        ));
        foreach ($_REQUEST['document_types'] as $iDocumentTypeId) {
            $oLink = new FolderDocTypeLink($this->oFolder->getId(), $iDocumentTypeId);
            $oLink->create();
        }
        $this->oFolder->update();
        $this->errorRedirectToMain(_('Changes made'), 'fFolderId=' .
                $this->oFolder->getId());
        exit(0);
    }
}

$d =& new KTFolderDocumentTypeDispatcher;
$d->dispatch();

?>
