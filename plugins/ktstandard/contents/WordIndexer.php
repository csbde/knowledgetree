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
    
    function findLocalCommand() {
        if (OS_WINDOWS) {
            $this->command = 'c:\antiword\antiword.exe';
            $this->commandconfig = 'indexer/antiword';
            $this->args = array();
        }
        
        $sCommand = KTUtil::findCommand($this->commandconfig, $this->command);
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
}

?>
