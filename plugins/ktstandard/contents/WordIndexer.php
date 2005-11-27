<?php

class KTWordIndexerTrigger {
    function setDocument($oDocument) {
        $this->oDocument = $oDocument;
    }

    function transform() {
        $oStorage = KTStorageManagerUtil::getSingleton();
        $sFile = $oStorage->temporaryFile($this->oDocument);

        $cmdline = array("catdoc", $sFile);
        $myfilename = tempnam("/tmp", "kt.catdoc");
        $command = KTUtil::safeShellString($cmdline) . " >> " . $myfilename;
        system($command);
        $contents = file_get_contents($myfilename);
        $aInsertValues = array(
            'document_id' => $this->oDocument->getId(),
            'document_text' => $contents,
        );
        $sTable = KTUtil::getTableName('document_text');
        DBUtil::autoInsert($sTable, $aInsertValues, array('noid' => true));
    }
}

?>
