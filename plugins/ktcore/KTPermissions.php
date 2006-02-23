<?php

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");
require_once(KT_LIB_DIR . "/groups/Group.inc");
require_once(KT_LIB_DIR . "/users/User.inc");
require_once(KT_LIB_DIR . "/roles/Role.inc");
require_once(KT_LIB_DIR . "/roles/roleallocation.inc.php");


require_once(KT_LIB_DIR . "/permissions/permission.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionobject.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionassignment.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissiondescriptor.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionutil.inc.php");

class KTDocumentPermissionsAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.permissions';
    var $_bAdminAlwaysAvailable = true;

    function getDisplayName() {
        return _('Permissions');
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails("permissions");

        $oTemplate = $this->oValidator->validateTemplate("ktcore/document/document_permissions");
        $oPO = KTPermissionObject::get($this->oDocument->getPermissionObjectID());
        $aPermissions = KTPermission::getList();
        $aMapPermissionGroup = array();
        $aMapPermissionRole = array();		
        foreach ($aPermissions as $oPermission) {
            $oPA = KTPermissionAssignment::getByPermissionAndObject($oPermission, $oPO);
            if (PEAR::isError($oPA)) {
                continue;
            }
            $oDescriptor = KTPermissionDescriptor::get($oPA->getPermissionDescriptorID());
            $iPermissionID = $oPermission->getID();
            $aIDs = $oDescriptor->getGroups();
            $aMapPermissionGroup[$iPermissionID] = array();
            foreach ($aIDs as $iID) {
                $aMapPermissionGroup[$iPermissionID][$iID] = true;
            }
            $aIds = $oDescriptor->getRoles();
            $aMapPermissionRole[$iPermissionID] = array();
            foreach ($aIds as $iId) {
                $aMapPermissionRole[$iPermissionID][$iId] = true;
            }		
        }

        $oInherited = KTPermissionUtil::findRootObjectForPermissionObject($oPO);
        if ($oInherited === $this->oDocument) {
            $bEdit = true;
        } else {
            $iInheritedFolderID = $oInherited->getID();
            /* $sInherited = displayFolderPathLink(Folder::getFolderPathAsArray($iInheritedFolderID),
                        Folder::getFolderPathNamesAsArray($iInheritedFolderID),
                        "$default->rootUrl/control.php?action=editFolderPermissions");*/
            $sInherited = join(" &raquo; ", $oInherited->getPathArray());
            $bEdit = false;
        }

        $aTemplateData = array(
            "context" => $this,
            "permissions" => $aPermissions,
            "groups" => Group::getList(),
            "roles" => Role::getList(),			
            "iDocumentID" => $_REQUEST['fDocumentID'],
            "aMapPermissionGroup" => $aMapPermissionGroup,
            "aMapPermissionRole" => $aMapPermissionRole,			
            "edit" => $bEdit,
            "inherited" => $sInherited,
        );
        return $oTemplate->render($aTemplateData);
    }
}

class KTRoleAllocationPlugin extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.roles';

    var $_sShowPermission = "ktcore.permissions.security";
    var $bAutomaticTransaction = true;
    var $_bAdminAlwaysAvailable = true;

    function getDisplayName() {
        return _('Allocate Roles');
    }

    function do_main() {
        $this->oPage->setTitle(_("Allocate Roles"));
        $this->oPage->setBreadcrumbDetails(_("Allocate Roles"));
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/folder/roles");
        
        // we need to have:
        //   - a list of roles
        //   - with their users / groups
        //   - and that allocation id
        $aRoles = array(); // stores data for display.
        
        $aRoleList = Role::getList();
        foreach ($aRoleList as $oRole) {
            $iRoleId = $oRole->getId();
            $aRoles[$iRoleId] = array("name" => $oRole->getName());
            $oRoleAllocation = RoleAllocation::getAllocationsForFolderAndRole($this->oFolder->getId(), $iRoleId);
            
            $u = array();
            $g = array();
            $aid = null;
            $raid = null;
            if ($oRoleAllocation == null) {
                ; // nothing.
            } else {
                $raid = $oRoleAllocation->getId(); // real_alloc_id
                if ($oRoleAllocation->getFolderId() == $this->oFolder->getId()) {
                    $aid = $oRoleAllocation->getid(); // alloc_id
                }
                $oPermDesc = KTPermissionDescriptor::get($oRoleAllocation->getPermissionDescriptorId());
                if (!PEAR::isError($oPermDesc)) {
                    $aAllowed = $oPermDesc->getAllowed();
                    if (!empty($aAllowed['user'])) {
                        $u = $aAllowed['user'];
                    }
                    if (!empty($aAllowed['group'])) {
                        $g = $aAllowed['group'];
                    }
                }
            }
            $aRoles[$iRoleId]['users'] = $u;
            $aRoles[$iRoleId]['groups'] = $g;
            $aRoles[$iRoleId]['allocation_id'] = $aid;
            $aRoles[$iRoleId]['real_allocation_id'] = $raid;
        }
        
        /*
        print '<pre>';
        var_dump($aRoles);
        print '</pre>';
        */
        

        
        // FIXME this is test data.
        /*
        $aRoles = array(
            1 => array('name' => 'Manager', 'users' => array(1), 'groups' => array(1), 'allocation_id' => 1),
            2 => array('name' => 'Peasant', 'users' => array(1), 'groups' => array(), 'allocation_id' => 2),
            3 => array('name' => 'Inherited', 'users' => array(), 'groups' => array(1), 'allocation_id' => null),
        );
        */
        
        
        // final step.
        
        // map to users, groups.
        foreach ($aRoles as $key => $role) {
		    /*
            $_users = array();
            foreach ($aRoles[$key]['users'] as $iUserId) {
                $oUser = User::get($iUserId);
                if (!(PEAR::isError($oUser) || ($oUser == false))) {
                    $_users[] = $oUser->getName();
                }
            }
			if (empty($_users)) {
			    $aRoles[$key]['users'] = '<span class="descriptiveText"> ' . _('no users') . '</span>'; 	
			} else {
                $aRoles[$key]['users'] = join(', ',$_users);
			}
			*/
            
            $_groups = array();
            foreach ($aRoles[$key]['groups'] as $iGroupId) {
                $oGroup = Group::get($iGroupId);
                if (!(PEAR::isError($oGroup) || ($oGroup == false))) {
                    $_groups[] = $oGroup->getName();
                }
            }
			if (empty($_groups)) {
			    $aRoles[$key]['groups'] = '<span class="descriptiveText"> ' . _('no groups') . '</span>'; 	
			} else {
			    $aRoles[$key]['groups'] = join(', ',$_groups);
			}
        }
        
        $aTemplateData = array(
            'context' => &$this,
            'roles' => $aRoles,
        );
        return $oTemplate->render($aTemplateData);
    }
    
    
    
    function do_overrideParent() {
        $role_id = KTUtil::arrayGet($_REQUEST, 'role_id', null);
        $oRole = Role::get($role_id);
        if (PEAR::isError($oRole)) {
            $this->errorRedirectToMain(_('Invalid Role.'));
        }
        // FIXME do we need to check that this role _isn't_ allocated?
        $oRoleAllocation = new RoleAllocation();
        $oRoleAllocation->setFolderId($this->oFolder->getId());
        $oRoleAllocation->setRoleId($role_id);
        
        // create a new permission descriptor. 
        // FIXME we really want to duplicate the original (if it exists)
        
        $aAllowed = array(); // no-op, for now.
		$this->startTransaction();
		
        $oRoleAllocation->setAllowed($aAllowed);
        $res = $oRoleAllocation->create();
		
		if (PEAR::isError($res) || ($res == false)) {
			$this->errorRedirectToMain(_('Failed to create the role allocation.') . print_r($res, true), sprintf('fFolderId=%d', $this->oFolder->getId()));
		}
        
		$this->renegeratePermissionsForRole($oRoleAllocation->getRoleId());
        
        $this->successRedirectToMain(_('Role allocation created.'), sprintf('fFolderId=%d', $this->oFolder->getId()));
    }
    
    function do_useParent() { 
        $role_id = KTUtil::arrayGet($_REQUEST, 'role_id', null);
        $oRole = Role::get($role_id);
        if (PEAR::isError($oRole)) {
            $this->errorRedirectToMain(_('Invalid Role.'), sprintf('fFolderId=%d',$this->oFolder->getId())); 
        }
        $role_id = $oRole->getId(); // numeric, for various testing purposes.
        
        $oRoleAllocation = RoleAllocation::getAllocationsForFolderAndRole($this->oFolder->getId(), $role_id);
        
        if ($oRoleAllocation->getFolderId() != $this->oFolder->getId()) {
            $this->errorRedirectToMain(_('Already using a different descriptor.'), sprintf('fFolderId=%d',$this->oFolder->getId())); 
        } 
        $this->startTransaction();
		
        $res = $oRoleAllocation->delete();
		
        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectToMain(_('Unable to change role allocation.') . print_r($res, true), sprintf('fFolderId=%d',$this->oFolder->getId())); 
            exit(0);
        }
		
		$this->renegeratePermissionsForRole($oRoleAllocation->getRoleId());
        $this->successRedirectToMain(_('Role now uses parent.'), sprintf('fFolderId=%d',$this->oFolder->getId())); 
    }
    
    function do_editRoleUsers() {

        $role_allocation_id = KTUtil::arrayGet($_REQUEST, 'alloc_id');
        $oRoleAllocation = RoleAllocation::get($role_allocation_id);
        if ((PEAR::isError($oRoleAllocation)) || ($oRoleAllocation=== false)) {
            $this->errorRedirectToMain(_('No such role allocation.'), sprintf('fFolderId=%d',$this->oFolder->getId()));
        }
        
        
        $this->oPage->setBreadcrumbDetails(_('Manage Users for Role'));
        $this->oPage->setTitle(sprintf(_('Manage Users for Role')));
        
        $initJS = 'var optGroup = new OptionTransfer("userSelect","chosenUsers"); ' .
        'function startTrans() { var f = getElement("userroleform"); ' .
        ' optGroup.saveNewRightOptions("userFinal"); ' .
        ' optGroup.init(f); }; ' .
        ' addLoadEvent(startTrans); '; 
        $this->oPage->requireJSStandalone($initJS);
        
        $aInitialUsers = $oRoleAllocation->getUsers();
        $aAllUsers = User::getList();
        
        
        // FIXME this is massively non-performant for large userbases..
        $aRoleUsers = array();
        $aFreeUsers = array();
        foreach ($aInitialUsers as $oUser) {
            $aRoleUsers[$oUser->getId()] = $oUser;
        }
        foreach ($aAllUsers as $oUser) {
            if (!array_key_exists($oUser->getId(), $aRoleUsers)) {
                $aFreeUsers[$oUser->getId()] = $oUser;
            }
        }
        
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate("ktcore/folder/roles_manageusers");
        $aTemplateData = array(
            "context" => $this,
            "edit_rolealloc" => $oRoleAllocation,
			'unused_users' => $aFreeUsers,
			'role_users' => $aRoleUsers,
        );
        return $oTemplate->render($aTemplateData);
    }
	
    function do_editRoleGroups() { 

        $role_allocation_id = KTUtil::arrayGet($_REQUEST, 'alloc_id');
        $oRoleAllocation = RoleAllocation::get($role_allocation_id);
        if ((PEAR::isError($oRoleAllocation)) || ($oRoleAllocation=== false)) {
            $this->errorRedirectToMain(_('No such role allocation.'), sprintf('fFolderId=%d',$this->oFolder->getId()));
        }
        
        
        $this->oPage->setBreadcrumbDetails(_('Manage Groups for Role'));
        $this->oPage->setTitle(sprintf(_('Manage Groups for Role')));
        
        $initJS = 'var optGroup = new OptionTransfer("groupSelect","chosenGroups"); ' .
        'function startTrans() { var f = getElement("grouproleform"); ' .
        ' optGroup.saveNewRightOptions("groupFinal"); ' .
        ' optGroup.init(f); }; ' .
        ' addLoadEvent(startTrans); '; 
        $this->oPage->requireJSStandalone($initJS);
        
        $aInitialUsers = $oRoleAllocation->getGroups();
        $aAllUsers = Group::getList();
        
        
        // FIXME this is massively non-performant for large userbases..
        $aRoleUsers = array();
        $aFreeUsers = array();
        foreach ($aInitialUsers as $oGroup) {
            $aRoleUsers[$oGroup->getId()] = $oGroup;
        }
        foreach ($aAllUsers as $oGroup) {
            if (!array_key_exists($oGroup->getId(), $aRoleUsers)) {
                $aFreeUsers[$oGroup->getId()] = $oGroup;
            }
        }
        
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate("ktcore/folder/roles_managegroups");
        $aTemplateData = array(
            "context" => $this,
            "edit_rolealloc" => $oRoleAllocation,
			'unused_groups' => $aFreeUsers,
			'role_groups' => $aRoleUsers,
        );
        return $oTemplate->render($aTemplateData);
	}
    
    function do_setRoleUsers() {

        $role_allocation_id = KTUtil::arrayGet($_REQUEST, 'allocation_id');
        $oRoleAllocation = RoleAllocation::get($role_allocation_id);
        if ((PEAR::isError($oRoleAllocation)) || ($oRoleAllocation=== false)) {
            $this->errorRedirectToMain(_('No such role allocation.'), sprintf('fFolderId=%d',$this->oFolder->getId()));
        }
        $users = KTUtil::arrayGet($_REQUEST, 'userFinal', '');
		$aUserIds = explode(',', $users);

		// check that its not corrupt..
		$aFinalUserIds = array();
		foreach ($aUserIds as $iUserId) {
			$oUser =& User::get($iUserId);
			if (!(PEAR::isError($oUser) || ($oUser == false))) {
				$aFinalUserIds[] = $iUserId;
			}
		}
		if (empty($aFinalUserIds)) { $aFinalUserIds = null; }
		
		// hack straight in.
		$oPD = $oRoleAllocation->getPermissionDescriptor();
		$aAllowed = $oPD->getAllowed();		
		
		
		
		// now, grab the existing allowed and modify.

		$aAllowed['user'] = $aFinalUserIds;
		
		$oRoleAllocation->setAllowed($aAllowed);
		$res = $oRoleAllocation->update();
		
		if (PEAR::isError($res) || ($res == false)) {
			$this->errorRedirectToMain(_('Failed to change the role allocation.') . print_r($res, true), sprintf('fFolderId=%d', $this->oFolder->getId()));
		}
		
		$this->renegeratePermissionsForRole($oRoleAllocation->getRoleId());
		
        $this->successRedirectToMain(_('Allocation changed.'), sprintf('fFolderId=%d',$this->oFolder->getId())); 
    }
    
    function do_setRoleGroups() {
	    
        $role_allocation_id = KTUtil::arrayGet($_REQUEST, 'allocation_id');
        $oRoleAllocation = RoleAllocation::get($role_allocation_id);
        if ((PEAR::isError($oRoleAllocation)) || ($oRoleAllocation=== false)) {
            $this->errorRedirectToMain(_('No such role allocation.'), sprintf('fFolderId=%d',$this->oFolder->getId()));
        }
        $groups = KTUtil::arrayGet($_REQUEST, 'groupFinal', '');
		$aGroupIds = explode(',', $groups);

		// check that its not corrupt..
		$aFinalGroupIds = array();
		foreach ($aGroupIds as $iGroupId) {
			$oGroup =& Group::get($iGroupId);
			if (!(PEAR::isError($oGroup) || ($oGroup == false))) {
				$aFinalGroupIds[] = $iGroupId;
			}
		}
		if (empty($aFinalGroupIds)) { $aFinalGroupIds = null; }
		
		// hack straight in.
		$oPD = $oRoleAllocation->getPermissionDescriptor();
		$aAllowed = $oPD->getAllowed();		
		
		
		
		// now, grab the existing allowed and modify.

		$aAllowed['group'] = $aFinalGroupIds;
		
		$oRoleAllocation->setAllowed($aAllowed);
		$res = $oRoleAllocation->update();
		
		if (PEAR::isError($res) || ($res == false)) {
			$this->errorRedirectToMain(_('Failed to change the role allocation.') . print_r($res, true), sprintf('fFolderId=%d', $this->oFolder->getId()));
		}
		
		$this->renegeratePermissionsForRole($oRoleAllocation->getRoleId());
		
        $this->successRedirectToMain(_('Allocation changed.'), sprintf('fFolderId=%d',$this->oFolder->getId()));     
    }
   	
	function renegeratePermissionsForRole($iRoleId) {
	    $iStartFolderId = $this->oFolder->getId();
		$oRoleAllocation = RoleAllocation::getAllocationsForFolderAndRole($iStartFolderId, $iRoleId);		
		/* 
		 * 1. find all folders & documents "below" this one which use the role 
		 *    definition _active_ (not necessarily present) at this point.
		 * 2. tell permissionutil to regen their permissions.
		 *
		 * The find algorithm is:
		 *
		 *  folder_queue <- (iStartFolderId)
		 *  while folder_queue is not empty:
		 *     active_folder = 
		 *     for each folder in the active_folder:
		 *         find folders in _this_ folder without a role-allocation on the iRoleId
		 *            add them to the folder_queue
		 *         update the folder's permissions.
		 *         find documents in this folder:
		 *            update their permissions.
		 */
		
		$sQuery = 'SELECT f.id as `id` FROM ' . Folder::_table() . ' AS f LEFT JOIN ' . RoleAllocation::_table() . ' AS ra ON (f.id = ra.folder_id) WHERE f.parent_id = ? AND ra.role_id ';
		if ($oRoleAllocation == null) { // no alloc.
		    $sQuery .= ' IS NULL ';
			$hasId = false;
		} else {
		    $sQuery .= ' = ? ';
			$aId = $oRoleAllocation->getId();
			$hasId = true;
		}
		
		
		$folder_queue = array($iStartFolderId);
		while (!empty($folder_queue)) {
			$active_folder = array_pop($folder_queue);
			
			
			$aParams = array($active_folder);
			if ($hasId) { $aParams[] = $aId; }
			
			
			$aNewFolders = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');
			if (PEAR::isError($aNewFolders)) {
				$this->errorRedirectToMain(_('Failure to generate folderlisting.'));
			}
			$folder_queue = array_merge ($folder_queue, $aNewFolders); // push.

			
			// update the folder.
			$oFolder =& Folder::get($active_folder);
			if (PEAR::isError($oFolder) || ($oFolder == false)) {
			    $this->errorRedirectToMain(_('Unable to locate folder: ') . $active_folder);
			}
			
			KTPermissionUtil::updatePermissionLookup($oFolder);
			$aDocList =& Document::getList(array('folder_id = ?', $active_folder));
			if (PEAR::isError($aDocList) || ($aDocList === false)) {
			    $this->errorRedirectToMain(sprintf(_('Unable to get documents in folder %s: %s'), $active_folder, $aDocList->getMessage()));
			}
			
			foreach ($aDocList as $oDoc) { 
			    KTPermissionUtil::updatePermissionLookup($oDoc);
			}
		}
	}
}
