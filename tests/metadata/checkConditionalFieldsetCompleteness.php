<?php

require_once('../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');

error_reporting(E_ALL);

var_dump(KTMetadataUtil::checkConditionalFieldsetCompleteness(11));

?>
