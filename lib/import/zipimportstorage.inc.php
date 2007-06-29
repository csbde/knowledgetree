<?php
/**
 * $Id$
 *
 * Manages listing and contents for documents uploaded from a zip file
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

require_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
require_once(KT_LIB_DIR . '/import/fsimportstorage.inc.php');

class KTZipImportStorage extends KTFSImportStorage {
    function KTZipImportStorage($sZipPath) {
        $this->sZipPath = $sZipPath;
    }

    function init() {
        $oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");    
    
        $sTmpPath = tempnam($sBasedir, 'zipimportstorage');
        if ($sTmpPath === false) {
            return PEAR::raiseError(_kt("Could not create temporary directory for zip storage"));
        }
        if (!file_exists($this->sZipPath)) {
            return PEAR::raiseError(_kt("Zip file given does not exist"));
        }
        unlink($sTmpPath);
        mkdir($sTmpPath, 0700);
        $this->sBasePath = $sTmpPath;
        $sUnzipCommand = KTUtil::findCommand("import/unzip", "unzip");
        if (empty($sUnzipCommand)) {
            return PEAR::raiseError(_kt("unzip command not found on system"));
        }
        $aArgs = array(
            $sUnzipCommand,
            "-q", "-n",
            "-d", $sTmpPath,
            $this->sZipPath,
        );
        $aRes = KTUtil::pexec($aArgs);

        if ($aRes['ret'] !== 0) {
            return PEAR::raiseError(_kt("Could not retrieve contents from zip storage"));
        }
    }

    function cleanup() {
        if ($this->sBasePath && file_exists($this->sBasePath)) {
            KTUtil::deleteDirectory($this->sBasePath);
            $this->sBasePath = null;
        }
    }
}

?>
