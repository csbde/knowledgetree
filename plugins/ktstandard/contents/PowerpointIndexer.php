<?php

class KTPowerpointIndexerTrigger extends KTBaseIndexerTrigger {
    var $mimetypes = array(
       'application/vnd.ms-powerpoint' => true,
    );
    var $command = 'catppt';          // could be any application.
    var $commandconfig = 'indexer/catppt';          // could be any application.
    var $args = array();
    var $use_pipes = true;
    
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
