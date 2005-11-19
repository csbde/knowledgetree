<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * Utilities helpful to traversing the document repository
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Neil Blakey-Milner <nbm@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.browse
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
            $aActions = array_keys(KTDocumentActionUtil::getDocumentActions());
            if (in_array($sFileName, $aActions)) {
                return array($iFolderID, $iDocumentID, $sFileName);
            }
            return false;
        }

        $sQuery = "SELECT id FROM folders WHERE parent_id = ? AND name = ?";
        $aParams = array($iFolderID, $sFileName);
        $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
        if (PEAR::isError($id)) {
            // XXX: log error
            return false;
        }
        if ($id) {
            return array($id, null, null);
        }

        $sQuery = "SELECT id FROM documents WHERE folder_id = ? AND filename = ?";
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
            if ($sFolderName === "") {
                continue;
            }
            $sQuery = "SELECT id FROM folders WHERE parent_id = ? AND name = ?";
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
            $default->log->error("iFolderID set to " . print_r($id, true));
            $iFolderID = (int)$id;
        }

        $sQuery = "SELECT id FROM documents WHERE folder_id = ? AND filename = ?";
        $aParams = array($iFolderID, $sFileName);
        $iDocumentID = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');

        if (PEAR::isError($iDocumentID)) {
            // XXX: log error
            return false;
        }
        
        if ($iDocumentID === null) {
            $sQuery = "SELECT id FROM folders WHERE parent_id = ? AND name = ?";
            $aParams = array($iFolderID, $sFileName);
            $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
            if (PEAR::isError($id)) {
                // XXX: log error
                return false;
            }
            if (is_null($id)) {
                if ($sFileName === "") {
                    return array($iFolderID, null);
                }
                // XXX: log error
                return array($iFolderID, false);
            }
            /* if (substr($path, -1) !== "/") {
                header("Location: " . $_SERVER["PHP_SELF"] . "/");
            } */
            return array($id, null);
        }

        return array($iFolderID, (int)$iDocumentID);
    }
    // }}}

    // {{{ breadcrumbsForFolder
    function breadcrumbsForFolder($oFolder, $aOptions = null) {
        $oFolder =& KTUtil::getObject('Folder', $oFolder);

        $bFinal = KTUtil::arrayGet($aOptions, 'final', true, false);
        $bFolderBrowseBase = KTUtil::arrayGet($aOptions, 'folderbase', "");
        $aBreadcrumbs = array();
        
        // skip root.
        $folder_path_names = array_slice($oFolder->getPathArray(), 1);
        $folder_path_ids = array_slice(explode(',', $oFolder->getParentFolderIds()), 1);

        $parents = count($folder_path_ids);
        $sAction = KTUtil::arrayGet($aOptions, 'folderaction');

        if ($parents != 0) {
            foreach (range(0, $parents - 1) as $index) {
                $id = $folder_path_ids[$index];
                $url = "?fFolderId=" . $id;
                if (!empty($sAction)) {
                    $url = generateControllerUrl($sAction, "fFolderId=" . $id);
                }
                $aBreadcrumbs[] = array("url" => $url, "name" => $folder_path_names[$index]);
            }
        }

        // now add this folder, _if we aren't in 1_.
        if ($oFolder->getId() != 1) {
            if ($bFinal) {
                $aBreadcrumbs[] = array("name" => $oFolder->getName());
            } else {
                $id = $oFolder->getId();
                $url = "?fFolderId=" . $id;
                if (!empty($sAction)) {
                    $url = generateControllerUrl($sAction, "fFolderId=" . $id);
                }
                $aBreadcrumbs[] = array("url" => $url, "name" => $oFolder->getName());
            }
        }

        return $aBreadcrumbs;
    }
    // }}}

    // {{{ breadcrumbsForDocument
    function breadcrumbsForDocument($oDocument, $aOptions = null) {
        $bFinal = KTUtil::arrayGet($aOptions, 'final', true, false);
        $aOptions = KTUtil::meldOptions($aOptions, array(
            "final" => false,
        ));
        $iFolderId = $oDocument->getFolderId();
        $aBreadcrumbs = KTBrowseUtil::breadcrumbsForFolder($iFolderId, $aOptions);
        $sAction = KTUtil::arrayGet($aOptions, 'documentaction');
        $url = "?fDocumentId=" . $oDocument->getId();
        if (!empty($sAction)) {
            $url = generateControllerUrl($sAction, "fDocumentId=" .  $oDocument->getId());
        }

        if ($bFinal) {
            $aBreadcrumbs[] = array("name" => $oDocument->getName());
        } else {
            $aBreadcrumbs[] = array("url" => $url, "name" => $oDocument->getName());
        }
        return $aBreadcrumbs;
    }
    // }}}
}
