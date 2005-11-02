<?php

require_once('../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');

$aNewValues = array(
    'zxcv',
    'asdfq',
    'tgb',
    'edrf',
);

$res = KTMetadataUtil::synchroniseMetadata(4, $aNewValues);

?>
