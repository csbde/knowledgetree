<?php

require_once(KT_LIB_DIR . "/ktentity.inc"); 
require_once(KT_LIB_DIR . "/util/ktutil.inc"); 
require_once(KT_LIB_DIR . "/database/dbutil.inc"); 
 
require_once(KT_LIB_DIR . "/permissions/permissiondescriptor.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionutil.inc.php");
require_once(KT_LIB_DIR . "/users/User.inc");
require_once(KT_LIB_DIR . "/groups/Group.inc");
require_once(KT_LIB_DIR . "/documentmanagement/Document.inc");
require_once(KT_LIB_DIR . "/documentmanagement/documentcore.inc.php");

require_once(KT_LIB_DIR . "/roles/roleallocation.inc.php");

 
class DocumentRoleAllocation extends KTEntity {
	
	/** role object primary key */
	var $iId=-1;
	var $iDocumentId;
	var $iRoleId;
	var $iPermissionDescriptorId;
	
	var $_bUsePearError = true;
	
	var $_aFieldToSelect = array(
	    'iId' => 'id',
		'iRoleId' => 'role_id',
		'iDocumentId' => 'document_id',
		'iPermissionDescriptorId' => 'permission_descriptor_id',
	);

	function setDocumentId($iDocumentId) { $this->iDocumentId = $iDocumentId; }
	function setRoleId($iRoleId) { $this->iRoleId = $iRoleId; }
	function setPermissionDescriptorId($iPermissionDescriptorId) { $this->iPermissionDescriptorId = $iPermissionDescriptorId; }
	function getDocumentId() {  return $this->iDocumentId; }
	function getRoleId() { return $this->iRoleId; }
	function getPermissionDescriptorId() { return $this->iPermissionDescriptorId; }

	// aggregate:  set (for this alloc) the array('user' => array(), 'group' => array()).
	function setAllowed($aAllowed) {
		$oDescriptor = KTPermissionUtil::getOrCreateDescriptor($aAllowed); // fully done, etc.
		$this->iPermissionDescriptorId = $oDescriptor->getId();
	}
	
	function getAllowed() {
	    if (!is_null($this->iPermissionDescriptorId)) {
	        $oDescriptor = KTPermissionDescriptor::get($this->iPermissionDescriptorId); // fully done, etc.	
		    $aAllowed = $oDescriptor->getAllowed();
		} else {
		    $aAllowed = array();
		}
		// special case "document owner".
		if ($this->iRoleId == -1) {
		    
		    $oDoc = KTDocumentCore::get($this->iDocumentId);
			
			/* ! NBM Please Review
			 *
			 * This should never be an error - we were called by PermissionUtil 
             * to get the details for a document, but it _is_ be a DocumentCore
			 * object during _add.
			 *
			 * When we try to grab the Document, it blows up on the MetadataVersion,
			 * so we have to use a DocumentCore to avoid a fail-out on the initial 
             * on-add permission check.
			 *
			 * Is this bad/evil/not appropriate in some way?  I can't see a major
			 * issue with it...
 			 *
			 */
			
		
			if (PEAR::isError($oDoc)) { 
			    return $aAllowed; 
		    }
			
		    // ! NBM Please review
		    // we cascade "owner" from the folder (if, for some _bizarre_ reason the
		    // owner role is allocated to users/groups/etc.  this can be disabled
		    // with the CRACK_IS_BAD flag, or removed entirely.  I am undecided.
			//
			// There is some argument to be made for the consistency, but it may not be 
			// that big.  I think it _may_ lead to easily misconfigured setups, but I 
			// really don't know.
			$CRACK_IS_BAD = false;
			if ((!$CRACK_IS_BAD) && is_null($this->iPermissionDescriptorId)) {
				$oDerivedAlloc = RoleAllocation::getAllocationsForFolderAndRole($oDoc->getFolderID(), $this->iRoleId);
				if (!(PEAR::isError($oDerivedAlloc) || is_null($oDerivedAlloc))) { 
				    $aAllowed = $oDerivedAlloc->getAllowed();
				}
			}							
			
			$owner_id = $oDoc->getOwnerId();
		    if (is_null($aAllowed['user'])) {
				$aAllowed['user'] = array($owner_id);
			} else 	if (array_search($owner_id, $aAllowed['user']) === false) {
			    $aAllowed['user'][] = $owner_id;
			}
		}
		return $aAllowed;
	}
	
    function _fieldValues () { return array(
            'role_id' => $this->iRoleId,
            'document_id' => $this->iDocumentId,
			'permission_descriptor_id' => $this->iPermissionDescriptorId,
        );
    }

	/* getAllocationForDocumentAndRole($iFolderId, $iRoleId)
	 *
	 *   this is the key function:  for a given document and role,
	 *   returns either a RoleAllocation object, or null 
	 *   (if there is none).  IT DOES NOT SCAN UP THE HIERACHY (Use RoleAllocation)
	 */
	function & getAllocationsForDocumentAndRole($iDocumentId, $iRoleId) {
		$raTable = KTUtil::getTableName('document_role_allocations');
		
		$dTable = KTUtil::getTableName('documents');
		
		$sQuery = "SELECT ra.id as `id` FROM " . $raTable . " AS ra " .
		' LEFT JOIN ' . $dTable . ' AS d ON (d.id = ra.document_id) ' .
		' WHERE d.id = ?' .
		' AND ra.role_id = ?';
		$aParams = array($iDocumentId, $iRoleId);
		
		$iAllocId = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
		if (PEAR::isError($iAllocId)) { 
		    return null; 
		}
		if (false) {
		    print '<pre>';
			var_dump($iAllocId);
			print '';
			print $sQuery;
			print '</pre>';		
		}
		
		// magic for the Owner role here.
		if (empty($iAllocId) && ($iRoleId == -1)) {
		    $permDescriptor = null;
			// THIS OBJECT MUST NEVER BE MODIFIED, without first calling CREATE.
		    $oFakeAlloc = new DocumentRoleAllocation();
			$oFakeAlloc->setDocumentId($iDocumentId);
			$oFakeAlloc->setRoleId($iRoleId);		
			$oFakeAlloc->setPermissionDescriptorId($permDescriptor);		
			//var_dump($oFakeAlloc);
			return $oFakeAlloc;
		} else if (empty($iAllocId)) { 
		    return null;
		}
		
		return DocumentRoleAllocation::get($iAllocId);
	}
	
	// static, boilerplate.
    function _table () { return KTUtil::getTableName('document_role_allocations'); }
    function & get($iRoleId) { return KTEntityUtil::get('DocumentRoleAllocation', $iRoleId); }
	function & getList($sWhereClause = null) { return KTEntityUtil::getList2('DocumentRoleAllocation', $sWhereClause); }
	function & createFromArray($aOptions) { return KTEntityUtil::createFromArray('DocumentRoleAllocation', $aOptions); }

	function getPermissionDescriptor() {
	    // could be an error - return as-is.
		$oDescriptor =& KTPermissionDescriptor::get($this->iPermissionDescriptorId);
		return $oDescriptor;
	}

	// setting users and groups needs to use permissionutil::getOrCreateDescriptor
	function getUsers() {
		$aAllowed = $this->getAllowed();
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
		$aAllowed = $this->getAllowed();
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
		$aAllowed = $this->getAllowed();
		if ($aAllowed['user'] !== null) {
		    $aUsers = $aAllowed['user'];
		} 
		
		return $aUsers;
	}
	
	function getGroupIds() {
		$aAllowed = $this->getAllowed();
		if ($aAllowed['group'] !== null) {
		    $aGroups = $aAllowed['group'];
		} 
		
		return $aGroups;
	}	
	
	// utility function to establish user membership in this allocation.
	// FIXME nbm:  is there are more coherent way to do this ITO your PD infrastructure?
	function hasMember($oUser) {
		$aAllowed = $this->getAllowed();
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
