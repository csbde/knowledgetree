<?php

require_once(KT_DIR . '/plugins/ktstandard/contents/BaseIndexer.php');

class KTTextIndexerTrigger extends KTBaseIndexerTrigger {
    var $mimetypes = array(
       'text/plain' => true,
       'text/csv' => true,
    );
    
    // no need for diagnostic - this is always available.
    
    function extract_contents($sFilename, $sTempFilename) {
        $contents = file_get_contents($sFilename);
        return $contents;
    }
}

?>
