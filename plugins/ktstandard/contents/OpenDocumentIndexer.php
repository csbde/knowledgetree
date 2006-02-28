<?php

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
        $iMimeTypeId = $this->oDocument->getMimeTypeId();
        $sMimeType = KTMime::getMimeTypeName($iMimeTypeId);
        $sFileName = $this->oDocument->getFileName();
        if (in_array($sMimeType, array('application/octet-stream', 'application/zip', 'application/x-zip'))) {
            $sExtension = KTMime::stripAllButExtension($sFileName);
            $sTable = KTUtil::getTableName('mimetypes');
            $sQuery = "SELECT id FROM $sTable WHERE LOWER(filetypes) = ?";
            $aParams = array($sExtension);
            $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
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
        return _('Built-in');
    }
    
    function findLocalCommand() {
        $sCommand = KTUtil::findCommand("import/unzip", "unzip");
        return $sCommand;
    }    

    function getDiagnostic() {
        $sCommand = $this->findLocalCommand();
        
        // can't find the local command.
        if (empty($sCommand)) {
            return sprintf(_('Unable to find required command for indexing.  Please ensure that <strong>%s</strong> is installed and in the KnowledgeTree Path.  For more information on indexers and helper applications, please <a href="%s">visit the KTDMS site</a>.'), $this->command, $this->support_url);
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
