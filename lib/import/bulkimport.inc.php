<?php /* vim: set expandtab softtabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * Import all documents from an import storage location
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
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
            $oThisFolder = KTFolderUtil::add($oFolder, basename($sFolderPath), $this->oUser);
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
