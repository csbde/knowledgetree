<?php /* vim: set expandtab softtabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * Manages listing and contents for documents on a filesystem.
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
            $sThisPath = sprintf("%s/%s", $sFullPath, $sFilename);
            if (is_file($sThisPath)) {
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
            if (is_dir($sThisPath)) {
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
