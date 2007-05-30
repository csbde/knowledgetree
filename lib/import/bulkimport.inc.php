<?php
/**
 * $Id$
 *
 * Import all documents from an import storage location
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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
            if (Folder::folderExistsName(basename($sFolderPath), KTUtil::getId($oFolder))) {
                $_SESSION['KTErrorMessage'][] = sprintf(_kt("The folder %s is already present in %s.  Adding files into pre-existing folder."), $sFolderPath, $oFolder->getName());
                $aOptions = Folder::getList("parent_id = " . KTUtil::getId($oFolder) . ' AND name = "' . DBUtil::escapeSimple($sFolderPath) . '"');
                if (PEAR::isError($aOptions)) { 
                    return $aOptions;
                }
                if (count($aOptions) != 1) {
                    return PEAR::raiseError(sprintf(_kt("Two folders named %s present in %s. Unable to decide which to use..."), $sFolderName, $oFolder->getName()));
                } else {
                    $oThisFolder = $aOptions[0];
                }
            } else {
                $oThisFolder = KTFolderUtil::add($oFolder, basename($sFolderPath), $this->oUser);
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
        if (PEAR::isError($aInfo)) {
            return $aInfo;
        }
        // need to check both of these.
        if (KTDocumentUtil::nameExists($oFolder, basename($sPath))) {
            $_SESSION['KTErrorMessage'][] = sprintf(_kt("The document %s is already present in %s.  Ignoring."), basename($sPath), $oFolder->getName());
            $oDocument =& Document::getByNameAndFolder(basename($sPath), KTUtil::getId($oFolder));
            return $oDocument;            
        } else if (KTDocumentUtil::fileExists($oFolder, basename($sPath))) {
            $_SESSION['KTErrorMessage'][] = sprintf(_kt("The document %s is already present in %s.  Ignoring."), basename($sPath), $oFolder->getName());
            $oDocument =& Document::getByFilenameAndFolder(basename($sPath), KTUtil::getId($oFolder));
            return $oDocument;
        }
        // else
        $aOptions = array(
            // XXX: Multiversion Import
            'contents' => $aInfo->aVersions[0],
            'metadata' => $this->aMetadata,
            'documenttype' => $this->oDocumentType,
        );
        $oDocument =& KTDocumentUtil::add($oFolder, basename($sPath), $this->oUser, $aOptions);
        return $oDocument;
    }
}

?>
