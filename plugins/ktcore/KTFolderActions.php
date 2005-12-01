<?php

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');

require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

$oKTActionRegistry =& KTActionRegistry::getSingleton();

class KTFolderAddDocumentAction extends KTFolderAction {
    var $sDisplayName = 'Add Document';
    var $sName = 'ktcore.actions.folder.addDocument';

    var $_sShowPermission = "ktcore.permissions.write";

    function do_main() {
        $this->oPage->setBreadcrumbDetails("add document");
        $this->oPage->setTitle('Add a document');
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/document/add');
        $add_fields = array();
        $add_fields[] = new KTFileUploadWidget('File', 'The contents of the document to be added to the document management system.', 'file', "", $this->oPage, true);
        $add_fields[] = new KTStringWidget('Title', 'Describe the changes made to the document.', 'title', "", $this->oPage, true);
        
        
        
        $aVocab = array();
        foreach (DocumentType::getList() as $oDocumentType) {
            $aVocab[$oDocumentType->getId()] = $oDocumentType->getName();
        }
        $fieldOptions = array("vocab" => $aVocab);
        $add_fields[] = new KTLookupWidget('Document Type', 'FIXME', 'fDocumentTypeId', null, $this->oPage, true, "add-document-type", $fieldErrors, $fieldOptions);

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
        require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');
        require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
        require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
        require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
        require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

        // make sure the user actually selected a file first
        // and that something was uploaded
        if (!((strlen($_FILES['file']['name']) > 0) && $_FILES['file']['size'] > 0)) {
            // no uploaded file
            $message = _("You did not select a valid document to upload");

            $errors = array(
               1 => _("The uploaded file is larger than the PHP upload_max_filesize setting"),
               2 => _("The uploaded file is larger than the MAX_FILE_SIZE directive that was specified in the HTML form"),
               3 => _("The uploaded file was not fully uploaded to KnowledgeTree"),
               4 => _("No file was selected to be uploaded to KnowledgeTree"),
               6 => _("An internal error occurred receiving the uploaded document"),
            );
            $message = KTUtil::arrayGet($errors, $_FILES['file']['error'], $message);

            if (@ini_get("file_uploads") == false) {
                $message = _("File uploads are disabled in your PHP configuration");
            }

            $this->errorRedirectToMain($message, 'fFolderId=' . $this->oFolder->getId());
            exit(0);
        }

        $matches = array();
        $aFields = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('/^metadata_(\d+)$/', $k, $matches)) {
                $aFields[] = array(DocumentField::get($matches[1]), $v);
            }
        }

        $this->oValidator->validateDocumentType($_REQUEST['fDocumentTypeId']);

        $aOptions = array(
            'contents' => new KTFSFileLike($_FILES['file']['tmp_name']),
            'documenttype' => $this->oDocumentType,
            'metadata' => $aFields,
            'description' => $_REQUEST['fName'],
        );

        $this->startTransaction();
        $oDocument =& KTDocumentUtil::add($this->oFolder, basename($_FILES['file']['name']), $this->oUser, $aOptions);
        if (PEAR::isError($oDocument)) {
            $message = $oDocument->getMessage();
            $this->errorRedirectToMain($message, 'fFolderId=' . $this->oFolder->getId());
            exit(0);
        }

        $this->commitTransaction();
        //redirect to the document details page
        controllerRedirect("viewDocument", "fDocumentId=" . $oDocument->getID());
    }

}
$oKTActionRegistry->registerAction('folderaction', 'KTFolderAddDocumentAction', 'ktcore.actions.folder.addDocument');

class KTFolderAddFolderAction extends KTFolderAction {
    var $sDisplayName = 'Add a Folder';
    var $sName = 'ktcore.actions.folder.addFolder';

    var $_sShowPermission = "ktcore.permissions.write";

    function do_main() {
        $this->oPage->setBreadcrumbDetails("add document");
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/addFolder');
        $fields = array();
        $fields[] = new KTStringWidget('Folder name', '', 'name', "", $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'fields' => $fields,
        ));
        return $oTemplate->render();
    }

    function do_addFolder() {
        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );
        $sFolderName = KTUtil::arrayGet($_REQUEST, 'name');
        $aErrorOptions['defaultmessage'] = "No name given";
        $sFolderName = $this->oValidator->validateString($sFolderName, $aErrorOptions);

        $this->startTransaction();

        $res = KTFolderUtil::add($this->oFolder, $sFolderName, $this->oUser);
        $aErrorOptions['defaultmessage'] = "Could not create folder in the document management system";
        $this->oValidator->notError($res, $aErrorOptions);

        $this->commitTransaction();
        controllerRedirect('browse', sprintf('fFolderId=%d', $this->oFolder->getId()));
        exit(0);
    }
}
$oKTActionRegistry->registerAction('folderaction', 'KTFolderAddFolderAction', 'ktcore.actions.folder.addFolder');

class KTFolderPermissionsAction extends KTFolderAction {
    var $sDisplayName = 'Permissions';
    var $sName = 'ktcore.actions.folder.permissions';

    var $_sShowPermission = "ktcore.permissions.write";
    var $bAutomaticTransaction = true;

    function do_main() {
        $this->oPage->setBreadcrumbDetails("viewing permissions");
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/folder/permissions");
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aPermissions = KTPermission::getList();
        $aMapPermissionGroup = array();
        foreach ($aPermissions as $oPermission) {
            $oPA = KTPermissionAssignment::getByPermissionAndObject($oPermission, $oPO);
            if (PEAR::isError($oPA)) {
                continue;
            }
            $oDescriptor = KTPermissionDescriptor::get($oPA->getPermissionDescriptorId());
            $iPermissionId = $oPermission->getId();
            $aIds = $oDescriptor->getGroups();
            $aMapPermissionGroup[$iPermissionId] = array();
            foreach ($aIds as $iId) {
                $aMapPermissionGroup[$iPermissionId][$iId] = true;
            }
        }
        $aMapPermissionUser = array();
        $aUsers = User::getList();
        foreach ($aPermissions as $oPermission) {
            $iPermissionId = $oPermission->getId();
            foreach ($aUsers as $oUser) {
                if (KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $this->oFolder)) {
                    $aMapPermissionUser[$iPermissionId][$oUser->getId()] = true;
                }
            }
        }

        $oInherited = KTPermissionUtil::findRootObjectForPermissionObject($oPO);
        if ($oInherited === $this->oFolder) {
            $bEdit = true;
        } else {
            $iInheritedFolderId = $oInherited->getId();
            $sInherited = join(" &raquo; ", $oInherited->getPathArray());

            $bEdit = false;
        }

        $aDynamicConditions = KTPermissionDynamicCondition::getByPermissionObject($oPO);
        $aTemplateData = array(
            "permissions" => $aPermissions,
            "groups" => Group::getList(),
            "iFolderId" => $this->oFolder->getId(),
            "aMapPermissionGroup" => $aMapPermissionGroup,
            "users" => $aUsers,
            "aMapPermissionUser" => $aMapPermissionUser,
            "edit" => $bEdit,
            "inherited" => $sInherited,
            "conditions" => KTSavedSearch::getConditions(),
            "dynamic_conditions" => $aDynamicConditions,
            'context' => &$this,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_update() {
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aFoo = $_REQUEST['foo'];
        $aPermissions = KTPermission::getList();
        foreach ($aPermissions as $oPermission) {
            $iPermId = $oPermission->getId();
            $aAllowed = KTUtil::arrayGet($aFoo, $iPermId, array());
            KTPermissionUtil::setPermissionForId($oPermission, $oPO, $aAllowed);
        }
        KTPermissionUtil::updatePermissionLookupForPO($oPO);
        return $this->successRedirectToMain('Permissions updated',
                array('fFolderId' => $this->oFolder->getId()));
    }

    function do_copyPermissions() {
        KTPermissionUtil::copyPermissionObject($this->oFolder);
        return $this->successRedirectToMain('Permissions updated',
                array('fFolderId' => $this->oFolder->getId()));
    }

    function do_inheritPermissions() {
        KTPermissionUtil::inheritPermissionObject($this->oFolder);
        return $this->successRedirectToMain('Permissions updated',
                array('fFolderId' => $this->oFolder->getId()));
    }

    function do_newDynamicPermission() {
        $oGroup =& $this->oValidator->validateGroup($_REQUEST['fGroupId']);
        $oCondition =& $this->oValidator->validateCondition($_REQUEST['fConditionId']);
        $aPermissionIds = $_REQUEST['fPermissionIds'];
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());

        $oDynamicCondition = KTPermissionDynamicCondition::createFromArray(array(
            'groupid' => $oGroup->getId(),
            'conditionid' => $oCondition->getId(),
            'permissionobjectid' => $oPO->getId(),
        ));
        $aOptions = array(
            'redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()),
        );
        $this->oValidator->notError($oDynamicCondition, $aOptions);
        $res = $oDynamicCondition->saveAssignment($aPermissionIds);
        $this->oValidator->notError($res, $aOptions);
        $this->successRedirectToMain("Dynamic permission added", "fFolderId=" . $this->oFolder->getId());
    }
}
$oKTActionRegistry->registerAction('folderaction', 'KTFolderPermissionsAction', 'ktcore.actions.folder.permissions');

$oRegistry =& KTPluginRegistry::getSingleton();
$oPlugin =& $oRegistry->getPlugin('ktcore.plugin');
$oPlugin->registerAction('folderaction', 'KTBulkImportFolderAction', 'ktcore.actions.folder.bulkImport', 'folder/BulkImport.php');
$oPlugin->registerAction('folderaction', 'KTBulkUploadFolderAction', 'ktcore.actions.folder.bulkUpload', 'folder/BulkUpload.php');
$oPlugin->register();

?>
