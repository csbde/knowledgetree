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
    var $sDisplayName = 'Add a Folder';
    var $sName = 'ktcore.actions.folder.addFolder';

    var $_sShowPermission = "ktcore.permissions.write";

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
        controllerRedirect('browse', sprintf('fFolderId=%d', $this->oFolder->getId()));
        exit(0);
    }
}

class KTFolderPermissionsAction extends KTFolderAction {
    var $sDisplayName = 'Permissions';
    var $sName = 'ktcore.actions.folder.permissions';

    var $_sShowPermission = "ktcore.permissions.write";
    var $_adminAlwaysAvailable = true;
    var $bAutomaticTransaction = true;

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_("viewing permissions"));
        $oTemplating = new KTTemplating;
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
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aFoo = $_REQUEST['foo'];
        $aPermissions = KTPermission::getList();
        foreach ($aPermissions as $oPermission) {
            $iPermId = $oPermission->getId();
            $aAllowed = KTUtil::arrayGet($aFoo, $iPermId, array());
            KTPermissionUtil::setPermissionForId($oPermission, $oPO, $aAllowed);
        }
        KTPermissionUtil::updatePermissionLookupForPO($oPO);
        return $this->successRedirectToMain(_('Permissions updated'),
                array('fFolderId' => $this->oFolder->getId()));
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
        $this->successRedirectToMain(_("Dynamic permission added"), "fFolderId=" . $this->oFolder->getId());
    }
}

?>
