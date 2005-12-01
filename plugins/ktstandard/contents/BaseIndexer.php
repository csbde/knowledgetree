<?php

class KTBaseIndexerTrigger { 
    var $mimetypes = array(
       'text/plain' => true,
    );
    var $command = 'catdoc';          // could be any application.
    var $args = array("-w");
    var $use_pipes = true;

    function setDocument($oDocument) {
        $this->oDocument = $oDocument;
    }

    function transform() {
        $iMimeTypeId = $this->oDocument->getMimeTypeId();
        $sMimeType = KTMime::getMimeTypeName($iMimeTypeId);
        if (!array_key_exists($sMimeType, $this->mimetypes)) {
            return;
        }

        $oStorage = KTStorageManagerUtil::getSingleton();
        $sFile = $oStorage->temporaryFile($this->oDocument);

        $tempstub = 'transform';
        if ($this->command != null) {
            $tempstub = $this->command;
        }
        $myfilename = tempnam("/tmp", 'kt.' . $tempstub);
        $contents = $this->extract_contents($sFile, $myfilename);
        
        unlink($myfilename);
        if (empty($contents)) {
            return;
        }
        $aInsertValues = array(
            'document_id' => $this->oDocument->getId(),
            'document_text' => $contents,
        );
        $sTable = KTUtil::getTableName('document_text');
        
        // clean up the document query "stuff".
        // FIXME this suggests that we should move the _old_ document_searchable_text across to the old-document's id if its a checkin.
        DBUtil::runQuery(array('DELETE FROM ' . $sTable . ' WHERE document_id = ?', array($this->oDocument->getId())));
        DBUtil::autoInsert($sTable, $aInsertValues, array('noid' => true));
        
    }
    
    // handles certain, _very_ simple reader types.
    function extract_contents($sFilename, $sTempFilename) {
        $cmdline = array($this->command);
        $cmdline = array_merge($cmdline, $this->args);
        $cmdline[] = $sFilename;
        
        if ($this->use_pipes) {
            $command = KTUtil::safeShellString($cmdline) . " >> " . $sTempFilename;
        } else {
            $cmdline[] = $sTempFilename;
            
            $command = KTUtil::safeShellString($cmdline);
        
        }
        system($command);
        $contents = file_get_contents($sTempFilename);
        
        return $contents;
    }
}

?>