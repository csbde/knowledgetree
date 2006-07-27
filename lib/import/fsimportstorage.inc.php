<?php /* vim: set expandtab softtabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * Manages listing and contents for documents on a filesystem.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once(KT_LIB_DIR . '/import/importstorage.inc.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');

class KTFSImportStorage extends KTImportStorage {
    function KTFSImportStorage($sBasePath) {
        $this->sBasePath = $sBasePath;
    }

    function init() {
        if (!file_exists($this->sBasePath)) {
            return PEAR::raiseError("Filesystem location given does not exist");
        }
        if (!is_dir($this->sBasePath)) {
            return PEAR::raiseError("Filesystem location given is not a directory");
        }
    }

    function listDocuments($sFolderPath) {
        $ret = array();
        if (substr($sFolderPath, -1) === "/") {
            $sFolderPath = substr($sFolderPath, 0, -1);
        }
        $sFullPath = sprintf("%s/%s", $this->sBasePath, $sFolderPath);
        if (!is_dir($sFullPath)) {
            return PEAR::raiseError('Path is not a folder');
        }
        $rDir = @opendir($sFullPath);
        if ($rDir === false) {
            return PEAR::raiseError('Failed to open folder');
        }
        while (($sFilename = readdir($rDir)) !== false) {
            if (in_array($sFilename, array(".", ".."))) {
                continue;
            }
            $sThisPath = sprintf("%s/%s", $sFullPath, $sFilename);
            if (!file_exists($sThisPath)) {
                return PEAR::raiseError('Could not read file: ' . $sThisPath);
            }
            if (@is_file($sThisPath)) {
                if (empty($sFolderPath)) {
                    $ret[] = $sFilename;
                } else {
                    $ret[] = sprintf("%s/%s", $sFolderPath, $sFilename);
                }
            }
        }
        closedir($rDir);
        return $ret;
    }

    function listFolders($sFolderPath) {
        $ret = array();
        if (substr($sFolderPath, -1) === "/") {
            $sFolderPath = substr($sFolderPath, 0, -1);
        }
        $sFullPath = sprintf("%s/%s", $this->sBasePath, $sFolderPath);
        if (!is_dir($sFullPath)) {
            return PEAR::raiseError('Path is not a folder');
        }
        $rDir = @opendir($sFullPath);
        if ($rDir === false) {
            return PEAR::raiseError('Failed to open folder');
        }
        while (($sFilename = readdir($rDir)) !== false) {
            if (in_array($sFilename, array(".", ".."))) {
                continue;
            }
            $sThisPath = sprintf("%s/%s", $sFullPath, $sFilename);
            if (!file_exists($sThisPath)) {
                return PEAR::raiseError('Could not read file: ' . $sThisPath);
            }
            if (@is_dir($sThisPath)) {
                if (empty($sFolderPath)) {
                    $ret[] = $sFilename;
                } else {
                    $ret[] = sprintf("%s/%s", $sFolderPath, $sFilename);
                }
            }
        }
        closedir($rDir);
        return $ret;
    }

    function getDocumentInfo($sDocumentPath) {
        return new KTImportStorageInfo(
            basename($sDocumentPath),
            array(
                new KTFSFileLike(sprintf("%s/%s", $this->sBasePath, $sDocumentPath))
            )
        );
    }
}

?>
