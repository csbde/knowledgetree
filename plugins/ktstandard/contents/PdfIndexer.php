<?php

require_once(KT_DIR . '/plugins/ktstandard/contents/BaseIndexer.php');

class KTPdfIndexerTrigger extends KTBaseIndexerTrigger {
    var $mimetypes = array(
       'application/pdf' => true,
    );
    var $command = 'pdftotext';          // could be any application.
    var $commandconfig = 'indexer/pdftotext';          // could be any application.
    var $args = array("-nopgbrk");
    var $use_pipes = false;
    
    // see BaseIndexer for how the extraction works.
    function findLocalCommand() {   
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
