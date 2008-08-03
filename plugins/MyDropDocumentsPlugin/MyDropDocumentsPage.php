<?php
/**
 * $Id: $
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

require_once("config/dmsDefaults.php");
require_once(KT_DIR .  "/ktapi/ktapi.inc.php");
require_once(KT_LIB_DIR . "/plugins/plugin.inc.php");
require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php"); 
require_once(KT_LIB_DIR . "/dashboard/dashlet.inc.php");
require_once(KT_DIR . "/plugins/ktcore/KTFolderActions.php");
require_once(KT_DIR . "/ktapi/KTAPIFolder.inc.php");
require_once(KT_LIB_DIR . "/roles/Role.inc");
require_once(KT_LIB_DIR . "/roles/roleallocation.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionutil.inc.php");
require_once(KT_LIB_DIR . '/mime.inc.php');
/* This page is run via an AJAX call from the update.js for this plugin. 
 * It checks to see if both the dropdocuments folder and the users personal folder exist. 
 * If they don't, it creates them and assigns permission and roles accordingly.
 * If the dropdocuments folder does exist it checks if the WorkSpaceOwner role exists.
 * If the role exists it assigns the current user to the role on the dropdocuments folder.
 * Therefore any users running the plugin after the dropdocuments folder has been created will have access to it too.
 * The underlying logic is that everyone is assigned to the WorkSpaceOwner Role, they have all permission except
 * Delete, Rename Folder, Manage security and Manage workflow on the dropdocuments folder.
 * This role is then assigned to their personal folder too (which is named according to their username) and is overidden
 * to give only the current user full rights to their folder.
 * Essentially everyone can look at the dropdocuments folder but will only see their own folder within it.
 */
 
class MyDropDocumentsPage extends KTStandardDispatcher {
    
    function do_main() {
       
       $iRootID = (int)1;
       $oUser = $this->oUser;
       $sUserName = (string)$this->oUser->getUserName();
       $this->ktapi = new KTAPI();
       $this->session = $this->ktapi->start_system_session();
       
       if(!Folder::FolderExistsName('DroppedDocuments', $iRootID))
       {
            		
            		$root=$this->ktapi->get_root_folder();
					
					//Create dropdocuments folder
					$userFolder = $root->add_folder('DroppedDocuments');
					
					//In order to stop permission inheritance a copy of the parent permission object is created.
					//This copy is then used to set separate permissions for this folder.
					KTPermissionUtil::copyPermissionObject($userFolder->get_folder());
					
					//If WorkSpaceOwner role doesn't exist, create it
					if(!$this->roleExistsName('WorkSpaceOwner'))
		       		{
		       			$oWorkSpaceOwnerRole = $this->createRole('WorkSpaceOwner');	
		       			if ($oWorkSpaceOwnerRole == null)
		       			{
		       				$this->session->logout();
		       				return _kt('Error: Failed to create WorkSpaceOwner Role');
		       			}
		       		}
		       			
	       			//$root=$this->ktapi->get_root_folder();
	       			//$personalFolder = $root->get_folder_by_name('/dropdocuments/'.$sUserName);
	       			
	       			//Get the folder object
	       			$userFolderObject = $userFolder->get_folder();
	       			
	       			//Get the permission object from the dropdocuments folder object
	       			$oUserPO = KTPermissionObject::get($userFolderObject->getPermissionObjectId());
	       			
	       			//Check to see if there are duplicate WorkSpaceOwner roles.
	       			if (count($this->getRoleIdByName('WorkSpaceOwner')) > 1)
	       			{
	       				$this->session->logout();
	       				return _kt('Error: cannot set user role permissions: more than one role named \'WorkSpaceOwner\' exists'); 
	        				
	       			}
	       			
	       			//call the function to set the permission on the dropdocuments folder
	       			$this->setUserDocsPermissions($oUserPO);
					
					//Assign the current user to the WorkSpaceOwner role
	                $this->setUserDocsRoleAllocation($userFolderObject);
	
       }
       else
       {
       		
       		$root = $this->ktapi->get_root_folder();
       		$userFolder = $root->get_folder_by_name('/DroppedDocuments');
       		
       		//Get the dropdocuments folder object
       		$userFolderObject = $userFolder->get_folder();
       		
       		if(!$this->roleExistsName('WorkSpaceOwner'))
       		{
       			
       			$oWorkSpaceOwnerRole = $this->createRole('WorkSpaceOwner');	
       			if ($oWorkSpaceOwnerRole == null)
       			{
       				$this->session->logout();
       				return _kt('Error: Failed to create WorkSpaceOwner Role');
       			}
       			
       			//set permissions
       			$oUserPO = KTPermissionObject::get($userFolderObject->getPermissionObjectId());
       			$this->setUserDocsPermissions($oUserPO);
       			//assign current user to role
       			$this->setUserDocsRoleAllocation($userFolderObject);
       		}
       		else
       		{
       			
       			//update WrokSpaceOwner role to include current user
       			$this->updateUserDocsRoleAllocation($userFolderObject);
       		}
 		       	
       }
       
       $iUserDocsFolderID = $this->getFolderID('DroppedDocuments');
       $oUserDocsFolder = Folder::get($iUserDocsFolderID);
       
       if(!Folder::FolderExistsName($sUserName, $iUserDocsFolderID))
       {
      		
       		            		
            		$root=$this->ktapi->get_root_folder();
            		$userDocsFolder = $root->get_folder_by_name('/DroppedDocuments');
            		
            		//create the personal folder. (Use the username to create it)
            		$personalFolder = $userDocsFolder->add_folder($sUserName);
            		
            		//Copy the permission object to stop permission inheritance
            		KTPermissionUtil::copyPermissionObject($personalFolder->get_folder());
            		
            		//The role should exist by now.
            		//In both the if and else statements for the dropdocuments above the role is created
            		//If its doesn't exist by now there is an error 
 					if(!$this->roleExistsName('WorkSpaceOwner')) 
       				{
       						
		       					$this->session->logout();
		       					return _kt('Error: WorkSpaceOwner Role not setup, cannot assign to Personal Folder');
		       				
       				}	
					
   					$personalFolderRole = $root->get_folder_by_name('/DroppedDocuments/'.$sUserName);
   					$PersonalFolderObject = ($personalFolderRole->get_folder());
   					
   					//Get permission object
   					$oPO = KTPermissionObject::get($PersonalFolderObject->getPermissionObjectId());
   					
   					//Check for duplicate WorkSpaceOwner roles
   					if (count($this->getRoleIdByName('WorkSpaceOwner')) > 1)
   					{
   						$this->session->logout();
   						return _kt('Error: cannot set personal folder role permissions: more than one role named \'WorkSpaceOwner\' exists'); 
    				
	       			}
	       			
	       			$this->setPersonalFolderPermissions($oPO);

					$this->updatePersonalFolderRoleAllocation($PersonalFolderObject);
		
				
            	//folder just created so no top list of last modified documents
       		       		
       		$iMyDocsFolderID = $this->getFolderID($sUserName);
       		$this->session->logout();
       		return _kt('<span class="descriptiveText"> You do not have any dropped documents </span><br><br><br>');
						
			
       }
      
       else //if personal folder does exist
       {
      		//Getting personal folder id
      		$iMyDocsFolderID = $this->getFolderID($sUserName);
      		
      		
      		if(!$this->roleExistsName('WorkSpaceOwner'))
       		{
       			$this->session->logout();
       			return _kt('Error: WorkSpaceOwner Role does not exist');
       		}
       		else
       		{
       		
       			$oTempPersonalFolder = $root->get_folder_by_name('/DroppedDocuments/'.$sUserName);
       			$oPersonalFolder = $oTempPersonalFolder->get_folder();
       			//update WorkSpaceOwner role to include current user
       			
       			//Get permission object
   				$oPO = KTPermissionObject::get($oPersonalFolder->getPermissionObjectId());
       			
       			$this->setPersonalFolderPermissions($oPO);
       			
       			$this->updatePersonalFolderRoleAllocation($oPersonalFolder);
       			
       		}
       		
       		
       		
       		$aExternalWhereClauses[] = '(DT.transaction_namespace IN (?,?,?) AND (D.parent_folder_ids LIKE "%,'.$iMyDocsFolderID.',%" OR D.parent_folder_ids LIKE "%,'.$iMyDocsFolderID.'"))';
       		$aExternalWhereParams[] = 'ktcore.transactions.create';
        	$aExternalWhereParams[] = 'ktcore.transactions.check_in';
			$aExternalWhereParams[] = 'ktcore.transactions.event';
			
        	
        	$aDocumentTransactions = KTSimpleTransactionUtil::getTransactionsMatchingQuery($oUser, '', $aExternalWhereClauses, $aExternalWhereParams);
			if (empty($aDocumentTransactions))
			{ 
				$this->session->logout();
				return _kt('<span class="descriptiveText"> You do not have any dropped documents </span><br><br><br>');
			}
        
        	$maxcount = 5;
        	$aDocumentTransactions = array_slice($aDocumentTransactions, 0, $maxcount);
       		
       		$sReturnTable =  '<span class="descriptiveText">'._kt('Recently Dropped Documents').'</span>
       				<table width="100%" class="kt_collection drop_box" cellspacing="0"> 
       				
					<thead>
    				<tr>
        			<th width="100%">'._kt('Document').'</th>    
        			<th width="1%">'._kt('Date Dropped').'</th>
        			</tr>
					</thead>
					<tbody>';
					
			$sOddorEven = '';
			$count = 1;
			foreach ($aDocumentTransactions as $aRow)
			{
    			$oDocument = Document::get($aRow[document_id]);
				$aParentFolders = explode('/',$oDocument->getFullPath());
				$sPath = '';

				for($i = 0; $i < count($aParentFolders); $i++)
				{
					if ($i > 2)
					{
						$sPath  .= '/'.$aParentFolders[$i];
					}
				}
				
				$sContentType = KTMime::getIconPath($oDocument->getMimeTypeID());
				$aAnchorData = $this->getDocInfo($aRow[document_id]);
				$sLink = $aAnchorData[0];
				$sDocName = $aAnchorData[1];
				$sShortDocName = $sDocName;
				if(strlen($sPath) > 0)
				{
					$sDocName = $sPath.'/'.$sDocName;
				}
				
				$sFullDocName = $sDocName;
				$iDocLength = strlen($sDocName);
				if ( $iDocLength > 30 )
				{
					$sDocName = substr($sDocName, ($iDocLength - 30), $iDocLength);
					$sDocName = '...'.$sDocName;
				}
				
				if($count%2 == 0)
				{
					$sOddorEven = 'even';
				}
				else
				{
					$sOddorEven = 'odd';
				}
				
    			$sReturnTable .= '<tr class="'.$sOddorEven.'">'.
        						 '<td width="100%"><span class="contenttype '.$sContentType.'"><a title="'.$sShortDocName.'" href='.$sLink.'>'.$sDocName.'</a></span></td>'.
        						 '<td width="1%">'.$aRow[datetime].'</td>'.                 
    							 '</tr>';
    			$count ++;
			}

			$location = 'browse.php?fFolderId='.$iMyDocsFolderID;
			$sReturnTable .= '</tbody>'.
							 '</table>'.
							 '<br>'.
							 '<a href="'.$location.'">'._kt(' View All').' </a><br><br>'; 
			$this->session->logout();

       		return $sReturnTable;

       }
	}

    function handleOutput($sOutput) {
        print $sOutput;
    }
   
    //This function is used to set the permission on the dropdocuments folder
	function setUserDocsPermissions($oUserPO)
	{
		//arrays returned from get Role ID's
		$aWorkSpaceOwnerRoleID = $this->getRoleIdByName('WorkSpaceOwner');
		$aAdminGroupID = $this->getGroupIdByName('System Administrators');
		
		//arrays used to make integers for $aAllowed array variable
		$iWorkSpaceOwnerRoleID = $aWorkSpaceOwnerRoleID[0]['id'];
		$iAdminGroupID = $aAdminGroupID[0]['id'];
		//$aBothAllowed is used to give permissions to the admin group and the WorkSpaceOwner role
		$aBothAllowed = array('group' => array($iAdminGroupID), 'role' => array($iWorkSpaceOwnerRoleID));
		
		//$aAdminAllowed is used to give permissions to the admin group only
		$aAdminAllowed = array('group' => array($iAdminGroupID));
		
		//Get the list of permissions
		$aPermissions = KTPermission::getList();
		
		foreach ($aPermissions as $oPermission) 
		{
   			//If the permission is not one of the below then both are allowed the permission
   			//Otherwise only the admin group is allowed the permission
   			if($oPermission->getHumanName() != 'Delete' && $oPermission->getHumanName() != 'Rename Folder'
   				&& $oPermission->getHumanName() != 'Manage security' && $oPermission->getHumanName() != 'Manage workflow')
   			{
   				KTPermissionUtil::setPermissionForId($oPermission, $oUserPO, $aBothAllowed);
   			}
   			else
   			{
   				KTPermissionUtil::setPermissionForId($oPermission, $oUserPO, $aAdminAllowed);
   			}
   		}
   		
        //UPdate the permission lookup
        KTPermissionUtil::updatePermissionLookupForPO($oUserPO);
	}   
	
	//This function is used for allocating the user to the WorkSpaceOwner role only when the dropdocuments folder 
	//has just been created.
	function setUserDocsRoleAllocation($oUserFolderObject)
	{
		$userFolderID = $oUserFolderObject->getId();
	                
        $tempWorkSpaceOwnerRoleID = $this->getRoleIdByName('WorkSpaceOwner');
        $WorkSpaceOwnerRoleID = $tempWorkSpaceOwnerRoleID[0]['id'];
        
        //create a new role allocation
		$oDropdocumentsRoleAllocation = new RoleAllocation();
		if ($oDropdocumentsRoleAllocation == null)
		{
			$this->session->logout();
			return _kt('Error: cannot create WorkSpaceOwner role allocation');
		}
		
		//set the folder and role for the allocation
		$oDropdocumentsRoleAllocation->setFolderId($userFolderID);
		$oDropdocumentsRoleAllocation->setRoleId($WorkSpaceOwnerRoleID);
		
		$aWorkSpaceOwnerRoleAllowed = array();
		$oDropdocumentsRoleAllocation->setAllowed($aWorkSpaceOwnerRoleAllowed);
	    //It might be a problem that i'm not doing a "start transaction" here. 
	    //Unable to roll back in event of db failure    	
		$res = $oDropdocumentsRoleAllocation->create();
		
		//The role is created and then updated by adding the current user to the allowed list 
		
		$oPD = $oDropdocumentsRoleAllocation->getPermissionDescriptor();
		$aWorkSpaceOwnerRoleAssignAllowed = $oPD->getAllowed();
		$aUserId[] = $this->oUser->getId();	
		$aWorkSpaceOwnerRoleAssignAllowed['user'] = $aUserId;
		$oDropdocumentsRoleAllocation->setAllowed($aWorkSpaceOwnerRoleAssignAllowed);
		$res = $oDropdocumentsRoleAllocation->update();
		
		//Update all info linked to the role
		$this->renegeratePermissionsForRole($oDropdocumentsRoleAllocation->getRoleId(), $userFolderID);
	}
	
	//This function is used to allocate the current user to the WorkSpaceOwner role after the Dropdocuments folder
	//has already been created. 
	function updateUserDocsRoleAllocation($oUserFolder)
	{
		$userFolderID = $oUserFolder->getId();
	    $tempWorkSpaceOwnerRoleID = $this->getRoleIdByName('WorkSpaceOwner');//$oUserRole->getId();
        $WorkSpaceOwnerRoleID = $tempWorkSpaceOwnerRoleID[0]['id'];
        
        //Get the role allocation object for the Dropdocuments folder and the WorkSpaceOwner role
        $oDropdocumentsRoleAllocation = $oRoleAllocation = RoleAllocation::getAllocationsForFolderAndRole($userFolderID, $WorkSpaceOwnerRoleID);
		
		//check that the object is not null
		if ($oDropdocumentsRoleAllocation == null)
		{
			$this->session->logout();
			return _kt('Error: cannot find WorkSpaceOwner role allocation');
		}
		
		$oPD = $oDropdocumentsRoleAllocation->getPermissionDescriptor();
		$aWorkSpaceOwnerRoleAssignAllowed = $oPD->getAllowed();
		
		//If the user ID is not in the allowed list already then add it to the list.		
		if(!in_array($this->oUser->getId(), $aWorkSpaceOwnerRoleAssignAllowed['user']))
		{
			$aNewAllowed = array();
			$aNewAllowed = $aWorkSpaceOwnerRoleAssignAllowed['user'];
			$aNewAllowed[] = $this->oUser->getId();
			$aWorkSpaceOwnerRoleAssignAllowed['user'] = $aNewAllowed;
			$oDropdocumentsRoleAllocation->setAllowed($aWorkSpaceOwnerRoleAssignAllowed);
			$res = $oDropdocumentsRoleAllocation->update();
			$this->renegeratePermissionsForRole($oDropdocumentsRoleAllocation->getRoleId(), $userFolderID);
		}
	}
   
   	function setPersonalFolderPermissions($oPO)
   	{
   		$aWorkSpaceOwnerRoleID = $this->getRoleIdByName('WorkSpaceOwner');
		$aAdminGroupID = $this->getGroupIdByName('System Administrators');
		
		//arrays used to make integers for $aAllowed array variable
		$iWorkSpaceOwnerRoleID = $aWorkSpaceOwnerRoleID[0]['id'];
		$iAdminGroupID = $aAdminGroupID[0]['id'];
		
		//set permissions for the role and the admin group
		$aAllowed = array('role' => array($iWorkSpaceOwnerRoleID), 'group' => array($iAdminGroupID));
		
		//Get the List of all the permissions
		$aPersonalFolderPermissions = KTPermission::getList();

		//Iterate through and apply all permissions to the current user and the admin group 
		foreach ($aPersonalFolderPermissions as $oPersonalFolderPermission) 
		{
			KTPermissionUtil::setPermissionForId($oPersonalFolderPermission, $oPO, $aAllowed);
	
		}
 		
 		//Update permission lookup
 		KTPermissionUtil::updatePermissionLookupForPO($oPO);
   	}
   
   	function updatePersonalFolderRoleAllocation($oPersonalFolder)
   	{
   		//Assign user to the WorkSpaceOwner role
        $personalFolderID = $oPersonalFolder->getId();
        $tempWorkSpaceOwnerRoleID = $this->getRoleIdByName('WorkSpaceOwner');
        $WorkSpaceOwnerRoleID = $tempWorkSpaceOwnerRoleID[0]['id'];
        
		$oRoleAllocation = new RoleAllocation();
		if ($oRoleAllocation == null)
		{
			$this->session->logout();
			return _kt('Error: Cannot create WorkSpaceOwner role allocation on personal folder');
		}
		$oRoleAllocation->setFolderId($personalFolderID);
		$oRoleAllocation->setRoleId($WorkSpaceOwnerRoleID);
		
		$aRoleAllowed = array();
		$oRoleAllocation->setAllowed($aRoleAllowed);
		
	    //It might be a problem that i'm not doing a "start transaction" here. 
	    //Unable to roll back in event of db failure    	
		$res = $oRoleAllocation->create();
		
		//The role is first created and then the current user is allocated to the role below
		
		$oPD = $oRoleAllocation->getPermissionDescriptor();
		$aRoleAssignAllowed = $oPD->getAllowed();
		$aUserId[] = $this->oUser->getId();	
		$aRoleAssignAllowed['user'] = $aUserId;
		$oRoleAllocation->setAllowed($aRoleAssignAllowed);
		$res = $oRoleAllocation->update();
		$this->renegeratePermissionsForRole($oRoleAllocation->getRoleId(), $personalFolderID);
   	}
   
   	//FIXME: Direct Database access   
    function getFolderID($sFolderName) {
        $sQuery = 'SELECT id FROM folders WHERE name = \''.$sFolderName.'\'';
                
                $id = DBUtil::getResultArray($sQuery);
                return $id[0]['id'];
	}
	
	//this function returns the document link and document name to be displayed on the dashlet
	function getDocInfo($iDocId) {
        $oDocument = Document::get($iDocId);
        
        if (PEAR::isError($oDocument)) {
            return _kt('Document no longer exists.');
        }
        
        $sName = htmlentities($oDocument->getName(), ENT_NOQUOTES, 'UTF-8');
        $sLink = KTBrowseUtil::getUrlForDocument($oDocument);
        
		$aAnchorData = array();
		$aAnchorData[] = $sLink;
		$aAnchorData[] = $sName;
		return $aAnchorData;
    }
    
    //This function is used to create the role, role allocation is done separately
    function createRole ($sName)
    {
    	$this->startTransaction();
        $oRole = Role::createFromArray(array('name' => $sName));        
        
        if (PEAR::isError($oRole) || ($oRole == false))
        {
    		if ($this->bTransactionStarted)
            {
            	 $this->rollbackTransaction();
        	}
            //return null on failure
            return null;
        }
        else
        {
        	return $oRole;
        	
        }
    }
    
    //FIXME: Direct Database access
    function roleExistsName ($sName)
    {
    	$sQuery = "SELECT id FROM roles WHERE name = ?";
        $aParams = array($sName);
		$res = DBUtil::getResultArray(array($sQuery, $aParams));
		
		if (count($res) != 0) 
		{
			return true;
		}
		return false;
    }
    
    //FIXME: Direct Database access
    function groupExistsName ($sName)
    {
    	$sQuery = "SELECT id FROM groups_lookup WHERE name = ?";
        $aParams = array($sName);
		$res = DBUtil::getResultArray(array($sQuery, $aParams));
		
		if (count($res) != 0) 
		{
			return true;
		}
		return false;
    }
    
    //FIXME: Direct Database access
    function getRoleIdByName($sName)
    {
    	$sQuery = "SELECT id FROM roles WHERE name = ?";
        $aParams = array($sName);
		$res = DBUtil::getResultArray(array($sQuery, $aParams));
		return $res;
    }
        
    //FIXME: Direct Database access
    function getGroupIdByName ($sName)
    {
    	$sQuery = "SELECT id FROM groups_lookup WHERE name = ?";
        $aParams = array($sName);
		$res = DBUtil::getResultArray(array($sQuery, $aParams));
		return $res;
    }  
    
    //function taken from KTPermission.php and edited to work here
    function renegeratePermissionsForRole($iRoleId, $iFolderId) {
	    $iStartFolderId = $iFolderId;
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

		$sRoleAllocTable = KTUtil::getTableName('role_allocations');
		$sFolderTable = KTUtil::getTableName('folders');
		$sQuery = sprintf('SELECT f.id as id FROM %s AS f LEFT JOIN %s AS ra ON (f.id = ra.folder_id) WHERE ra.id IS NULL AND f.parent_id = ?', $sFolderTable, $sRoleAllocTable);


		$folder_queue = array($iStartFolderId);
		while (!empty($folder_queue)) {
			$active_folder = array_pop($folder_queue);

			$aParams = array($active_folder);

			$aNewFolders = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');
			if (PEAR::isError($aNewFolders)) {
				//$this->errorRedirectToMain(_kt('Failure to generate folderlisting.'));
				echo _kt('Failure to generate folderlisting.');
			}
			$folder_queue = kt_array_merge ($folder_queue, (array) $aNewFolders); // push.


			// update the folder.
			$oFolder =& Folder::get($active_folder);
			if (PEAR::isError($oFolder) || ($oFolder == false)) {
			    //$this->errorRedirectToMain(_kt('Unable to locate folder: ') . $active_folder);
			    echo _kt('Unable to locate folder: ').$active_folder;
			}

			KTPermissionUtil::updatePermissionLookup($oFolder);
			$aDocList =& Document::getList(array('folder_id = ?', $active_folder));
			if (PEAR::isError($aDocList) || ($aDocList === false)) {
			    //$this->errorRedirectToMain(sprintf(_kt('Unable to get documents in folder %s: %s'), $active_folder, $aDocList->getMessage()));
			    echo _kt('Unable to get documents in folder ').$active_folder;
			}

			foreach ($aDocList as $oDoc) {
			    if (!PEAR::isError($oDoc)) {
			        KTPermissionUtil::updatePermissionLookup($oDoc);
				}
			}
		}
	}
    
     /*
         attempt to abstract the transaction-matching query.

         tables that are already defined (other than sec ones):

         - Documents (D)
         - Users (U)
         - TransactionTypes (DTT)
         - Document Transactions (DT)

         so where clausess can take advantage of those.   

      */
    function getTransactionsMatchingQuery($oUser, $sJoinClause, $aExternalWhereClauses, $aExternalWhereParams, $aOptions = null) {

        $sSelectItems = 'DTT.name AS transaction_name, U.name AS user_name, DT.version AS version, DT.comment AS comment, DT.datetime AS datetime, D.id as document_id, DT.transaction_namespace as namespace';    
        $sBaseJoin =  "FROM " . KTUtil::getTableName("document_transactions") . " AS DT " .
            "INNER JOIN " . KTUtil::getTableName("users") . " AS U ON DT.user_id = U.id " .
            "INNER JOIN " . KTUtil::getTableName("transaction_types") . " AS DTT ON DTT.namespace = DT.transaction_namespace " .
            "INNER JOIN " . KTUtil::getTableName("documents") . " AS D ON D.id = DT.document_id ";

        // now we're almost at partialquery like status.
        $perm_res = KTSearchUtil::permissionToSQL($oUser, 'ktcore.permissions.read');
        if (PEAR::isError($perm_res)) {
            return $perm_res;
        }
        list($sPermissionString, $aPermissionParams, $sPermissionJoin) = $perm_res;

        // compile the final list
        $aFinalWhere = kt_array_merge(array($sPermissionString,'D.creator_id IS NOT NULL'), $aExternalWhereClauses, array('D.status_id = ?'));
        $aFinalWhereParams = kt_array_merge($aPermissionParams, $aExternalWhereParams, array(LIVE));

        if (!is_array($aOptions)) {
            $aOptions = (array) $aOptions;
        }        
        $sOrderBy = KTUtil::arrayGet($aOptions, 'orderby', 'DT.datetime DESC');

        // compile these.
        // NBM: do we need to wrap these in ()?
        $sWhereClause = implode(' AND ', $aFinalWhere);
        if (!empty($sWhereClause)) {
            $sWhereClause = 'WHERE ' . $sWhereClause;
        }

        $sQuery = sprintf("SELECT %s %s %s %s %s ORDER BY %s",
            $sSelectItems,
            $sBaseJoin,
            $sPermissionJoin,
            $sJoinClause,
            $sWhereClause,
            $sOrderBy
        );

        //var_dump(array($sQuery, $aFinalWhereParams));

        $res = DBUtil::getResultArray(array($sQuery, $aFinalWhereParams));
        //var_dump($res); exit(0);
        return $res;
    }
}
?>
