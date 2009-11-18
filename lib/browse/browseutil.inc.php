<?php
/**
 * $Id$
 *
 * Utilities helpful to traversing the document repository
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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
 */

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');

class KTBrowseUtil {
    // {{{ folderOrDocument
    function folderOrDocument($sPath, $bAction = false) {
        $sFileName = basename($sPath);
        $sFolderPath = dirname($sPath);

        $aFolderInfo = KTBrowseUtil::_folderOrDocument($sFolderPath);

        if ($aFolderInfo === false) {
            return $aFolderInfo;
        }

        list($iFolderID, $iDocumentID) = $aFolderInfo;

        if ($iDocumentID && $bAction) {
            $aActions = array_keys(KTDocumentActionUtil::getAllDocumentActions());
            if (in_array($sFileName, $aActions)) {
                return array($iFolderID, $iDocumentID, $sFileName);
            }
            return false;
        }

        $sQuery = 'SELECT id FROM folders WHERE parent_id = ? AND name = ?';
        $aParams = array($iFolderID, $sFileName);
        $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
        if (PEAR::isError($id)) {
            // XXX: log error
            return false;
        }
        if ($id) {
            return array($id, null, null);
        }

        $sQuery = sprintf('SELECT d.id FROM %s AS d' .
        ' LEFT JOIN %s AS dm ON (d.metadata_version_id = dm.id) LEFT JOIN %s AS dc ON (dm.content_version_id = dc.id)' .
        ' WHERE d.folder_id = ? AND dc.filename = ?',
        KTUtil::getTableName(documents),
        KTUtil::getTableName('document_metadata_version'),
        KTUtil::getTableName('document_content_version'));
        $aParams = array($iFolderID, $sFileName);
        $iDocumentID = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');

        if (PEAR::isError($iDocumentID)) {
            // XXX: log error
            return false;
        }

        if ($iDocumentID) {
            return array($iFolderID, $iDocumentID, null);
        }

        if ($bAction) {
         // $aActions = array_keys(KTFolderAction::getFolderActions());
            $aActions = array('ktcore.delete');
            if (in_array($sFileName, $aActions)) {
                return array($iFolderID, null, $sFileName);
            }
        }
        return false;
    }

    function _folderOrDocument($sPath) {
        global $default;
        $sFileName = basename($sPath);
        $sFolderPath = dirname($sPath);

        $aFolderNames = split('/', $sFolderPath);

        $iFolderID = 0;

        $aRemaining = $aFolderNames;
        while (count($aRemaining)) {
            $sFolderName = $aRemaining[0];
            $aRemaining = array_slice($aRemaining, 1);
            if ($sFolderName === '') {
                continue;
            }
            $sQuery = 'SELECT id FROM folders WHERE parent_id = ? AND name = ?';
            $aParams = array($iFolderID, $sFolderName);
            $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
            if (PEAR::isError($id)) {
                // XXX: log error
                return false;
            }
            if (is_null($id)) {
                // Some intermediary folder path doesn't exist
                return false;
            }
            $default->log->error('iFolderID set to ' . print_r($id, true));
            $iFolderID = (int)$id;
        }

        $sQuery = sprintf('SELECT d.id FROM %s AS d' .
        ' LEFT JOIN %s AS dm ON (d.metadata_version_id = dm.id) LEFT JOIN %s AS dc ON (dm.content_version_id = dc.id)' .
        ' WHERE d.folder_id = ? AND dc.filename = ?',
        KTUtil::getTableName(documents),
        KTUtil::getTableName('document_metadata_version'),
        KTUtil::getTableName('document_content_version'));
        $aParams = array($iFolderID, $sFileName);
        $iDocumentID = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');

        if (PEAR::isError($iDocumentID)) {
            // XXX: log error
            return false;
        }

        if ($iDocumentID === null) {
            $sQuery = 'SELECT id FROM folders WHERE parent_id = ? AND name = ?';
            $aParams = array($iFolderID, $sFileName);
            $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');

            if (PEAR::isError($id)) {
                // XXX: log error
                return false;
            }
            if (is_null($id)) {
                if ($sFileName === '') {
                    return array($iFolderID, null);
                }
                // XXX: log error
                return array($iFolderID, false);
            }
            return array($id, null);
        }

        return array($iFolderID, (int)$iDocumentID);
    }
    // }}}

    // {{{ breadcrumbsForFolder
    function breadcrumbsForFolder($oFolder, $aOptions = null) {
        $oFolder =& KTUtil::getObject('Folder', $oFolder);
        $sAction = KTUtil::arrayGet($aOptions, 'folderaction');

	if(PEAR::isError($oFolder)) {
	    $url = KTUtil::addQueryStringSelf('fFolderId=1');
	    if(!empty($sAction)) {
		$url = generateControllerUrl($sAction, 'fFolderId=1');
	    }
	    return array( array( 'url'=> $url, 'name' => '&hellip;'));
	}


        $bFinal = KTUtil::arrayGet($aOptions, 'final', true, false);
        $bFolderBrowseBase = KTUtil::arrayGet($aOptions, 'folderbase', '');
        $aBreadcrumbs = array();

        // skip root.
        //$folder_path_names = $oFolder->getPathArray();
        $folder_path_ids = array_slice(explode(',', $oFolder->getParentFolderIds()), 1);

        $parents = count($folder_path_ids);

        // we have made the "default" folder non-root, so we need to be able
        // to reach "Root" (Folder::get(1)).
        $url = KTUtil::addQueryStringSelf('fFolderId=1');
        if (!empty($sAction)) {
            $url = generateControllerUrl($sAction, 'fFolderId=1');
        }
        $aBreadcrumbs[] = array('url' => $url, 'name' => _kt('Folders'));
        $oUser = User::get($_SESSION['userID']);

        if ($parents != 0) {
            foreach (range(0, $parents - 1) as $index) {
                $id = $folder_path_ids[$index];
                $oThisFolder = Folder::get($id);
                $sFolderName = $oThisFolder->getName();
                $url = KTUtil::addQueryStringSelf('fFolderId=' . $id);
                if (!empty($sAction)) {
                    $url = generateControllerUrl($sAction, 'fFolderId=' . $id);
                }
                if (!KTPermissionUtil::userHasPermissionOnItem($oUser, 'ktcore.permissions.folder_details', $oThisFolder)) {
                    if (KTBrowseUtil::inAdminMode($oUser, $oThisFolder)) {
                        $aBreadcrumbs[] = array('url' => $url, 'name' => sprintf('(%s)', $sFolderName));
                    } else {
                        $aBreadcrumbs[] = array('name' => '...');
                    }
                    continue;
                }
                $aBreadcrumbs[] = array('url' => $url, 'name' => $sFolderName);
            }
        }

        // now add this folder, _if we aren't in 1_.
        if ($oFolder->getId() != 1) {
            $id = $oFolder->getId();
            $url = KTUtil::addQueryStringSelf('fFolderId=' . $id);
            if (!empty($sAction)) {
                $url = generateControllerUrl($sAction, 'fFolderId=' . $id);
            }
            if (!KTPermissionUtil::userHasPermissionOnItem($oUser, 'ktcore.permissions.folder_details', $oFolder)) {
                if (KTBrowseUtil::inAdminMode($oUser, $oFolder)) {
                    $aBreadcrumbs[] = array('url' => $url, 'name' => sprintf('(%s)', $oFolder->getName()));
                } else {
                    $aBreadcrumbs[] = array('name' => '...');
                }
            } else if ($bFinal) {
                $aBreadcrumbs[] = array('name' => $oFolder->getName());
            } else {
                $aBreadcrumbs[] = array('url' => $url, 'name' => $oFolder->getName());
            }
        }

        $aCompactBreadcrumbs = array();
        $lastdotdotdot = false;
        foreach ($aBreadcrumbs as $aBreadcrumb) {
            if ($aBreadcrumb['name'] == '...') {
                if ($lastdotdotdot == true) {
                    continue;
                }
                $lastdotdotdot = true;
            } else {
                $lastdotdotdot = false;
            }
            $aCompactBreadcrumbs[] = $aBreadcrumb;
        }

        return $aCompactBreadcrumbs;
    }
    // }}}

    // {{{ breadcrumbsForDocument
    function breadcrumbsForDocument($oDocument, $aOptions = null, $iFolderId = null) {
        $bFinal = KTUtil::arrayGet($aOptions, 'final', true, false);
        $aOptions = KTUtil::meldOptions($aOptions, array(
            'final' => false,
        ));

        if($iFolderId == null){
       	 	$iFolderId = $oDocument->getFolderId();
        }
        $aBreadcrumbs = KTBrowseUtil::breadcrumbsForFolder($iFolderId, $aOptions);


        $sAction = KTUtil::arrayGet($aOptions, 'documentaction');
        $url = KTUtil::addQueryStringSelf('fDocumentId=' . $oDocument->getId());
        if (!empty($sAction)) {
            $url = generateControllerUrl($sAction, 'fDocumentId=' .  $oDocument->getId());
        }

        if ($bFinal) {
            $aBreadcrumbs[] = array('name' => $oDocument->getName());
        } else {
            $aBreadcrumbs[] = array('url' => $url, 'name' => $oDocument->getName());
        }
        return $aBreadcrumbs;
    }
    // }}}

    // {{{ getUrlForFolder
    function getUrlForFolder($oFolder) {
        $iFolderId = KTUtil::getId($oFolder);
        $sExt = '.php';
        if (KTUtil::arrayGet($_SERVER, 'kt_no_extensions')) {
            $sExt = '';
        }
        return sprintf('%s/browse%s?fFolderId=%d', $GLOBALS['KTRootUrl'], $sExt, $iFolderId);
    }
    // }}}

    // {{{ getUrlForDocument
    function getUrlForDocument($oDocument) {
        $iDocumentId = KTUtil::getId($oDocument);
        $sExt = '.php';
        if (KTUtil::arrayGet($_SERVER, 'kt_no_extensions')) {
            $sExt = '';
        }
        return sprintf('%s/view%s?fDocumentId=%d', $GLOBALS['KTRootUrl'], $sExt, $iDocumentId);
    }
    // }}}



    function buildBaseUrl($sPage) {
        $sExt = '.php';
        if (KTUtil::arrayGet($_SERVER, 'kt_no_extensions')) {
            $sExt = '';
        }
        return sprintf('%s/%s%s', $GLOBALS['KTRootUrl'], $sPage, $sExt);
    }
    // }}}



    // {{{ getBrowseBaseUrl
    function getBrowseBaseUrl() {
        return KTBrowseUtil::buildBaseUrl('browse');
    }
    // }}}

    // {{{ getViewBaseUrl
    function getViewBaseUrl() {
        return KTBrowseUtil::buildBaseUrl('view');
    }
    // }}}

    // {{{ getActionBaseUrl
    function getActionBaseUrl() {
        return KTBrowseUtil::buildBaseUrl('action');
    }
    // }}}

    function getSimpleSearchBaseUrl() {
        return KTBrowseUtil::buildBaseUrl('search/simpleSearch');
    }
    function getBooleanSearchBaseUrl() {
        return KTBrowseUtil::buildBaseUrl('search/booleanSearch');
    }
    function getSearchResultURL() {
        return KTBrowseUtil::buildBaseUrl('search2');
    }
    // {{{ inAdminMode
    /**
     * Determines whether the user is in administrator mode, including
     * whether the user is in the unit for which it is unit
     * administrator.
     */
    function inAdminMode($oUser, $oFolder) {
        if (KTUtil::arrayGet($_SESSION, 'adminmode', false) !== true) {
            return false;
        }

        if (Permission::userIsSystemAdministrator($oUser)) {
            return true;
        }

        return Permission::isUnitAdministratorForFolder($oUser, $oFolder);
    }
    // }}}

    // {{{ getBrowseableFolders
    /**
     * Finds folders that aren't reachable by the user but to which the
     * user has read permissions.
     *
     * Returns an array of Folder objects.
     */
    function getBrowseableFolders($oUser) {
        $aPermissionDescriptors = KTPermissionUtil::getPermissionDescriptorsForUser($oUser);


        if (empty($aPermissionDescriptors)) {
            return array();
        }
        $sPermissionDescriptors = DBUtil::paramArray($aPermissionDescriptors);

        $oPermission = KTPermission::getByName('ktcore.permissions.read');
        $oPermission2 = KTPermission::getByName('ktcore.permissions.folder_details');
        $aPermissionIds = array($oPermission->getId(), $oPermission->getId(), $oPermission2->getId(), $oPermission2->getId());

        $sFoldersTable = KTUtil::getTableName('folders');
        $sPLTable = KTUtil::getTableName('permission_lookups');
        $sPLATable = KTUtil::getTableName('permission_lookup_assignments');
        $sQuery = "SELECT DISTINCT F.id AS id FROM
            $sFoldersTable AS F
                LEFT JOIN $sPLTable AS PL ON F.permission_lookup_id = PL.id
                LEFT JOIN $sPLATable AS PLA ON PLA.permission_lookup_id = PL.id AND (PLA.permission_id = ? || PLA.permission_id = ?)

            LEFT JOIN $sFoldersTable AS F2 ON F.parent_id = F2.id
                LEFT JOIN $sPLTable AS PL2 ON F2.permission_lookup_id = PL2.id
                LEFT JOIN $sPLATable AS PLA2 ON PLA2.permission_lookup_id = PL2.id AND (PLA2.permission_id = ? || PLA.permission_id = ?)
            WHERE
                PLA.permission_descriptor_id IN ($sPermissionDescriptors)
                AND F2.id <> 1
                AND NOT (PLA2.permission_descriptor_id IN ($sPermissionDescriptors))";
        $aParams = kt_array_merge($aPermissionIds, $aPermissionDescriptors, $aPermissionDescriptors);
        $res = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');

        if (PEAR::isError($res)) {
            return $res;
        }
        $aFolders = array();
        foreach ($res as $iFolderId) {
            $aFolders[] = Folder::get($iFolderId);
        }
        return $aFolders;
    }
    // }}}

}
