<?php
/**
 * $Id$
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
            return sprintf(_kt('Unable to find required command for indexing.  Please ensure that <strong>%s</strong> is installed and in the %s Path.  For more information on indexers and helper applications, please <a href="%s">visit the %s site</a>.'), $this->command, APP_NAME, $this->support_url, APP_NAME);
        }
        
        return null;
    }

    function extract_contents($sFilename, $sTmpFilename) {
        $sUnzipCommand = KTUtil::findCommand("import/unzip", "unzip");
        if (empty($sUnzipCommand)) {
            return;
        }
        $oKTConfig =& KTConfig::getSingleton();
        $sBasedir = $oKTConfig->get("urls/tmpDirectory");
        
        $this->sTmpPath = tempnam($sBasedir, 'opendocumentextract');
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
        KTUtil::pexec($sCmd, array('exec_wait' => 'true'));

        $sManifest = sprintf("%s/%s", $this->sTmpPath, "META-INF/manifest.xml");
        if (OS_WINDOWS) {
    	     $sManifest = str_replace( '/','\\',$sManifest);
    	  } 
        if (!file_exists($sManifest)) {
            $this->cleanup();
            return;
        }
        $sContentFile = sprintf("%s/%s", $this->sTmpPath, "content.xml");
        if (OS_WINDOWS) {
    	     $sContentFile = str_replace( '/','\\',$sContentFile );
    	  } 
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
        return;
        //KTUtil::deleteDirectory($this->sTmpPath);
    }
}

?>
