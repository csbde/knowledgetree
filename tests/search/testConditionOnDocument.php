<?php

require_once('../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/search/savedsearch.inc.php');
require_once(KT_LIB_DIR . '/search/searchutil.inc.php');

$oSearch = KTSavedSearch::getByNamespace('http://ktcvs.local/local/savedsearches/mp3');
$iDocumentId = 96;
var_dump(KTSearchUtil::testConditionOnDocument($oSearch, $iDocumentId));
