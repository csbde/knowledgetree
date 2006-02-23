<?php

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

require_once(KT_LIB_DIR . '/roles/Role.inc');

class KTFolderAddFolderAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.addFolder';

    var $_sShowPermission = "ktcore.permissions.addFolder";

    function getDisplayName() {
        return _('Add a Folder');
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_("add folder"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/addFolder');
        $fields = array();
        $fields[] = new KTStringWidget(_('Folder name'), _('The name for the new folder.'), 'name', "", $this->oPage, true);

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
        $aErrorOptions['defaultmessage'] = _("No name given");
        $sFolderName = $this->oValidator->validateString($sFolderName, $aErrorOptions);

        $this->startTransaction();

        $res = KTFolderUtil::add($this->oFolder, $sFolderName, $this->oUser);
        $aErrorOptions['defaultmessage'] = _("Could not create folder in the document management system");
        $this->oValidator->notError($res, $aErrorOptions);

        $this->commitTransaction();
        controllerRedirect('browse', sprintf('fFolderId=%d', $res->getId()));
        exit(0);
    }
}

class KTFolderPermissionsAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.permissions';

    var $_sShowPermission = "ktcore.permissions.security";
    var $_bAdminAlwaysAvailable = true;
    var $bAutomaticTransaction = true;

    function getDisplayName() {
        return _('Permissions');
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_("viewing permissions"));
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/folder/permissions");
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aPermissions = KTPermission::getList();
        $aMapPermissionGroup = array();
        $aMapPermissionRole = array();
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
            $aIds = $oDescriptor->getRoles();
            $aMapPermissionRole[$iPermissionId] = array();
            foreach ($aIds as $iId) {
                $aMapPermissionRole[$iPermissionId][$iId] = true;
            }
        }
        
        $bEdit = true;
        $edit_mode = KTUtil::arrayGet($_REQUEST, 'edit_mode', false);
        if ($edit_mode == false) { $bEdit = false; }
        
        $oInherited = KTPermissionUtil::findRootObjectForPermissionObject($oPO);
        if ($oInherited === $this->oFolder) {
            ; // leave edit mode as per request.
        } else {
            $iInheritedFolderId = $oInherited->getId();
            $sInherited = join(" &raquo; ", $oInherited->getPathArray());

            // you cannot edit an inherited item.
            $bEdit = false;
        }

        $aDynamicConditions = KTPermissionDynamicCondition::getByPermissionObject($oPO);
        $aTemplateData = array(
            "permissions" => $aPermissions,
            "groups" => Group::getList(),
            "roles" => Role::getList(),
            "iFolderId" => $this->oFolder->getId(),
            "aMapPermissionGroup" => $aMapPermissionGroup,
            "aMapPermissionRole" => $aMapPermissionRole,
            "edit" => $bEdit,
            "inherited" => $sInherited,
            "conditions" => KTSavedSearch::getConditions(),
            "dynamic_conditions" => $aDynamicConditions,
            'context' => &$this,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_update() {
        require_once(KT_LIB_DIR . "/documentmanagement/observers.inc.php");
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aFoo = $_REQUEST['foo'];
        $aPermissions = KTPermission::getList();
        foreach ($aPermissions as $oPermission) {
            $iPermId = $oPermission->getId();
            $aAllowed = KTUtil::arrayGet($aFoo, $iPermId, array());
            KTPermissionUtil::setPermissionForId($oPermission, $oPO, $aAllowed);
        }

        $po =& new JavascriptObserver($this);
        $po->start();
        $oChannel =& KTPermissionChannel::getSingleton();
        $oChannel->addObserver($po);

        KTPermissionUtil::updatePermissionLookupForPO($oPO);

        $this->commitTransaction();

        $po->redirect(KTUtil::addQueryString($_SERVER['PHP_SELF'], "fFolderId=" . $this->oFolder->getId()));
    }

    function do_copyPermissions() {
        KTPermissionUtil::copyPermissionObject($this->oFolder);
        return $this->successRedirectToMain(_('Permissions updated'),
                array('fFolderId' => $this->oFolder->getId()));
    }

    function do_inheritPermissions() {
        KTPermissionUtil::inheritPermissionObject($this->oFolder);
        return $this->successRedirectToMain(_('Permissions updated'),
                array('fFolderId' => $this->oFolder->getId()));
    }

    function do_newDynamicPermission() {
        $aOptions = array(
            'redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()),
        );
        $oGroup =& $this->oValidator->validateGroup($_REQUEST['fGroupId'], $aOptions);
        $oCondition =& $this->oValidator->validateCondition($_REQUEST['fConditionId'], $aOptions);
        $aPermissionIds = $_REQUEST['fPermissionIds'];
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());

        $oDynamicCondition = KTPermissionDynamicCondition::createFromArray(array(
            'groupid' => $oGroup->getId(),
            'conditionid' => $oCondition->getId(),
            'permissionobjectid' => $oPO->getId(),
        ));
        $this->oValidator->notError($oDynamicCondition, $aOptions);
        $res = $oDynamicCondition->saveAssignment($aPermissionIds);
        $this->oValidator->notError($res, $aOptions);
        KTPermissionUtil::updatePermissionLookupForPO($oPO);
        $this->successRedirectToMain(_("Dynamic permission added"), "fFolderId=" . $this->oFolder->getId());
    }

    function do_removeDynamicCondition() {
        $aOptions = array(
            'redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()),
        );
        $oDynamicCondition =& $this->oValidator->validateDynamicCondition($_REQUEST['fDynamicConditionId'], $aOptions);
        $res = $oDynamicCondition->delete();
        $this->oValidator->notError($res, $aOptions);
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        KTPermissionUtil::updatePermissionLookupForPO($oPO);
        $this->successRedirectToMain(_("Dynamic permission removed"), "fFolderId=" . $this->oFolder->getId());
    }
}

?>
