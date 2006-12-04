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
    var $_sShowPermission = "ktcore.permissions.security";
    var $_bAdminAlwaysAvailable = true;
    var $bAutomaticTransaction = true;

    function getDisplayName() {
        return _kt('Permissions');
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("Permissions"));
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
        if (KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $bEdit = true;
        }

        $sInherited = '';
        $oInherited = KTPermissionUtil::findRootObjectForPermissionObject($oPO);
        // This is fine, since a folder can only inherit permissions
        // from a folder.
        if ($oInherited->getId() !== $this->oFolder->getId()) {
            $iInheritedFolderId = $oInherited->getId();
            $sInherited = join(" &raquo; ", $oInherited->getPathArray());
        }
        // only allow inheritance if not inherited, -and- folders is editable
        $bInheritable = $bEdit && ($oInherited->getId() !== $this->oFolder->getId());        
        // only allow edit if the folder is editable.
        $bEdit = $bEdit && ($oInherited->getId() == $this->oFolder->getId());
        
        $aConditions = array();
        $aDynConditions = KTPermissionDynamicCondition::getByPermissionObject($oPO);
        
        foreach ($aDynConditions as $oDynCondition) {
            $g = Group::get($oDynCondition->getGroupId());

            if (PEAR::isError($g)) { continue; }
            $c = KTSavedSearch::get($oDynCondition->getConditionId());            
            if (PEAR::isError($c)) { continue; }
            
            $aInfo = array(
                'group' => $g->getName(),
                'name' => $c->getName(),
            );
            $aAssign = $oDynCondition->getAssignment();
            $perms = array();
            foreach ($aAssign as $iPermissionId) {
                $perms[$iPermissionId] = true;
            }
            $aInfo['perms'] = $perms;
            $aConditions[] = $aInfo;
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
            'inheritable' => $bInheritable,
            "inherited" => $sInherited,
            'foldername' => $this->oFolder->getName(),
            'conditions' => $aConditions,             
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_resolved_users() {
        $this->oPage->setBreadcrumbDetails(_kt("Permissions"));
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
            'foldername' => $this->oFolder->getName(),     
            "iFolderId" => $this->oFolder->getId(),                   
        );
        return $oTemplate->render($aTemplateData);
    }












    function _copyPermissions() {
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
    }


    function do_edit() {
        $this->oPage->setBreadcrumbDetails(_kt("Viewing Permissions"));


        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
	$aOptions = array('redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()));

        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);
        }

    	// copy permissions if they were inherited
        $oInherited = KTPermissionUtil::findRootObjectForPermissionObject($oPO);
        if ($oInherited->getId() !== $this->oFolder->getId()) {
            $override = KTUtil::arrayGet($_REQUEST, 'override', false);
            if (empty($override)) { 
                $this->errorRedirectToMain(_kt("This folder does not override its permissions"), sprintf("fFolderId=%d", $this->oFolder->getId()));
            }
            $this->startTransaction();
    	    $this->_copyPermissions();
            $this->commitTransaction();
            $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());           
        }


	// permissions in JS format
	$aPermissionsToJSON = array();
	foreach(KTPermission::getList() as $oP) {
	    $aPermissionsToJSON[] = array('id'=>$oP->getId(), 'name'=>$oP->getHumanName());
	}

	$oJSON = new Services_JSON;
	$sJSONPermissions = $oJSON->encode($aPermissionsToJSON);

	// dynamic conditions
        $aDynamicConditions = KTPermissionDynamicCondition::getByPermissionObject($oPO);

	// templating
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/folder/permissions");

        $bCanInherit = ($this->oFolder->getId() != 1);

        $perms = KTPermission::getList();
        $docperms = KTPermission::getDocumentRelevantList();
        
        $aTemplateData = array(			       
            "iFolderId" => $this->oFolder->getId(),
	        'roles' => Role::getList(),
	        'groups' => Group::getList(),
            "conditions" => KTSavedSearch::getConditions(),
            "dynamic_conditions" => $aDynamicConditions,
            'context' => &$this,
            'foldername' => $this->oFolder->getName(),          
	        'jsonpermissions' => $sJSONPermissions,
	        'edit' => true,
	        'permissions' => $perms,
	        'document_permissions' => $docperms,
	        'can_inherit' => $bCanInherit,
        );
        return $oTemplate->render($aTemplateData);
    }


    function json_permissionError() {
	return array('error' => true,
		     'type' => 'kt.permission_denied',
		     'alert' => true,
		     'message' => _kt("You do not have permission to alter security settings."));
    }

    function &_getPermissionsMap() {
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aPermissions = KTPermission::getList();
        $aPermissionsMap = array('role'=>array(), 'group'=>array());

        foreach ($aPermissions as $oPermission) {
            $oPA = KTPermissionAssignment::getByPermissionAndObject($oPermission, $oPO);
            if (PEAR::isError($oPA)) {
                continue;
            }
            $oDescriptor = KTPermissionDescriptor::get($oPA->getPermissionDescriptorId());
            $iPermissionId = $oPermission->getId();

	    // groups
            $aGroupIds = $oDescriptor->getGroups();
            foreach ($aGroupIds as $iId) {
                $aPermissionsMap['group'][$iId][$iPermissionId] = true;
            }

	    // roles
            $aRoleIds = $oDescriptor->getRoles();
            foreach ($aRoleIds as $iId) {
                $aPermissionsMap['role'][$iId][$iPermissionId] = true;
            }	    
        }
	return $aPermissionsMap;
    }



    function json_getEntities($optFilter = null) {
	$sFilter = KTUtil::arrayGet($_REQUEST, 'filter', false);
	if($sFilter == false && $optFilter != null) {
	    $sFilter = $optFilter;
	}

	$bSelected = KTUtil::arrayGet($_REQUEST, 'selected', false);

	$aEntityList = array('off' => _kt('-- Please filter --'));

	// check permissions
        $oPO = KTPermissionObject::get($this->oFolder->getPermissionObjectId());
        $aOptions = array('redirect_to' => array('json', 'json_action=permission_error&fFolderId=' .  $this->oFolder->getId()));

        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);
        }

	// get permissions map
	$aPermissionsMap =& $this->_getPermissionsMap();

	if($bSelected || $sFilter && trim($sFilter)) {
	    if(!$bSelected) {
		$aEntityList = array();
	    }

	    $aGroups = Group::getList(sprintf('name like "%%%s%%"', $sFilter));
	    foreach($aGroups as $oGroup) {
		$aPerm = @array_keys($aPermissionsMap['group'][$oGroup->getId()]);
		if(!is_array($aPerm)) {
		    $aPerm = array();
		}
		if($bSelected) {
		    if(count($aPerm))
		    $aEntityList['g'.$oGroup->getId()] = array('type' => 'group',
							       'display' => 'Group: ' . $oGroup->getName(),
							       'name' => $oGroup->getName(),
							       'permissions' => $aPerm,
							       'id' => $oGroup->getId(),
							       'selected' => true);
		} else {
		    $aEntityList['g'.$oGroup->getId()] = array('type' => 'group',
							       'display' => 'Group: ' . $oGroup->getName(),
							       'name' => $oGroup->getName(),
							       'permissions' => $aPerm,
							       'id' => $oGroup->getId());
		}						      
	    }

	    $aRoles = Role::getList(sprintf('name like "%%%s%%"', $sFilter));
	    foreach($aRoles as $oRole) {
		$aPerm = @array_keys($aPermissionsMap['role'][$oRole->getId()]);
		if(!is_array($aPerm)) {
		    $aPerm = array();
		}
		
		if($bSelected) {
		    if(count($aPerm)) 
		    $aEntityList['r'.$oRole->getId()] = array('type' => 'role',
							      'display' => 'Role: ' . $oRole->getName(),
							      'name' => $oRole->getName(),
							      'permissions' => $aPerm,
							      'id' => $oRole->getId(),
							      'selected' => true);
		} else {
		    $aEntityList['r'.$oRole->getId()] = array('type' => 'role',
							      'display' => 'Role: ' . $oRole->getName(),
							      'name' => $oRole->getName(),
							      'permissions' => $aPerm,
							      'id' => $oRole->getId());
		}
	    }
	}	 
	return $aEntityList;
    }



    function do_update() {
        $aOptions = array('redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()));
        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);
        }
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

        $this->addInfoMessage(_kt("Permissions on folder updated"));
        $po->redirect(KTUtil::addQueryString($_SERVER['PHP_SELF'], "action=edit&fFolderId=" . $this->oFolder->getId()));
        exit(0);
    }


    function do_inheritPermissions() {
        $aOptions = array('redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()));
        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);
        }
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
        return $this->successRedirectTo('main', _kt('Permissions updated'),
                array('fFolderId' => $this->oFolder->getId()));
    }

    function do_newDynamicPermission() {
        $aOptions = array('redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()));
        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);
        }
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
        $aOptions = array('redirect_to' => array('main', 'fFolderId=' .  $this->oFolder->getId()));
        if (!KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
            $this->oValidator->userHasPermissionOnItem($this->oUser, $this->_sEditShowPermission, $this->oFolder, $aOptions);
        }
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
