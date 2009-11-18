<?php
/**
 * $Id$
 *
 * Manages listing and contents for documents on a filesystem.
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

require_once(KT_LIB_DIR . '/import/importstorage.inc.php');
require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');

class KTFSImportStorage extends KTImportStorage {
    function KTFSImportStorage($sBasePath) {
        $this->sBasePath = $sBasePath;
    }

    function init() {
        if (!file_exists($this->sBasePath)) {
            return PEAR::raiseError(_kt("Filesystem location given does not exist"));
        }
        if (!is_dir($this->sBasePath)) {
            return PEAR::raiseError(_kt("Filesystem location given is not a directory"));
        }
    }

    function listDocuments($sFolderPath) {
        $ret = array();
        if (substr($sFolderPath, -1) === "/") {
            $sFolderPath = substr($sFolderPath, 0, -1);
        }
        $sFullPath = sprintf("%s/%s", $this->sBasePath, $sFolderPath);
        if (!is_dir($sFullPath)) {
            return PEAR::raiseError(_kt('Path is not a folder'));
        }
        $rDir = @opendir($sFullPath);
        if ($rDir === false) {
            return PEAR::raiseError(_kt('Failed to open folder'));
        }
        while (($sFilename = readdir($rDir)) !== false) {
            if (in_array($sFilename, array(".", ".."))) {
                continue;
            }
            $sThisPath = sprintf("%s/%s", $sFullPath, $sFilename);
            if (!file_exists($sThisPath)) {
                return PEAR::raiseError(sprintf(_kt('Could not read file: %s') , $sThisPath));
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
            return PEAR::raiseError(_kt('Path is not a folder'));
        }
        $rDir = @opendir($sFullPath);
        if ($rDir === false) {
            return PEAR::raiseError(_kt('Failed to open folder'));
        }
        while (($sFilename = readdir($rDir)) !== false) {
            if (in_array($sFilename, array(".", ".."))) {
                continue;
            }
            $sThisPath = sprintf("%s/%s", $sFullPath, $sFilename);
            if (!file_exists($sThisPath)) {
                return PEAR::raiseError(sprintf(_kt('Could not read file: %s'), $sThisPath));
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
