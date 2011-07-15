<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
 *
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

require_once(KT_LIB_DIR . "/documentmanagement/Document.inc");
require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");

require_once(KT_LIB_DIR . "/permissions/permission.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionassignment.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissiondescriptor.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionlookup.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionlookupassignment.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionobject.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissiondynamiccondition.inc.php");

require_once(KT_LIB_DIR . '/security/PermissionCache.php');

require_once(KT_LIB_DIR . "/groups/GroupUtil.php");
require_once(KT_LIB_DIR . "/roles/roleallocation.inc.php");
require_once(KT_LIB_DIR . "/roles/documentroleallocation.inc.php");

require_once(KT_LIB_DIR . "/workflow/workflowutil.inc.php");
require_once(KT_LIB_DIR . "/workflow/workflowstatepermissionsassignment.inc.php");

class KTPermissionUtil {

    static $permArr = array();

    // {{{ generateDescriptor
    /**
     * Generate a unique textual representation of a specific collection
     * of users/groups/roles described by a dictionary.
     *
     * This function _must_ always generate the same descriptor for a
     * given collection of users/groups/roles, no matter the order of
     * the keys or the order of the ids in the values of the collection.
     */
    function generateDescriptor ($aAllowed) {
        $aAllowedSort = array();
        // PHP5: clone
        $aTmp = $aAllowed;
        ksort($aTmp);
        $sOutput = "";
        foreach ($aTmp as $k => $v) {
            if (empty($v)) {
                continue;
            }
            $v = array_unique($v);
            $sOutput .= "$k(";
            sort($v);
            $sOutput .= join(",", $v);
            $sOutput .= ")";
        }

        return $sOutput;
    }
    // }}}

    // {{{ getOrCreateDescriptor
    /**
     * For a given collection of users/groups/roles, get the permission
     * descriptor object that describes that exact collection, creating
     * such an object if it does not already exist.
     */
    function getOrCreateDescriptor ($aAllowed) {
        $sDescriptor = KTPermissionUtil::generateDescriptor($aAllowed);
        $oDescriptor = KTPermissionDescriptor::getByDescriptor(md5($sDescriptor));
        if (PEAR::isError($oDescriptor)) {
            $error = $oDescriptor->getMessage();

            $oDescriptor =& KTPermissionDescriptor::createFromArray(array(
                "descriptortext" => $sDescriptor,
            ));
            if (PEAR::isError($oDescriptor)) {
                global $default;
                $default->log->error('FATAL ERROR! KTPermissionUtil->getOrCreateDescriptor: Cannot create descriptor - ' . $oDescriptor->getMessage() . ' Error on getByDescriptor - ' . $error);
                return false;
            }
            $oDescriptor->saveAllowed($aAllowed);

        }
        return $oDescriptor;
    }
    // }}}

    // {{{ getAllowedForDescriptor
    function getAllowedForDescriptor($oDescriptor) {
        $oDescriptor =& KTUtil::getObject('KTPermissionDescriptor', $oDescriptor);
        return $oDescriptor->getAllowed();
    }
    // }}}

    // {{{ getOrCreateAssignment
    /**
     * For a given permission object, get the assignment object for the
     * given permission, or create one if there isn't one already.
     *
     * This assignment object describes the group of users/groups/roles
     * that have the given permission.  If one is created, it is created
     * empty.
     */
    function getOrCreateAssignment ($sPermission, $iObjectID) {
        if (is_string($sPermission)) {
            $oPermission =& KTPermission::getByName($sPermission);
        } else {
            $oPermission =& $sPermission;
        }
        if (is_numeric($iObjectID)) {
            $oObject =& KTPermissionObject::get($iObjectID);
        } else {
            $oObject =& $iObjectID;
        }
        $oPA = KTPermissionAssignment::getByPermissionAndObject($oPermission, $oObject);
        if (PEAR::isError($oPA)) {
            $oPA = KTPermissionAssignment::createFromArray(array(
                'permissionid' => $oPermission->getID(),
                'permissionobjectid' => $oObject->getID(),
            ));
        }
        return $oPA;
    }
    // }}}

    // {{{ setPermissionForID
    /**
     * For a given permission object, set the given group of
     * users/groups/roles that have a given permission, removing any
     * previous assignment.
     */
    function setPermissionForID($sPermission, $iObjectID, $aAllowed) {
        $oPermissionAssignment = KTPermissionUtil::getOrCreateAssignment($sPermission, $iObjectID);
        $oDescriptor = KTPermissionUtil::getOrCreateDescriptor($aAllowed);
        if ($oDescriptor === false) {
            return false;
        }
        $oPermissionAssignment->setPermissionDescriptorID($oDescriptor->getID());
        $res = $oPermissionAssignment->update();
        return $res;
    }
    // }}}

    // {{{ updatePermissionLookupForState
    function updatePermissionLookupForState($oState) {
        $aDocuments = Document::getByState($oState);
        foreach ($aDocuments as $oDocument) {
            KTPermissionUtil::updatePermissionLookup($oDocument);
        }
    }
    // }}}

    // {{{ updatePermissionLookupForPO
    /**
     * Updates permission lookups for all objects of a certain
     * permission object.
     *
     * It may be that you don't have or want to have the root item for a
     * permission object that you do have and have updates - then use
     * this.
     *
     * @static
     */
    function updatePermissionLookupForPO($oPO) {
        $sWhere = 'permission_object_id = ?';
        $aParams = array($oPO->getID());
        $aFolders =& Folder::getList(array($sWhere, $aParams));

        // init once time those var for speeding up updates
		$oChannel =& KTPermissionChannel::getSingleton();
		$aPermAssigns = KTPermissionAssignment::getByObjectMulti($oPO);
		$aMapPermAllowed = array();

		foreach ($aPermAssigns as $oPermAssign) {
		  $oPermDescriptor = KTPermissionDescriptor::get($oPermAssign->getPermissionDescriptorID());
		  $aGroupIDs = $oPermDescriptor->getGroups();
		  $aUserIDs = array();
		  $aRoleIDs = $oPermDescriptor->getRoles();
		  $aAllowed = array(
							'group' => $aGroupIDs,
							'user' => $aUserIDs,
							'role' => $aRoleIDs,
							);
		  $aMapPermAllowed[$oPermAssign->getPermissionID()] = $aAllowed;
		}

		$aMapPermDesc = array();
		foreach ($aMapPermAllowed as $iPermissionId => $aAllowed) {
		  $oLookupPD = KTPermissionUtil::getOrCreateDescriptor($aAllowed);
		  if ($oLookupPD === false) {
              return false;
          }
		  $aMapPermDesc[$iPermissionId] = $oLookupPD->getID();
		}

		$oPermLookup = KTPermissionLookupAssignment::findOrCreateLookupByPermissionDescriptorMap($aMapPermDesc);
		$aOptions = array('channel' => $oChannel, 'map_allowed' => $aMapPermAllowed, 'perm_lookup' => $oPermLookup);

        if (!PEAR::isError($aFolders)) {
            foreach ($aFolders as $oFolder) {
                KTPermissionUtil::updatePermissionLookup($oFolder, $aOptions);
            }
        }
        $aIds = DBUtil::getResultArrayKey(array("SELECT id FROM documents WHERE permission_object_id=?", $aParams), 'id');
        if (!PEAR::isError($aIds))
        {
			$cache = KTCache::getSingleton();

			foreach ($aIds as $iId)
	        {
    	        $oDocument = Document::get($iId);

    	        if (PEAR::isError($oDocument)) {
    	            $GLOBALS['default']->log->error("Couldn't update document permissions ({$iId}): {$oDocument->getMessage()}");
    	            unset($oDocument);
    	            continue;
    	        }

        	    KTPermissionUtil::updatePermissionLookup($oDocument, $aOptions);

        	    $metadataid = $oDocument->getMetadataVersionId();
				$contentid = $oDocument->getContentVersionId();

				$cache->remove('KTDocumentMetadataVersion/id', $metadataid);
				$cache->remove('KTDocumentContentVersion/id', $contentid);
				$cache->remove('KTDocumentCore/id', $iId);
				$cache->remove('Document/id', $iId);
				unset($GLOBALS['_OBJECTCACHE']['KTDocumentMetadataVersion'][$metadataid]);
				unset($GLOBALS['_OBJECTCACHE']['KTDocumentContentVersion'][$contentid]);
				unset($GLOBALS['_OBJECTCACHE']['KTDocumentCore'][$iId]);

				unset($oDocument);
        	}
        }

        /* *** */

        // Clear the cached permissions to force an update of the cache
        self::clearCache();


        /* *** */

       /* $aDocuments =& Document::getList(array($sWhere, $aParams));
        if (!PEAR::isError($aDocuments)) {
            foreach ($aDocuments as $oDocument) {
                KTPermissionUtil::updatePermissionLookup($oDocument, $aOptions);
            }
        }*/
    }
    // }}}

    // {{{ updatePermissionLookupRecursive
    /**
     * Updates permission lookups for this folder and any ancestors, but
     * only if they use the same permission object.
     *
     * To be used any time a folder permission object is changed.
     */
    function updatePermissionLookupRecursive(&$oDocumentOrFolder) {
        if ($oDocumentOrFolder instanceof Document) {
            // XXX: metadata versions may need attention here
            KTPermissionUtil::updatePermissionLookup($oDocumentOrFolder);
            return;
        }

        $iFolderID = $oDocumentOrFolder->getID();
        $sFolderIDs = Folder::generateFolderIDs($iFolderID);
        $sFolderIDs .= '%';

        $sWhere = 'permission_object_id = ? AND parent_folder_ids LIKE ?';
        $aParams = array($oDocumentOrFolder->getPermissionObjectID(), $sFolderIDs);

        $aFolders =& Folder::getList(array($sWhere, $aParams));
        foreach ($aFolders as $oFolder) {
            KTPermissionUtil::updatePermissionLookup($oFolder);
        }

        $aDocuments =& Document::getList(array($sWhere, $aParams));
        foreach ($aDocuments as $oDocument) {
            KTPermissionUtil::updatePermissionLookup($oDocument);
        }
    }
    // }}}

    // {{{ updatePermissionLookup
    /**
     * Update's the permission lookup on one folder or document,
     * non-recursively.
     */
    function updatePermissionLookup(&$oFolderOrDocument, $aOptions = null) {
        $is_a_folder = ($oFolderOrDocument instanceof Folder) ;
		$is_a_document = ($oFolderOrDocument instanceof Document) || ($oFolderOrDocument instanceof KTDocumentCore) ;

		//ensure that the document shortcut is being updated.
		if ($is_a_document && $oFolderOrDocument->isSymbolicLink()) {
			$oFolderOrDocument->switchToRealCore();
		}

		$oChannel = null;
		$aMapPermAllowed = null;
		$oPermLookup = null;
		if (!is_null($aOptions)) {
		  $oChannel = $aOptions['channel'];
		  $aMapPermAllowed = $aOptions['map_allowed'];
		  $oPermLookup = $aOptions['perm_lookup'];
		}

        if (!$is_a_folder && !$is_a_document) {
		  return ; // we occasionally get handed a PEAR::raiseError.  Just ignore it.
		}


        if (is_null($oChannel)) {
			$oChannel =& KTPermissionChannel::getSingleton();
		}
        if ($is_a_folder) {
            $msg = sprintf("Updating folder %s", join('/', $oFolderOrDocument->getPathArray()));
        } else {
            if ($oFolderOrDocument instanceof Document) {
            	//modify the message to reflect that a shortcut is begin updated
            	if ($oFolderOrDocument->isSymbolicLink()) {
            		$msg = sprintf("Updating shortcut to %s", $oFolderOrDocument->getName());
            	}else{
                	$msg = sprintf("Updating document %s", $oFolderOrDocument->getName());
            	}
            } else {
                $msg = sprintf("Updating document %d", $oFolderOrDocument->getId());
            }
        }
        $oChannel->sendMessage(new KTPermissionGenericMessage($msg));
        //var_dump($msg);
        $iPermissionObjectId = $oFolderOrDocument->getPermissionObjectID();
        if (empty($iPermissionObjectId)) {
            return;
        }

        $oPO = KTPermissionObject::get($iPermissionObjectId);
        if (is_null($aMapPermAllowed)) {

			$aPAs = KTPermissionAssignment::getByObjectMulti($oPO);
			$aMapPermAllowed = array();
			foreach ($aPAs as $oPA) {
				$oPD = KTPermissionDescriptor::get($oPA->getPermissionDescriptorID());
				$aGroupIDs = $oPD->getGroups();
				$aUserIDs = array();
				$aRoleIDs = $oPD->getRoles();
				$aAllowed = array(
								  'group' => $aGroupIDs,
								  'user' => $aUserIDs,
								  'role' => $aRoleIDs,
								  );
				$aMapPermAllowed[$oPA->getPermissionID()] = $aAllowed;
			}
		}

        if (!$is_a_folder) {
            $aDynamicConditions = KTPermissionDynamicCondition::getByPermissionObject($oPO);
            if (!PEAR::isError($aDynamicConditions)) {
                foreach ($aDynamicConditions as $oDynamicCondition) {
                    $iConditionId = $oDynamicCondition->getConditionId();
                    if (KTSearchUtil::testConditionOnDocument($iConditionId, $oFolderOrDocument)) {
                        $iGroupId = $oDynamicCondition->getGroupId();
                        $aPermissionIds = $oDynamicCondition->getAssignment();
                        foreach ($aPermissionIds as $iPermissionId) {
                            $aCurrentAllowed = KTUtil::arrayGet($aMapPermAllowed, $iPermissionId, array());
                            $aCurrentAllowed['group'][] = $iGroupId;
                            $aMapPermAllowed[$iPermissionId] = $aCurrentAllowed;
                        }
                    }
                }
            }
        }

        if (!$is_a_folder) {
            $oState = KTWorkflowUtil::getWorkflowStateForDocument($oFolderOrDocument);
            if (!(PEAR::isError($oState) || is_null($oState) || ($oState == false))) {
                $aWorkflowStatePermissionAssignments = KTWorkflowStatePermissionAssignment::getByState($oState);
                foreach ($aWorkflowStatePermissionAssignments as $oAssignment) {
                    $iPermissionId = $oAssignment->getPermissionId();
                    $iPermissionDescriptorId = $oAssignment->getDescriptorId();

                    $oPD = KTPermissionDescriptor::get($iPermissionDescriptorId);
                    $aGroupIDs = $oPD->getGroups();
                    $aUserIDs = array();
                    $aRoleIDs = $oPD->getRoles();
                    $aAllowed = array(
                        'group' => $aGroupIDs,
                        'user' => $aUserIDs,
                        'role' => $aRoleIDs,
                    );
                    $aMapPermAllowed[$iPermissionId] = $aAllowed;
                }
            }
        }

        // if we have roles:  nearest folder.
        $iRoleSourceFolder = null;
        if ($is_a_document) {
            $iRoleSourceFolder = $oFolderOrDocument->getFolderID();
        } else {
            $iRoleSourceFolder = $oFolderOrDocument->getId();
        }

        // very minor perf win:  map role_id (in context) to PD.
        $_roleCache = array();

        foreach ($aMapPermAllowed as $iPermissionId => $aAllowed) {
            $aAfterRoles = array();
            if (array_key_exists('role', $aAllowed)) {
                foreach ($aAllowed['role'] as $k => $iRoleId) {
                    // store the PD <-> RoleId map

                    // special-case "all" or "authenticated".
                    if (($iRoleId == -3) || ($iRoleId == -4)) {
                        $aAfterRoles[] = $iRoleId;
                        continue;
                    }
                    if (!array_key_exists($iRoleId, $_roleCache)) {
                        $oRoleAllocation = null;
                        if ($is_a_document) {
                            $oRoleAllocation =& DocumentRoleAllocation::getAllocationsForDocumentAndRole($oFolderOrDocument->getId(), $iRoleId);
                            if (PEAR::isError($oRoleAllocation)) { $oRoleAllocation = null; }
                        }
                        // if that's null - not set _on_ the document, then
                        if (is_null($oRoleAllocation)) {
                            $oRoleAllocation =& RoleAllocation::getAllocationsForFolderAndRole($iRoleSourceFolder, $iRoleId);
                        }
                        $_roleCache[$iRoleId] = $oRoleAllocation;
                    }
                    // roles are _not_ always assigned (can be null at root)
                    if (!is_null($_roleCache[$iRoleId])) {
                        $aMapPermAllowed[$iPermissionId]['user'] = kt_array_merge($aMapPermAllowed[$iPermissionId]['user'], $_roleCache[$iRoleId]->getUserIds());
                        $aMapPermAllowed[$iPermissionId]['group'] = kt_array_merge($aMapPermAllowed[$iPermissionId]['group'], $_roleCache[$iRoleId]->getGroupIds());
                        // naturally, roles cannot be assigned roles, or madness follows.
                    }

                    unset($aAllowed['role'][$k]);
                }

            }

            unset($aMapPermAllowed[$iPermissionId]['role']);
            if (!empty($aAfterRoles)) {
                $aMapPermAllowed[$iPermissionId]['role'] = $aAfterRoles;
            }
        }

        /*
        print '<pre>';
        print '=======' . $oFolderOrDocument->getName();
        print '<br />';
        var_dump($aMapPermAllowed);
        print '</pre>';
        */

        //if (is_null($oPermLookup)) {
            $aMapPermDesc = array();
            foreach ($aMapPermAllowed as $iPermissionId => $aAllowed) {
                $oLookupPD = KTPermissionUtil::getOrCreateDescriptor($aAllowed);
                if ($oLookupPD === false) {
                    return false;
                }
                $aMapPermDesc[$iPermissionId] = $oLookupPD->getID();
            }

            $oPermLookup = KTPermissionLookupAssignment::findOrCreateLookupByPermissionDescriptorMap($aMapPermDesc);
        //}

        $oFolderOrDocument->setPermissionLookupID($oPermLookup->getID());
        //$oFolderOrDocument->update();
        if ($oFolderOrDocument instanceof Document) {
            $oFolderOrDocument->updateDocumentCore();
        }
        else {
            $oFolderOrDocument->update();
        }
    }
    // }}}

    // {{{ userHasPermissionOnItem
    /**
     * Check whether a given user has the given permission on the given
     * object, by virtue of a direct or indirect assignment due to the
     * user, its groups, its roles, or the roles assigned to its groups,
     * and so forth.
     *
     * @param int $user The id of the user | The user object
     * @param string $permission The permission string to check eg ktcore.permissions.read | The permission object
     * @param object $oFolderOrDocument The folder or document object on which the user has permissions.
     * @return bool
     */
    function userHasPermissionOnItem($user, $permission, $oFolderOrDocument)
    {
        //KTUtil::startTiming(__FUNCTION__);

        if ($user instanceof User || $user instanceof UserProxy) {
            $user = $user->iId;
        }

        if (!is_numeric($user)) {
            $GLOBALS['default']->log->error('Incorrect user id format received, should be numeric.');
            return false;
        }

        if (isset($_SESSION['adminmode']) && $_SESSION['adminmode'] === true) {
        	if (Permission::userIsSystemAdministrator($user)) {
        		return true;
        	}

        	// If admin mode is set and the user is not a system administrator, check if they are a unit admin
        	$folder = $oFolderOrDocument;
        	if ($oFolderOrDocument instanceof Document || $oFolderOrDocument instanceof DocumentProxy) {
        	    $folder = $oFolderOrDocument->getFolderID();
        	}

        	if (Permission::isUnitAdministratorForFolder($user, $folder)) {
        	    return true;
        	}
        }

        if ($permission instanceof KTPermission) {
            $permission = $permission->getName();
        }

        if (!is_string($permission)) {
            $GLOBALS['default']->log->error('Incorrect permission format received, should be a string.');
            return false;
        }

        if (PEAR::isError($oFolderOrDocument) || $oFolderOrDocument == null) {
            $msg = '';
            if (PEAR::isError($oFolderOrDocument)) {
                $msg = '- '.$oFolderOrDocument->getMessage();
            }

            $GLOBALS['default']->log->error('Error on the folder or document object '.$msg);
            return false;
        }

        $lookup_id = $oFolderOrDocument->getPermissionLookupID();

        // Get the users permissions from cache
	    $cache = PermissionCache::getSingleton();
	    $check = $cache->checkPermission($lookup_id, $permission, $user);
	    //KTUtil::logTiming(__FUNCTION__);
	    return $check;

	    /* *** Old version *** *

        KTUtil::startTiming(__FUNCTION__);

        $oPermission = $permission;
        $oUser = $user;

        if (is_numeric($oUser)) {
            $oUser = User::get($oUser);
        }

        if (is_string($oPermission)) {
             $oPermission =& KTPermission::getByName($oPermission);
        }
        if (PEAR::isError($oPermission)) {
            KTUtil::logTiming(__FUNCTION__);
            return false;
        }
        if (PEAR::isError($oFolderOrDocument) || $oFolderOrDocument == null) {
            KTUtil::logTiming(__FUNCTION__);
            return false;
        }

        // Quick fix for multiple permissions look ups.
        // For the current lookup, if the permissions have been checked then return their value
        $iPermId = $oPermission->getID();
        $iDocId = $oFolderOrDocument->getID();
        $lookup = 'folders';
        if (($oFolderOrDocument instanceof Document) || $oFolderOrDocument instanceof DocumentProxy) {
            $lookup = 'docs';
        }
        // check if permission has been set
        // $permArr[permId] = array('folders' => array('id' => bool), 'docs' => array('id' => bool));
        if (isset(KTPermissionUtil::$permArr[$iPermId][$lookup][$iDocId])) {
            //return KTPermissionUtil::$permArr[$iPermId][$lookup][$iDocId];
        }

        $oPL = KTPermissionLookup::get($oFolderOrDocument->getPermissionLookupID());
        $oPLA = KTPermissionLookupAssignment::getByPermissionAndLookup($oPermission, $oPL);
        if (PEAR::isError($oPLA)) {
            //print $oPL->getID();
            KTPermissionUtil::$permArr[$iPermId][$lookup][$iDocId] = false;
            KTUtil::logTiming(__FUNCTION__);
            return false;
        }
        $oPD = KTPermissionDescriptor::get($oPLA->getPermissionDescriptorID());

        // set permission array to true
        KTPermissionUtil::$permArr[$iPermId][$lookup][$iDocId] = true;

        // check for permissions
        $aGroups = GroupUtil::listGroupsForUserExpand($oUser);
        if ($oPD->hasRoles(array(-3))) {
            KTUtil::logTiming(__FUNCTION__, 'roles');
            return true;
        } // everyone has access.
        else if ($oPD->hasUsers(array($oUser))) {
            KTUtil::logTiming(__FUNCTION__, 'everyone');
            return true;
        }
        else if ($oPD->hasGroups($aGroups)) {
            KTUtil::logTiming(__FUNCTION__, 'groups');
            return true;
        }
        else if ($oPD->hasRoles(array(-4)) && !$oUser->isAnonymous() && $oUser->isLicensed()) {
            KTUtil::logTiming(__FUNCTION__, 'anon');
            return true;
        }

        // permission isn't true, set to false
        KTPermissionUtil::$permArr[$iPermId][$lookup][$iDocId] = false;
        KTUtil::logTiming(__FUNCTION__, 'final');
        return false;
        */
    }
    // }}}

    // {{{ findRootObjectForPermissionObject
    /**
     * Given a specific permission object, find the object (Folder or
     * Document) that is the root of that permission object - the one
     * object that has this permission object, but its parent has a
     * different one.
     */
    function findRootObjectForPermissionObject($oPO) {
        global $default;
        /*
         * If there are any folders with the permission object, then it
         * is set by _a_ folder.  All folders found will have a common
         * ancestor folder, which will be the one with:
         *
         * Potential hack: The shortest parent_folder_ids
         *
         * Potential non-hack: Choose random folder, check parent for
         * permission object recurringly until it changes.  Last success
         * is the ancestor parent...
         */
        $sQuery = "SELECT id FROM $default->folders_table WHERE permission_object_id = ? ORDER BY LENGTH(parent_folder_ids) LIMIT 1";
        $aParams = array($oPO->getID());
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
        if (!is_null($res)) {
            return Folder::get($res);
        }
        $sQuery = "SELECT id FROM $default->documents_table WHERE permission_object_id = ? LIMIT 1";
        $aParams = array($oPO->getID());
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
        if (!is_null($res)) {
            return Document::get($res);
        }
        return false;
    }
    // }}}

    // {{{ copyPermissionObject
    /**
     * Copy the object's parents permission object details, in
     * preparation for the object to have different permissions from its
     * parent.
     */
    function copyPermissionObject(&$oDocumentOrFolder) {
        global $default;
        $oOrigPO = KTPermissionObject::get($oDocumentOrFolder->getPermissionObjectID());
        $aOrigPAs =& KTPermissionAssignment::getByObjectMulti($oOrigPO);
        $oNewPO = KTPermissionObject::createFromArray(array());
        foreach ($aOrigPAs as $oOrigPA) {
            $oNewPA = KTPermissionAssignment::createFromArray(array(
                'permissionid' => $oOrigPA->getPermissionID(),
                'permissionobjectid' => $oNewPO->getID(),
                'permissiondescriptorid' => $oOrigPA->getPermissionDescriptorID(),
            ));
        }
        $oDocumentOrFolder->setPermissionObjectID($oNewPO->getID());
        $oDocumentOrFolder->update();

        // copy any dynamic conditions
        $aDPO = KTPermissionDynamicCondition::getByPermissionObject($oOrigPO);
        foreach ($aDPO as $oOrigDC) {
            $oNewDC = KTPermissionDynamicCondition::createFromArray(array(
                'permissionobjectid' => $oNewPO->getId(),
                'groupid' => $oOrigDC->getGroupId(),
                'conditionid' => $oOrigDC->getConditionId(),
            ));

            $oNewDC->saveAssignment($oOrigDC->getAssignment());
        }

        if (!($oDocumentOrFolder instanceof Folder)) {
            KTPermissionUtil::updatePermissionLookup($oDocumentOrFolder);
            return;
        }

        // For a folder - update permission object for all folders and
        // documents under this current folder if they're using the old
        // permission object id.  If they are, then they're getting the
        // permission object via this folder.  If they are not, then
        // they have their own permission object management, and thus
        // this folder has no effect on their permissions.

        $iFolderID = $oDocumentOrFolder->getID();
        $sFolderIDs = Folder::generateFolderIDs($iFolderID);
        $sFolderIDs .= '%';
        $sQuery = "UPDATE $default->folders_table SET
            permission_object_id = ? WHERE permission_object_id = ? AND
            parent_folder_ids LIKE ?";
        $aParams = array($oNewPO->getID(), $oOrigPO->getID(), $sFolderIDs);
        DBUtil::runQuery(array($sQuery, $aParams));

        Folder::clearAllCaches();

        $sQuery = "UPDATE $default->documents_table SET
            permission_object_id = ? WHERE permission_object_id = ? AND
            (parent_folder_ids LIKE ? OR folder_id = ?)";
        $aParams[] = $iFolderID;
        DBUtil::runQuery(array($sQuery, $aParams));

        Document::clearAllCaches();

        // All objects using this PO must be new and must need their
        // lookups updated...
        return KTPermissionUtil::updatePermissionLookupForPO($oNewPO);
    }
    // }}}

    // {{{ isPermissionOwner
    /**
     * Verify if the given object is the root of the permission object
     * it has assigned to it - in other words, if its parent has a
     * different permission object than it.
     */
    function isPermissionOwner(&$oDocumentOrFolder) {
        $oPermissionObject = KTPermissionObject::get($oDocumentOrFolder->getPermissionObjectID());
        $oParentObject = KTPermissionUtil::findRootObjectForPermissionObject($oPermissionObject);

        // Documents might be permission owner, but then they'd be the
        // only users of that permission object.
        if ($oParentObject instanceof Document) {
            return true;
        }

        // If you're a document and your permission owner isn't a
        // document, that means it's some ancestor, and thus not you.
        if ($oDocumentOrFolder instanceof Document) {
            return false;
        }

        // We're dealing with folders, so just compare IDs...
        if ($oDocumentOrFolder->getID() == $oParentObject->getID()) {
            return true;
        }
        return false;
    }
    // }}}

    // {{{ inheritPermissionObject
    /**
     * Inherits permission object from parent, throwing away our own
     * permission object.
     */
    function inheritPermissionObject(&$oDocumentOrFolder, $aOptions = null) {
        global $default;

        $oDocumentOrFolder->cacheGlobal=array();

        $bEvenIfNotOwner = KTUtil::arrayGet($aOptions, 'evenifnotowner');
        if (empty($bEvenIfNotOwner) && !KTPermissionUtil::isPermissionOwner($oDocumentOrFolder)) {
            return PEAR::raiseError(_kt("Document or Folder doesn't own its permission object"));
        }
        $iOrigPOID = $oDocumentOrFolder->getPermissionObjectID();
        $oOrigPO =& KTPermissionObject::get($iOrigPOID);
        $oFolder =& Folder::get($oDocumentOrFolder->getParentID());
        $iNewPOID = $oFolder->getPermissionObjectID();
        $oNewPO =& KTPermissionObject::get($iNewPOID);


        $oDocumentOrFolder->setPermissionObjectID($iNewPOID);
        $oDocumentOrFolder->update();

        if ($oDocumentOrFolder instanceof Document) {
            // If we're a document, no niggly children to worry about.
            KTPermissionUtil::updatePermissionLookup($oDocumentOrFolder);
            return;
        }

       // if the new and old permission object and lookup ids are the same, then we might as well bail
       if ($iOrigPOID == $iNewPOID)
        {
        	if ($oDocumentOrFolder->getPermissionLookupID() == $oFolder->getPermissionLookupID())
        	{
        		// doing this, as this was done below... (not ideal to copy, but anyways...)
        		Document::clearAllCaches();
        		Folder::clearAllCaches();
        		return;
        	}
        }

        $iFolderID = $oDocumentOrFolder->getID();
        $sFolderIDs = Folder::generateFolderIDs($iFolderID);
        $sFolderIDs .= '%';
        $sQuery = "UPDATE $default->folders_table SET
            permission_object_id = ? WHERE permission_object_id = ? AND
            parent_folder_ids LIKE ?";
        $aParams = array($oNewPO->getID(), $oOrigPO->getID(), $sFolderIDs);
        DBUtil::runQuery(array($sQuery, $aParams));

        Folder::clearAllCaches();

        // Update all documents in the folder and in the sub-folders
        $sQuery = "UPDATE $default->documents_table SET
            permission_object_id = ? WHERE permission_object_id = ? AND
            (parent_folder_ids LIKE ? OR folder_id = ?)";
        $aParams[] = $iFolderID;
        DBUtil::runQuery(array($sQuery, $aParams));

        Document::clearAllCaches();

        return KTPermissionUtil::updatePermissionLookupForPO($oNewPO);
    }
    // }}}

    // {{{ rebuildPermissionLookups
    function rebuildPermissionLookups($bEmptyOnly = true) {
        if ($bEmptyOnly) {
            $sTable = KTUtil::getTableName('folders');
            $sQuery = sprintf("SELECT id FROM %s WHERE permission_lookup_id IS NULL AND permission_object_id IS NOT NULL", $sTable);
        } else {
            $sTable = KTUtil::getTableName('folders');
            $sQuery = sprintf("SELECT id FROM %s WHERE permission_object_id IS NOT NULL", $sTable);
        }
        $aIds = DBUtil::getResultArrayKey($sQuery, 'id');
        foreach ($aIds as $iId) {
            $oFolder =& Folder::get($iId);
            KTPermissionUtil::updatePermissionLookup($oFolder);
        }

        if ($bEmptyOnly) {
            $sTable = KTUtil::getTableName('documents');
            $sQuery = sprintf("SELECT id FROM %s WHERE permission_lookup_id IS NULL", $sTable);
        } else {
            $sTable = KTUtil::getTableName('documents');
            $sQuery = sprintf("SELECT id FROM %s", $sTable);
        }
        $aIds = DBUtil::getResultArrayKey($sQuery, 'id');
        foreach ($aIds as $iId) {
            $oDocument =& Document::get($iId);
            KTPermissionUtil::updatePermissionLookup($oDocument);
        }

    }
    // }}}

    // {{{ getPermissionDescriptorsForUser
    function getPermissionDescriptorsForUser($oUser) {
        $aGroups = GroupUtil::listGroupsForUserExpand($oUser);
        $roles = array(-3); // everyone
        $aEveryoneDescriptors = array();
        $aAuthenticatedDescriptors = array();
        if (!$oUser->isAnonymous()) {
            // authenticated
            $roles[] = -4;
        }
        $aRoleDescriptors = KTPermissionDescriptor::getByRoles($roles, array('ids' => true));
        $aPermissionDescriptors = KTPermissionDescriptor::getByGroups($aGroups, array('ids' => true));
        $aUserDescriptors = KTPermissionDescriptor::getByUser($oUser, array('ids' => true));
        return kt_array_merge($aPermissionDescriptors, $aUserDescriptors, $aRoleDescriptors);
    }
    // }}}

    function updatePermissionObject($folder, $permissionObjectId, $selectedPermissions, $userId)
    {
    	$folderId = $folder->getId();
    	$permissionsList = KTPermission::getList();

        $permissionObject = KTPermissionObject::get($permissionObjectId);
    	
        foreach ($permissionsList as $permission) {
            $permissionId = $permission->getId();

            $allowed = KTUtil::arrayGet($selectedPermissions, $permissionId, array());
            $result = KTPermissionUtil::setPermissionForId($permission, $permissionObject, $allowed);

            if ($result === false) {	
            	global $default;
		    	$msg = (isset($_SESSION["errorMessage"])) ? $_SESSION["errorMessage"] : 'permission set failed';
		    	$default->log->error('Permissions: update failed - ' . $msg);
            	return false;
            }
        }

        // Create the transaction before backgrounding the task - we don't want the permissions to fail because the transaction could not be created.
        $transaction = KTFolderTransaction::createFromArray(array(
            'folderid' => $folderId,
            'comment' => _kt('Updated permissions'),
            'transactionNS' => 'ktcore.transactions.permissions_change',
            'userid' => $userId,
            'ip' => Session::getClientIP(),
            'parentid' => $folder->getParentID(),
        ));
        
        if (PEAR::isError($transaction) || ($transaction === false)) {
        	global $default;
        	$msg = (PEAR::isError($transaction)) ? $transaction->getMessage() : 'transaction insert failed';
        	$default->log->error('Permissions: update failed - ' . $msg);
        	return false;
        }
        
        return true;
    }

    /**
     * Update the permission lookup id for a given permission object
     *
     * @param int $objectId
     */
    static function updatePermissionLookupForObject($objectId, $folderId)
    {
    	global $default;
    	$default->log->info('Permissions: starting update...');
    	
        // Create a mapping of the permission id to the allowed groups, users and roles
        // Create a mapping of the permission id to the descriptor id for the above
		$aPermAssigns = KTPermissionAssignment::getByObjectMulti($objectId);
		$aMapPermAllowed = array();
		$aMapPermDesc = array();

		foreach ($aPermAssigns as $oPermAssign) {
		    $iDescriptorId = $oPermAssign->getPermissionDescriptorID();
		    $iPermissionId = $oPermAssign->getPermissionID();

            $oPermDescriptor = KTPermissionDescriptor::get($iDescriptorId);
            $aGroupIDs = $oPermDescriptor->getGroups();
            $aUserIDs = array();
            $aRoleIDs = $oPermDescriptor->getRoles();
            $aAllowed = array(
            			'group' => $aGroupIDs,
            			'user' => $aUserIDs,
            			'role' => $aRoleIDs,
            			);

            $aMapPermAllowed[$iPermissionId] = $aAllowed;
            $aMapPermDesc[$iPermissionId] = $iDescriptorId;
		}

		$aMapPermLookup = $aMapPermAllowed;

		// Map any roles allocated to the parent folder and include in the allowed groups and users
		$_roleCache = array();
		$role_mappings = array();

        foreach ($aMapPermLookup as $iPermissionId => $aAllowed) {
            $aSystemRoles = array();
            if (array_key_exists('role', $aAllowed)) {
                foreach ($aAllowed['role'] as $k => $iRoleId) {

                    // Check for the system roles: special-case "all" or "authenticated".
                    if (($iRoleId == -3) || ($iRoleId == -4)) {
                        $aSystemRoles[] = $iRoleId;
                        continue;
                    }

                    // If we haven't checked allocations for this role, get them
                    if (!array_key_exists($iRoleId, $_roleCache)) {
                        $oRoleAllocation = RoleAllocation::getAllocationsForFolderAndRole($folderId, $iRoleId);
                        $_roleCache[$iRoleId] = $oRoleAllocation;
                    }

                    // roles are _not_ always assigned (can be null at root)
                    $role_allowed = array();
                    if (!is_null($_roleCache[$iRoleId])) {
                        $role_allowed['user'] = $_roleCache[$iRoleId]->getUserIds();
                        $role_allowed['group'] = $_roleCache[$iRoleId]->getGroupIds();

                        $aMapPermLookup[$iPermissionId]['user'] = kt_array_merge($aMapPermLookup[$iPermissionId]['user'], $role['user']);
                        $aMapPermLookup[$iPermissionId]['group'] = kt_array_merge($aMapPermLookup[$iPermissionId]['group'], $role['group']);
                    }

                    $role_mappings[$iRoleId][$folderId] = $role_allowed;

                    unset($aAllowed['role'][$k]);
                }

            }

            unset($aMapPermLookup[$iPermissionId]['role']);
            if (!empty($aSystemRoles)) {
                $aMapPermLookup[$iPermissionId]['role'] = $aSystemRoles;
            }
        }

        // If the role cache has changed then the permission descriptor mapping has changed - update
        if (!empty($_roleCache)) {
            $aMapPermDesc = array();
            foreach ($aMapPermLookup as $iPermissionId => $aAllowed) {
                $oLookupPD = KTPermissionUtil::getOrCreateDescriptor($aAllowed);
                if ($oLookupPD === false) {
                    return false;
                }
                $aMapPermDesc[$iPermissionId] = $oLookupPD->getID();
            }
        }

		// Use the permission descriptor mapping to find / create a permission lookup id.
		$oPermLookup = KTPermissionLookupAssignment::findOrCreateLookupByPermissionDescriptorMap($aMapPermDesc);
		$lookupId = $oPermLookup->getId();

		// Update the permission lookup id on the folders associated with the permission object
		$res = DBUtil::whereUpdate('folders', array('permission_lookup_id' => $lookupId), array('permission_object_id' => $objectId));

		// Update the permission lookup id on the documents associated with the permission object
		$res = DBUtil::whereUpdate('documents', array('permission_lookup_id' => $lookupId), array('permission_object_id' => $objectId));

		// Note: At this point the selected permissions have been applied to all folders and documents.
		// The following code updates the permissions based on dynamic conditions, workflow and role allocations on sub-folders

		/* *** Check for dynamic conditions that can be applied to the documents *** */

		$aDynamicConditions = KTPermissionDynamicCondition::getByPermissionObject($objectId);

		// Test the dynamic condition on all documents associated with the permission object
        // Create permission mappings for the returned documents

        $conditions_mapping = array();
		if (!PEAR::isError($aDynamicConditions)) {
			$default->log->info('Permissions: applying dynamic conditions');
			
            foreach($aDynamicConditions as $oDynamicCondition) {
                $iConditionId = $oDynamicCondition->getConditionId();

                // Test the condition
                $results = KTSearchUtil::testConditionOnPermissionObject($iConditionId, $objectId);

                // Check if documents are returned in the results
                if (count($results) > 0) {
                    $iGroupId = $oDynamicCondition->getGroupId();
                    $aPermissionIds = $oDynamicCondition->getAssignment();

                    // Create a map of the allowed permissions for the current dynamic condition
                    $aMap = array();
                    foreach ($aPermissionIds as $iPermissionId) {
                        $aAllowed = array();
                        $aAllowed['group'][] = $iGroupId;
                        $aMap[$iPermissionId] = $aAllowed;
                    }

                    // Store for later use
                    $conditions_mapping[$iConditionId] = array('docs' => $results, 'map' => $aMap);
                }
            }
        }

        /* *** Check for documents in workflow states with restricted permissions *** */

        // Get the list of state ids
        // Get the permission assignments for each state

        // Get the workflow states for the permission object
        $states = KTWorkflowUtil::getWorkflowStatesForPermissionObject($objectId);

        $states_mapping = array();

        if ($states) {
        	$default->log->info('Permissions: applying workflow state permissions');
        	
            // Loop through states and get permission assignments
            foreach ($states as $state_id) {
                $aWorkflowStatePermissionAssignments = KTWorkflowStatePermissionAssignment::getByState($state_id);

                $aMap = array();
                foreach ($aWorkflowStatePermissionAssignments as $oAssignment) {
                    $iPermissionId = $oAssignment->getPermissionId();
                    $iPermissionDescriptorId = $oAssignment->getDescriptorId();

                    $oPD = KTPermissionDescriptor::get($iPermissionDescriptorId);
                    $aGroupIDs = $oPD->getGroups();
                    $aUserIDs = array();
                    $aRoleIDs = $oPD->getRoles();
                    $aAllowed = array(
                        'group' => $aGroupIDs,
                        'user' => $aUserIDs,
                        'role' => $aRoleIDs,
                    );

                    // Create permissions map
                    $aMap[$iPermissionId] = $aAllowed;
                }

                $states_mapping[$state_id] = array('docs' => array(), 'map' => $aMap);
            }
        }


        /* *** Check for role allocations on folders with the PO *** */

        // We've already checked roles on the top level folder
        // Find any roles allocated on folders associated with the permission object
        $role_allocations = RoleAllocation::getAllocationsForPO($objectId);
        
        if (!empty($role_allocations)) {
        	$default->log->info('Permissions: applying role allocations');
        	
            foreach ($role_allocations as $role_allocation) {
                $folder_id = $role_allocation['folder_id'];
                $role_id = $role_allocation['role_id'];
                $descriptor_id = $role_allocation['permission_descriptor_id'];

                $oPD = KTPermissionDescriptor::get($descriptor_id);
                $aGroupIDs = $oPD->getGroups();
                $aUserIDs = $oPD->getUsers();
                $aAllowed = array(
                    'group' => $aGroupIDs,
                    'user' => $aUserIDs
                );

                $role_mappings[$role_id][$folder_id] = $aAllowed;
            }
        }

        /* *** Loop through folders and documents and update the permission lookup *** */
        $options = array();
        $options['map'] = $aMapPermAllowed;
        $options['roles'] = $role_mappings;

        // If there are no role allocations then we don't need to loop through the folders
        if (!empty($role_mappings)) {
            $aFolders = KTFolderUtil::getFolderListByPO($objectId);
            foreach ($aFolders as $folder) {
               self::updateFolderPermissionLookup($folder, $options);
            }
        }

        $options['conditions'] = $conditions_mapping;
        $options['states'] = $states_mapping;

        $aDocuments = KTDocumentUtil::getDocumentsByPO($objectId);
        foreach ($aDocuments as $document) {
            self::updateDocumentPermissionLookup($document, $options);
        }

        // Clear the cached permissions to force an update of the cache
        self::clearCache();
        
        $default->log->info('Permissions: clearing cache and finishing...');
    }

    static function getHighestFolder($map, $folders)
    {
        $map_key = array_keys($map);
        $inter = array_intersect($map_key, $folders);

        return max($inter);
    }

    static function updateFolderPermissionLookup($folder, $options)
    {
        $aMapPermAllowed = $options['map'];
        $role_mappings = $options['roles'];

        $folderId = $folder['id'];
        $folderParentIds = $folder['parent_folder_ids'];
        $folderLookupId = $folder['permission_lookup_id'];

        $parents = explode(',', $folderParentIds);
        $parents[] = $folderId;

        // Check for any roles within the permissions and resolve them to users and groups
        foreach ($aMapPermAllowed as $iPermissionId => $aAllowed) {
            $aSystemRoles = array();
            if (array_key_exists('role', $aAllowed)) {
                foreach ($aAllowed['role'] as $k => $iRoleId) {

                    // Check for the system roles: special-case "all" or "authenticated".
                    if (($iRoleId == -3) || ($iRoleId == -4)) {
                        $aSystemRoles[] = $iRoleId;
                        continue;
                    }

                    $maps = $role_mappings[$iRoleId];
                    $key = self::getHighestFolder($maps, $parents);
                    $role_allowed = $maps[$key];

                    // roles are _not_ always assigned (can be null at root)
                    if (!is_null($role_allowed)) {
                        $aMapPermAllowed[$iPermissionId]['user'] = kt_array_merge($aMapPermAllowed[$iPermissionId]['user'], $role_allowed['user']);
                        $aMapPermAllowed[$iPermissionId]['group'] = kt_array_merge($aMapPermAllowed[$iPermissionId]['group'], $role_allowed['group']);
                    }

                    unset($aAllowed['role'][$k]);
                }

                unset($aMapPermAllowed[$iPermissionId]['role']);
                if (!empty($aSystemRoles)) {
                    $aMapPermAllowed[$iPermissionId]['role'] = $aSystemRoles;
                }
            }
        }

        $aMapPermDesc = array();
        foreach ($aMapPermAllowed as $iPermissionId => $aAllowed) {
            $oLookupPD = KTPermissionUtil::getOrCreateDescriptor($aAllowed);
            if ($oLookupPD === false) {
                return false;
            }
            $aMapPermDesc[$iPermissionId] = $oLookupPD->getID();
        }

        $oPermLookup = KTPermissionLookupAssignment::findOrCreateLookupByPermissionDescriptorMap($aMapPermDesc);
        $newLookupId = $oPermLookup->getID();

        if ($newLookupId != $folderLookupId) {
            DBUtil::autoUpdate('folders', array('permission_lookup_id' => $newLookupId), $folderId);
        }
    }

    static function updateDocumentPermissionLookup($document, $options)
    {
        $aMapPermAllowed = $options['map'];
        $conditions_mapping = $options['conditions'];
        $states_mapping = $options['states'];
        $role_mappings = $options['roles'];

        $docId = $document['id'];
        $folderId = $document['folder_id'];
        $folderParentIds = $document['parent_folder_ids'];
        $docLookupId = $document['permission_lookup_id'];

        $parents = explode(',', $folderParentIds);
        $parents[] = $folderId;

        // If there are is no folder id then the document has been deleted
        // Use the restore folder path
        if (empty($folderId)) {
            $parents = explode(',', $document['restore_folder_path']);
        }

        // Check for any dynamic conditions that affect the document
        foreach ($conditions_mapping as $condition) {
            if (in_array($docId, $condition['docs'])) {
                foreach ($condition['map'] as $iPermissionId => $allowed) {
                    // Dynamic conditions are only on groups
                    $aMapPermAllowed[$iPermissionId]['group'] = array_merge($aMapPermAllowed[$iPermissionId]['group'], $allowed['group']);
                }
            }
        }

        // Check whether the document has a workflow state with restricted permissions
        if (is_numeric($document['workflow_state_id'])) {
            $map = $states_mapping[$document['workflow_state_id']]['map'];

            foreach ($map as $iPermissionId => $allowed) {
                $aMapPermAllowed[$iPermissionId] = $allowed;
            }
        }

        // Check for any roles within the permissions and resolve them to users and groups
        foreach ($aMapPermAllowed as $iPermissionId => $aAllowed) {
            $aSystemRoles = array();
            if (array_key_exists('role', $aAllowed)) {
                foreach ($aAllowed['role'] as $k => $iRoleId) {

                    // Check for the system roles: special-case "all" or "authenticated".
                    if (($iRoleId == -3) || ($iRoleId == -4)) {
                        $aSystemRoles[] = $iRoleId;
                        continue;
                    }

                    // Check for the owner role
                    if ($iRoleId == -2) {
                        $ownerId = $document['owner_id'];
                        $aMapPermAllowed[$iPermissionId]['user'][] = $ownerId;
                    }

                    $maps = $role_mappings[$iRoleId];
                    $key = self::getHighestFolder($maps, $parents);
                    $role_allowed = $maps[$key];

                    // roles are _not_ always assigned (can be null at root)
                    if (!is_null($role_allowed)) {
                        $aMapPermAllowed[$iPermissionId]['user'] = kt_array_merge($aMapPermAllowed[$iPermissionId]['user'], $role_allowed['user']);
                        $aMapPermAllowed[$iPermissionId]['group'] = kt_array_merge($aMapPermAllowed[$iPermissionId]['group'], $role_allowed['group']);
                    }

                    unset($aAllowed['role'][$k]);
                }
            }

            unset($aMapPermAllowed[$iPermissionId]['role']);
            if (!empty($aSystemRoles)) {
                $aMapPermAllowed[$iPermissionId]['role'] = $aSystemRoles;
            }
        }

        $aMapPermDesc = array();
        foreach ($aMapPermAllowed as $iPermissionId => $aAllowed) {
            $oLookupPD = KTPermissionUtil::getOrCreateDescriptor($aAllowed);
            if ($oLookupPD === false) {
                return false;
            }
            $aMapPermDesc[$iPermissionId] = $oLookupPD->getID();
        }

        $oPermLookup = KTPermissionLookupAssignment::findOrCreateLookupByPermissionDescriptorMap($aMapPermDesc);
        $newLookupId = $oPermLookup->getID();

        if ($newLookupId != $docLookupId) {
            DBUtil::autoUpdate('documents', array('permission_lookup_id' => $oPermLookup->getID()), $docId);
        }
    }

    /**
     * Clear the cached permissions
     *
     * @static
     * @access public
     */
    static function clearCache()
    {
        // Invalidate the cached permissions to force an update of the cache
        $cache = PermissionCache::getSingleton();
        $cache->invalidateCache();
    }

}

class KTPermissionChannel {
    var $observers = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'KT_PermissionChannel')) {
            $GLOBALS['KT_PermissionChannel'] = new KTPermissionChannel;
        }
        return $GLOBALS['KT_PermissionChannel'];
    }

    function sendMessage(&$msg) {
        foreach ($this->observers as $oObserver) {
            $oObserver->receiveMessage($msg);
        }
    }

    function addObserver(&$obs) {
        array_push($this->observers, $obs);
    }
}

class KTPermissionGenericMessage {
    function KTPermissionGenericMessage($sMessage) {
        $this->sMessage = $sMessage;
    }

    function getString() {
        return $this->sMessage;
    }
}

?>