<?php

require_once(KT_DIR . '/plugins/ktstandard/contents/BaseIndexer.php');

class KTPostscriptIndexerTrigger extends KTBaseIndexerTrigger {
    var $mimetypes = array(
       'application/postscript' => true,
    );
    var $command = 'pstotext';          // could be any application.
    var $commandconfig = 'indexer/pstotext';          // could be any application.
    var $args = array();
    var $use_pipes = true;
    
    // see BaseIndexer for how the extraction works.
}

?>
