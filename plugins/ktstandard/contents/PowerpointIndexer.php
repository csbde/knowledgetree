<?php

class KTPowerpointIndexerTrigger extends KTBaseIndexerTrigger {
    var $mimetypes = array(
       'application/msword' => true,
    );
    var $command = 'catppt';          // could be any application.
    var $commandconfig = 'indexer/catppt';          // could be any application.
    var $args = array();
    var $use_pipes = true;
    
    // see BaseIndexer for how the extraction works.
}

?>
