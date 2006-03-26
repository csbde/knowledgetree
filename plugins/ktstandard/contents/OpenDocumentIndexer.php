<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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
