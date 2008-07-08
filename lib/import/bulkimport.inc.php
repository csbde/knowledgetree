<?php
/**
 * $Id$
 *
 * Import all documents from an import storage location
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
 */

require_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/filelike/filelikeutil.inc.php');

class KTBulkImportManager {
    var $oStorage;

    function KTBulkImportManager($oFolder, $oStorage, $oUser, $aOptions = null) {
        $this->oFolder =& $oFolder;
        $this->oStorage =& $oStorage;
        $this->oUser =& $oUser;
        $this->aOptions =& $aOptions;
        if (is_null($aOptions)) {
            $aOptions = array();
        }
        $this->aMetadata = KTUtil::arrayGet($aOptions, 'metadata', array());
        $this->oDocumentType = KTUtil::arrayGet($aOptions, 'documenttype', null); // DocUtil::_add will do the right thing.
    }

    function import() {
        $res = $this->oStorage->init();
        if (PEAR::isError($res)) {
            $this->oStorage->cleanup();
            return $res;
        }
        $res = $this->_importfolder($this->oFolder, "/");
        if (PEAR::isError($res)) {
            $this->oStorage->cleanup();
            return $res;
        }
        $this->oStorage->cleanup();
        return;
    }

    function _importfolder($oFolder, $sPath) {
        $oPermission = KTPermission::getByName('ktcore.permissions.addFolder');

        $aDocPaths = $this->oStorage->listDocuments($sPath);
        if (PEAR::isError($aDocPaths)) {
            return $aDocPaths;
        }
        foreach ($aDocPaths as $sDocumentPath) {
            $res = $this->_importdocument($oFolder, $sDocumentPath);
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        $aFolderPaths = $this->oStorage->listFolders($sPath);
        if (PEAR::isError($aFolderPaths)) {
            return $aFolderPaths;
        }
        foreach ($aFolderPaths as $sFolderPath) {
            if (Folder::folderExistsName(utf8_encode(basename($sFolderPath)), KTUtil::getId($oFolder))) {
                $_SESSION['KTErrorMessage'][] = sprintf(_kt("The folder %s is already present in %s.  Adding files into pre-existing folder."), utf8_encode(basename($sFolderPath)), $oFolder->getName());
                $aOptions = Folder::getList("parent_id = " . KTUtil::getId($oFolder) . ' AND name = "' . DBUtil::escapeSimple(utf8_encode(basename($sFolderPath))) . '"');
                if (PEAR::isError($aOptions)) {
                    return $aOptions;
                }
                if (count($aOptions) != 1) {
                    return PEAR::raiseError(sprintf(_kt("Two folders named %s present in %s. Unable to decide which to use..."), $sFolderName, $oFolder->getName()));
                } else {
                    $oThisFolder = $aOptions[0];
                }
            } else {

                if(KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $oFolder))
        		{
                	$oThisFolder = KTFolderUtil::add($oFolder, utf8_encode(basename($sFolderPath)), $this->oUser);
        		}
        		else
        		{
        			$oThisFolder = $oFolder;
        			if(!in_array('Your documents have been added to this folder and not the folder structure within the upload file because you do not have permission to add any folders.',$_SESSION['KTErrorMessage']))
        			{
        				$_SESSION['KTErrorMessage'][] = sprintf(_kt('Your documents have been added to this folder and not the folder structure within the upload file because you do not have permission to add any folders.'));
        			}
        		}
            }
            if (PEAR::isError($oThisFolder)) {
                return $oThisFolder;
            }

            $res = $this->_importfolder($oThisFolder, $sFolderPath);

            if (PEAR::isError($res)) {
                return $res;
            }
        }
    }

    function _importdocument($oFolder, $sPath) {
        $aInfo = $this->oStorage->getDocumentInfo($sPath);
        $sTmpFileName = sprintf("%s/%s", $this->oStorage->sBasePath, $sPath);
        if (PEAR::isError($aInfo)) {
            return $aInfo;
        }
        // need to check both of these.
        /*if (KTDocumentUtil::nameExists($oFolder, utf8_encode(basename($sPath)))) {
            $_SESSION['KTErrorMessage'][] = sprintf(_kt("The document %s is already present in %s.  Ignoring."), utf8_encode(basename($sPath)), $oFolder->getName());
            $oDocument =& Document::getByNameAndFolder(utf8_encode(basename($sPath)), KTUtil::getId($oFolder));
            return $oDocument;
        } else if (KTDocumentUtil::fileExists($oFolder, utf8_encode(basename($sPath)))) {
            $_SESSION['KTErrorMessage'][] = sprintf(_kt("The document %s is already present in %s.  Ignoring."), utf8_encode(basename($sPath)), $oFolder->getName());
            $oDocument =& Document::getByFilenameAndFolder(utf8_encode(basename($sPath)), KTUtil::getId($oFolder));
            return $oDocument;
        }*/
        // else
        $aOptions = array(
            // XXX: Multiversion Import
            //'contents' => $aInfo->aVersions[0],
            'temp_file' => $sTmpFileName,
            'metadata' => $this->aMetadata,
            'documenttype' => $this->oDocumentType,
        );
        $aOptions = array_merge($aOptions, $this->aOptions);
        $oDocument =& KTDocumentUtil::add($oFolder, utf8_encode(basename($sPath)), $this->oUser, $aOptions);
        return $oDocument;
    }
}

?>
