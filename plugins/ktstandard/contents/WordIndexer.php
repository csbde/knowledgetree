<?php

require_once(KT_DIR . '/plugins/ktstandard/contents/BaseIndexer.php');

class KTWordIndexerTrigger extends KTBaseIndexerTrigger {
    var $mimetypes = array(
       'application/msword' => true,
    );
    var $command = 'catdoc';          // could be any application.
    var $commandconfig = 'indexer/catdoc';          // could be any application.
    var $args = array("-w");
    var $use_pipes = true;
    
    function extract_contents($sFilename, $sTempFilename) {
        if (OS_WINDOWS) {
            $this->command = 'c:\antiword\antiword.exe';
            $this->commandconfig = 'indexer/antiword';
            $this->args = array();
        }
        return parent::extract_contents($sFilename, $sTempFilename);
    }
}

?>
