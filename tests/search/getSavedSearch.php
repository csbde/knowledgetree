<?php

require_once('../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/search/savedsearch.inc.php');

$oSearch = KTSavedSearch::getByNamespace('namespace');
var_dump($oSearch->getSearch());
