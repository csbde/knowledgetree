<?php

require_once(KT_LIB_DIR . "/ktentity.inc"); 
require_once(KT_LIB_DIR . "/util/ktutil.inc"); 
require_once(KT_LIB_DIR . "/database/dbutil.inc"); 
 
require_once(KT_LIB_DIR . "/permissions/permissiondescriptor.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionutil.inc.php");
require_once(KT_LIB_DIR . "/users/User.inc");
require_once(KT_LIB_DIR . "/groups/Group.inc");
require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");
 
class RoleAllocation extends KTEntity {
	
	/** role object primary key */
	var $iId=-1;
	var $iFolderId;
	var $iRoleId;
	var $iPermissionDescriptorId;
	
	var $_bUsePearError = true;
	
	var $_aFieldToSelect = array(
	    'iId' => 'id',
		'iRoleId' => 'role_id',
		'iFolderId' => 'folder_id',
		'iPermissionDescriptorId' => 'permission_descriptor_id',
	);

	function setFolderId($iFolderId) { $this->iFolderId = $iFolderId; }
	function setRoleId($iRoleId) { $this->iRoleId = $iRoleId; }
	function setPermissionDescriptorId($iPermissionDescriptorId) { $this->iPermissionDescriptorId = $iPermissionDescriptorId; }
	function getFolderId() {  return $this->iFolderId; }
	function getRoleId() { return $this->iRoleId; }
	function getPermissionDescriptorId() { return $this->iPermissionDescriptorId; }

	// aggregate:  set (for this alloc) the array('user' => array(), 'group' => array()).
	function setAllowed($aAllowed) {
		$oDescriptor = KTPermissionUtil::getOrCreateDescriptor($aAllowed); // fully done, etc.
		$this->iPermissionDescriptorId = $oDescriptor->getId();
	}
	
    function _fieldValues () { return array(
            'role_id' => $this->iRoleId,
            'folder_id' => $this->iFolderId,
			'permission_descriptor_id' => $this->iPermissionDescriptorId,
        );
    }

	/* getAllocationForFolderAndRole($iFolderId, $iRoleId)
	 *
	 *   this is the key function:  for a given folder and role,
	 *   returns either a RoleAllocation object, or null 
	 *   (if there is none).  It scans _up_ the hierachy of folders,
	 *   trying to find the nearest such object with a folder_id
	 *   in the mapping.
	 */
	function & getAllocationsForFolderAndRole($iFolderId, $iRoleId) {
		// FIXME the query we use here is ... not very pleasant.  
		// NBM: is this the "right" way to do this?
		$raTable = KTUtil::getTableName('role_allocations');
		
		$fTable = Folder::_table();
		
		$oFolder =& Folder::get($iFolderId);
		$parents = $oFolder->getParentFolderIDs() . ',' . $iFolderId; // this is formatted as 1,2,3,4,5,6 - perfect for "WHERE".
		
		// FIXME what (if anything) do we need to do to check that this can't be used as an attack?
		$folders = '(' . $parents . ')';
		
		$sQuery = "SELECT ra.id as `id` FROM " . $raTable . " AS ra " .
		' LEFT JOIN ' . $fTable . ' AS f ON (f.id = ra.folder_id) ' .
		' WHERE f.id IN ' . $folders .
		' ORDER BY CHAR_LENGTH(f.parent_folder_ids) desc, f.parent_folder_ids DESC; ';
		$aParams = array();
		
		$aRoleAllocIds = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');
		
		if (false) {
		    print '<pre>';
			var_dump($aRoleAllocIds);
			print '';
			print $sQuery;
			print '</pre>';		
		}
		
		if (empty($aRoleAllocIds)) { 
		    return null;
		}
		
		$iAllocId = $aRoleAllocIds[0]; // array pop?
		return RoleAllocation::get($iAllocId);
	}
	
	// static, boilerplate.
    function _table () { return KTUtil::getTableName('role_allocations'); }
    function & get($iRoleId) { return KTEntityUtil::get('RoleAllocation', $iRoleId); }
	function & getList($sWhereClause = null) { return KTEntityUtil::getList2('RoleAllocation', $sWhereClause); }
	function & createFromArray($aOptions) { return KTEntityUtil::createFromArray('RoleAllocation', $aOptions); }

	function getPermissionDescriptor() {
	    // could be an error - return as-is.
		$oDescriptor =& KTPermissionDescriptor::get($this->iPermissionDescriptorId);
		return $oDescriptor;
	}

	// setting users and groups needs to use permissionutil::getOrCreateDescriptor
	function getUsers() {
	    $oDescriptor = $this->getPermissionDescriptor();
		$aUsers = array();
		if (PEAR::isError($oDescriptor) || ($oDescriptor == false)) {
		     return $aUsers;
		}
		$aAllowed = $oDescriptor->getAllowed();
		if ($aAllowed['user'] !== null) {
		    $aUsers = $aAllowed['user'];
		} 
		
		// now we want to map to oUsers, since that's what groups do.
		$aFullUsers = array();
		foreach ($aUsers as $iUserId) {
		    $oUser = User::get($iUserId);
			if (!(PEAR::isError($oUser) || ($oUser == false))) {
			    $aFullUsers[$iUserId] = $oUser;
			}
		}
		
		return $aFullUsers;
	}
	
	function getGroups() {
	    $oDescriptor = $this->getPermissionDescriptor();
		$aGroups = array();
		if (PEAR::isError($oDescriptor) || ($oDescriptor == false)) {
		     return $aGroups;
		}
		$aAllowed = $oDescriptor->getAllowed();
		if ($aAllowed['group'] !== null) {
		    $aGroups = $aAllowed['group'];
		} 
		
		// now we want to map to oUsers, since that's what groups do.
		$aFullGroups = array();
		foreach ($aGroups as $iGroupId) {
		    $oGroup = Group::get($iGroupId);
			if (!(PEAR::isError($oGroup) || ($oGroup == false))) {
			    $aFullGroups[$iGroupId] = $oGroup;
			}
		}
		
		return $aFullGroups;
	}
	
	function getUserIds() {
	    $oDescriptor = $this->getPermissionDescriptor();
		$aUsers = array();
		if (PEAR::isError($oDescriptor) || ($oDescriptor == false)) {
		     return $aUsers;
		}
		$aAllowed = $oDescriptor->getAllowed();
		if ($aAllowed['user'] !== null) {
		    $aUsers = $aAllowed['user'];
		} 
		
		return $aUsers;
	}
	
	function getGroupIds() {
	    $oDescriptor = $this->getPermissionDescriptor();
		$aGroups = array();
		if (PEAR::isError($oDescriptor) || ($oDescriptor == false)) {
		     return $aGroups;
		}
		$aAllowed = $oDescriptor->getAllowed();
		if ($aAllowed['group'] !== null) {
		    $aGroups = $aAllowed['group'];
		} 
		
		return $aGroups;
	}	
	
	// utility function to establish user membership in this allocation.
	// FIXME nbm:  is there are more coherent way to do this ITO your PD infrastructure?
	function hasMember($oUser) {
	    $oPD = $this->getPermissionDescriptor();
		if (PEAR::isError($oPD) || ($oPD == false)) {
		    return false;
		}
		$aAllowed = $oPD->getAllowed();
		$iUserId = $oUser->getId();
		
		if ($aAllowed['user'] != null) {
			if (array_search($iUserId, $aAllowed['user']) !== false) {
			    return true;
			}
		}
		
		// now we need the group objects.
		// FIXME this could accelerated to a single SQL query on group_user_link.
		$aGroups = $this->getGroups();
		if (PEAR::isError($aGroups) || ($aGroups == false)) {
		    return false;
		} else {
		    foreach ($aGroups as $oGroup) {
				if ($oGroup->hasMember($oUser)) {
				    return true;
				}
			}
		}
	    
	    return false;
	}
	
}

?>
