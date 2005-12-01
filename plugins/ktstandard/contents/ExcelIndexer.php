<?php


require_once(KT_DIR . '/plugins/ktstandard/contents/BaseIndexer.php');

class KTExcelIndexerTrigger extends KTBaseIndexerTrigger {

    var $mimetypes = array(
       'application/msword' => true,
    );
    var $command = 'xls2csv';          // could be any application.
    var $args = array("-q", "0", "-c", " ");
    var $use_pipes = true;
    
    // see BaseIndexer for how the extraction works.
}

?>
