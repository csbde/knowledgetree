<?php

/**
 * $Id$
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
 */

require_once(KT_DIR . '/plugins/ktstandard/contents/BaseIndexer.php');

class KTOpenDocumentIndexerTrigger extends KTBaseIndexerTrigger {
    var $mimetypes = array(
       'application/octet-stream' => true,
       'application/zip' => true,
       'application/x-zip' => true,
       'application/vnd.oasis.opendocument.text' => true,
       'application/vnd.oasis.opendocument.text-template' => true,
       'application/vnd.oasis.opendocument.presentation' => true,
       'application/vnd.oasis.opendocument.presentation-template' => true,
       'application/vnd.oasis.opendocument.spreadsheet' => true,
       'application/vnd.oasis.opendocument.spreadsheet-template' => true,
    );

    function transform() {
        global $default;
        $iMimeTypeId = $this->oDocument->getMimeTypeId();
        $sMimeType = KTMime::getMimeTypeName($iMimeTypeId);
        $sFileName = $this->oDocument->getFileName();
        $aTestTypes = array('application/octet-stream', 'application/zip', 'application/x-zip');
        if (in_array($sMimeType, $aTestTypes)) {
            $sExtension = KTMime::stripAllButExtension($sFileName);
            $sTable = KTUtil::getTableName('mimetypes');
            $sQuery = "SELECT id, mimetypes FROM $sTable WHERE LOWER(filetypes) = ?";
            $aParams = array($sExtension);
            $aRow = DBUtil::getOneResult(array($sQuery, $aParams));

            if (PEAR::isError($aRow)) {
                $default->log->debug("ODI: error in query: " . print_r($aRow, true));
                return;
            }
            if (empty($aRow)) {
                $default->log->debug("ODI: query returned entry");
                return;
            }

            $id = $aRow['id'];
            $mimetype = $aRow['mimetypes'];
            $default->log->debug("ODI: query returned: " . print_r($aRow, true));

            if (in_array($mimetype, $aTestTypes)) {
                // Haven't changed, really not an OpenDocument file...
                return;
            }
            
            if ($id) {
                $this->oDocument->setMimeTypeId($id);
                $this->oDocument->update();
            }
        }
        parent::transform();
    }

    function getFriendlyCommand() {
        $sUnzipCommand = KTUtil::findCommand("import/unzip", "unzip");
        if (empty($sUnzipCommand)) {
            return false;
        }
        return _kt('Built-in');
    }
    
    function findLocalCommand() {
        $sCommand = KTUtil::findCommand("import/unzip", "unzip");
        return $sCommand;
    }    

    function getDiagnostic() {
        $sCommand = $this->findLocalCommand();
        
        // can't find the local command.
        if (empty($sCommand)) {
            return sprintf(_kt('Unable to find required command for indexing.  Please ensure that <strong>%s</strong> is installed and in the KnowledgeTree Path.  For more information on indexers and helper applications, please <a href="%s">visit the KTDMS site</a>.'), $this->command, $this->support_url);
        }
        
        return null;
    }

    function extract_contents($sFilename, $sTmpFilename) {
        $sUnzipCommand = KTUtil::findCommand("import/unzip", "unzip");
        if (empty($sUnzipCommand)) {
            return;
        }
        $this->sTmpPath = tempnam('/tmp', 'opendocumentextract');
        if ($this->sTmpPath === false) {
            return;
        }
        unlink($this->sTmpPath);
        mkdir($this->sTmpPath, 0700);

        $sCmd = array(
            $sUnzipCommand,
            "-q", "-n",
            "-d", $this->sTmpPath,
            $sFilename,
        );
        KTUtil::pexec($sCmd);

        $sManifest = sprintf("%s/%s", $this->sTmpPath, "META-INF/manifest.xml");
        if (!file_exists($sManifest)) {
            $this->cleanup();
            return;
        }
        $sContentFile = sprintf("%s/%s", $this->sTmpPath, "content.xml");
        if (!file_exists($sContentFile)) {
            $this->cleanup();
            return;
        }

        $sContent = file_get_contents($sContentFile);
        $sContent = preg_replace ("@(</?[^>]*>)+@", " ", $sContent);

        $this->cleanup();
        return $sContent;
    }

    function cleanup() {
        KTUtil::deleteDirectory($this->sTmpPath);
    }
}

?>
