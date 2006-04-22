<?php

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');

require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");

require_once(KT_LIB_DIR . '/roles/Role.inc');

class KTFolderPermissionsAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.permissions';

    var $_sEditShowPermission = "ktcore.permissions.security";
    var $_bAdminAlwaysAvailable = true;
    var $bAutomaticTransaction = true;

    function getDisplayName() {
        return _kt('Permissions');
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_("Permissions"));
        $oTemplate = $this->oValidator->validateTemplate("ktcore/folder/view_permissions");

        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectID());
        $aPermissions = KTPermission::getList();
        $aMapPermissionGroup = array();
        $aMapPermissionRole = array();
        $aMapPermissionUser = array();

        $aAllGroups = Group::getList();   // probably small enough
        $aAllRoles = Role::getList();     // probably small enough.
        // users are _not_ fetched this way.

        $aActiveGroups = array();
        $aActiveUsers = array();
        $aActiveRoles = array();

        foreach ($aPermissions as $oPermission) {
            $oPLA = KTPermissionAssignment::getByPermissionAndObject($oPermission, $oPO);
            if (PEAR::isError($oPLA)) {
                continue;
            }
            $oDescriptor = KTPermissionDescriptor::get($oPLA->getPermissionDescriptorID());
            $iPermissionID = $oPermission->getID();
            $aIDs = $oDescriptor->getGroups();
            $aMapPermissionGroup[$iPermissionID] = array();
            foreach ($aIDs as $iID) {
                $aMapPermissionGroup[$iPermissionID][$iID] = true;
                $aActiveGroups[$iID] = true;
            }
            $aIds = $oDescriptor->getRoles();
            $aMapPermissionRole[$iPermissionID] = array();
            foreach ($aIds as $iId) {
                $aMapPermissionRole[$iPermissionID][$iId] = true;
                $aActiveRoles[$iId] = true;
            }
            $aIds = $oDescriptor->getUsers();
            $aMapPermissionUser[$iPermissionID] = array();
            foreach ($aIds as $iId) {
                $aMapPermissionUser[$iPermissionID][$iId] = true;
                $aActiveUsers[$iId] = true;
            }
        }

        // now we constitute the actual sets.
        $users = array();
        $groups = array();
        $roles = array(); // should _always_ be empty, barring a bug in permissions::updatePermissionLookup

        // this should be quite limited - direct role -> user assignment is typically rare.
        foreach ($aActiveUsers as $id => $marker) {
            $oUser = User::get($id);
            $users[$oUser->getName()] = $oUser;
        }
        asort($users); // ascending, per convention.

        foreach ($aActiveGroups as $id => $marker) {
            $oGroup = Group::get($id);
            $groups[$oGroup->getName()] = $oGroup;
        }
        asort($groups);

        foreach ($aActiveRoles as $id => $marker) {
            $oRole = Role::get($id);
            $roles[$oRole->getName()] = $oRole;
        }
        asort($roles);

        $bEdit = KTPermissionUtil::userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder);

        $sInherited = '';
        $oInherited = KTPermissionUtil::findRootObjectForPermissionObject($oPO);
        // This is fine, since a folder can only inherit permissions
        // from a folder.
        if ($oInherited->getId() !== $this->oFolder->getId()) {
            $iInheritedFolderId = $oInherited->getId();
            $sInherited = join(" &raquo; ", $oInherited->getPathArray());
        }

        $aTemplateData = array(
            "context" => $this,
            "permissions" => $aPermissions,
            "groups" => $groups,
            "users" => $users,
            "roles" => $roles,
            "oFolder" => $this->oFolder,
            "aMapPermissionGroup" => $aMapPermissionGroup,
            "aMapPermissionRole" => $aMapPermissionRole,
            "aMapPermissionUser" => $aMapPermissionUser,
            "edit" => $bEdit,
            "inherited" => $sInherited,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_resolved_users() {
        $this->oPage->setBreadcrumbDetails(_("Permissions"));
        $oTemplate = $this->oValidator->validateTemplate("ktcore/folder/resolved_permissions_user");

        $oPL = KTPermissionLookup::get($this->oFolder->getPermissionLookupID());
        $aPermissions = KTPermission::getList();
        $aMapPermissionGroup = array();
        $aMapPermissionRole = array();
        $aMapPermissionUser = array();

        $aUsers = User::getList();

        foreach ($aPermissions as $oPermission) {
            $oPLA = KTPermissionLookupAssignment::getByPermissionAndLookup($oPermission, $oPL);
            if (PEAR::isError($oPLA)) {
                continue;
            }
            $oDescriptor = KTPermissionDescriptor::get($oPLA->getPermissionDescriptorID());
            $iPermissionID = $oPermission->getID();
            $aMapPermissionGroup[$iPermissionID] = array();
            foreach ($aUsers as $oUser) {
                if (KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $this->oFolder)) {
                    $aMapPermissionUser[$iPermissionID][$oUser->getId()] = true;
                    $aActiveUsers[$oUser->getId()] = true;
                }
            }
        }

        // now we constitute the actual sets.
        $users = array();
        $groups = array();
        $roles = array(); // should _always_ be empty, barring a bug in permissions::updatePermissionLookup

        // this should be quite limited - direct role -> user assignment is typically rare.
        foreach ($aActiveUsers as $id => $marker) {
            $oUser = User::get($id);
            $users[$oUser->getName()] = $oUser;
        }
        asort($users); // ascending, per convention.

        $bEdit = false;
        $sInherited = '';

        $aTemplateData = array(
            "context" => $this,
            "permissions" => $aPermissions,
            "groups" => $groups,
            "users" => $users,
            "roles" => $roles,
            "oFolder" => $this->oFolder,
            "aMapPermissionGroup" => $aMapPermissionGroup,
            "aMapPermissionRole" => $aMapPermissionRole,
            "aMapPermissionUser" => $aMapPermissionUser,
            "edit" => $bEdit,
            "inherited" => $sInherited,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_edit() {
        $this->oPage->setBreadcrumbDetails(_kt("viewing permissions"));
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/folder/permissions");
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aOptions = array('redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()));
        $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);

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

        $oInherited = KTPermissionUtil::findRootObjectForPermissionObject($oPO);
        // This is fine, since a folder can only inherit permissions
        // from a folder.
        if ($oInherited->getId() === $this->oFolder->getId()) {
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

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => "Updated permissions",
            'transactionNS' => 'ktcore.transactions.permissions_change',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
        ));
        $aOptions = array(
            'defaultmessage' => _kt('Error updating permissions'),
            'redirect_to' => array('edit', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );
        $this->oValidator->notErrorFalse($oTransaction, $aOptions);

        $po =& new JavascriptObserver($this);
        $po->start();
        $oChannel =& KTPermissionChannel::getSingleton();
        $oChannel->addObserver($po);

        KTPermissionUtil::updatePermissionLookupForPO($oPO);

        $this->commitTransaction();

        $po->redirect(KTUtil::addQueryString($_SERVER['PHP_SELF'], "action=edit&fFolderId=" . $this->oFolder->getId()));
    }

    function do_copyPermissions() {
        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => "Override permissions from parent",
            'transactionNS' => 'ktcore.transactions.permissions_change',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
        ));
        $aOptions = array(
            'defaultmessage' => _kt('Error updating permissions'),
            'redirect_to' => array('edit', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );
        $this->oValidator->notErrorFalse($oTransaction, $aOptions);

        KTPermissionUtil::copyPermissionObject($this->oFolder);
        return $this->successRedirectTo('edit', _kt('Permissions updated'),
                array('fFolderId' => $this->oFolder->getId()));
    }

    function do_inheritPermissions() {
        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => "Inherit permissions from parent",
            'transactionNS' => 'ktcore.transactions.permissions_change',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
        ));
        $aOptions = array(
            'defaultmessage' => _kt('Error updating permissions'),
            'redirect_to' => array('edit', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );
        $this->oValidator->notErrorFalse($oTransaction, $aOptions);

        KTPermissionUtil::inheritPermissionObject($this->oFolder);
        return $this->successRedirectTo('edit', _kt('Permissions updated'),
                array('fFolderId' => $this->oFolder->getId()));
    }

    function do_newDynamicPermission() {
        $aOptions = array(
            'redirect_to' => array('edit', 'fFolderId=' .  $this->oFolder->getId()),
        );
        $oGroup =& $this->oValidator->validateGroup($_REQUEST['fGroupId'], $aOptions);
        $oCondition =& $this->oValidator->validateCondition($_REQUEST['fConditionId'], $aOptions);
        $aPermissionIds = (array) $_REQUEST['fPermissionIds'];
        if (empty($aPermissionIds)) { $this->errorRedirectTo('edit', _kt('Please select one or more permissions.'), sprintf('fFolderId=%d', $this->oFolder->getId())); }
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => "Added dynamic permissions",
            'transactionNS' => 'ktcore.transactions.permissions_change',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
        ));
        $aOptions = array(
            'defaultmessage' => _kt('Error updating permissions'),
            'redirect_to' => array('edit', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );
        $this->oValidator->notErrorFalse($oTransaction, $aOptions);

        $oDynamicCondition = KTPermissionDynamicCondition::createFromArray(array(
            'groupid' => $oGroup->getId(),
            'conditionid' => $oCondition->getId(),
            'permissionobjectid' => $oPO->getId(),
        ));
        $this->oValidator->notError($oDynamicCondition, $aOptions);
        $res = $oDynamicCondition->saveAssignment($aPermissionIds);
        $this->oValidator->notError($res, $aOptions);
        KTPermissionUtil::updatePermissionLookupForPO($oPO);
        $this->successRedirectTo('edit', _kt("Dynamic permission added"), "fFolderId=" . $this->oFolder->getId());
    }

    function do_removeDynamicCondition() {
        $aOptions = array(
            'redirect_to' => array('edit', 'fFolderId=' .  $this->oFolder->getId()),
        );
        $oDynamicCondition =& $this->oValidator->validateDynamicCondition($_REQUEST['fDynamicConditionId'], $aOptions);
        $res = $oDynamicCondition->delete();
        $this->oValidator->notError($res, $aOptions);

        $oTransaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $this->oFolder->getId(),
            'comment' => "Removed dynamic permissions",
            'transactionNS' => 'ktcore.transactions.permissions_change',
            'userid' => $_SESSION['userID'],
            'ip' => Session::getClientIP(),
        ));
        $aOptions = array(
            'defaultmessage' => _kt('Error updating permissions'),
            'redirect_to' => array('edit', sprintf('fFolderId=%d', $this->oFolder->getId())),
        );
        $this->oValidator->notErrorFalse($oTransaction, $aOptions);

        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        KTPermissionUtil::updatePermissionLookupForPO($oPO);
        $this->successRedirectTo('edit', _kt("Dynamic permission removed"), "fFolderId=" . $this->oFolder->getId());
    }
}

?>
