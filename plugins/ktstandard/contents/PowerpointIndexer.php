<?php

class KTPowerpointIndexerTrigger {
    function setDocument($oDocument) {
        $this->oDocument = $oDocument;
    }

    function transform() {
        $iMimeTypeId = $this->oDocument->getMimeTypeId();
        $sMimeType = KTMime::getMimeTypeName($iMimeTypeId);
        if ($sMimeType != "application/msword") {
            return;
        }

        $oStorage = KTStorageManagerUtil::getSingleton();
        $sFile = $oStorage->temporaryFile($this->oDocument);

        $cmdline = array("catppt", $sFile);
        $myfilename = tempnam("/tmp", "kt.catppt");
        $command = KTUtil::safeShellString($cmdline) . " >> " . $myfilename;
        system($command);
        $contents = file_get_contents($myfilename);
        unlink($myfilename);
        if (empty($contents)) {
            return;
        }
        $aInsertValues = array(
            'document_id' => $this->oDocument->getId(),
            'document_text' => $contents,
        );
        $sTable = KTUtil::getTableName('document_text');
        DBUtil::autoInsert($sTable, $aInsertValues, array('noid' => true));
    }
}

?>
