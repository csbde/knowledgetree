<?php

require_once(KT_DIR . '/plugins/ktstandard/contents/BaseIndexer.php');

class KTPdfIndexerTrigger extends KTBaseIndexerTrigger {
    var $mimetypes = array(
       'application/pdf' => true,
    );
    var $command = 'pdftotext';          // could be any application.
    var $args = array("-nopgbrk");
    var $use_pipes = false;
    
    // see BaseIndexer for how the extraction works.
}

?>
