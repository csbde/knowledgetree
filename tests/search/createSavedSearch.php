<?php

require_once('../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/search/savedsearch.inc.php');

$aSearch = array(
    'join' => 'AND',
    'subgroup' => array(
        array(
            'join' => 'AND',
            'values' => array (
                array (
                    'type' => '-3',
                    'data' => array (
                        'bmd_3' => '4',
                    ),
                ),
            ),
        ),
    ),
);

$oSearch = KTSavedSearch::createFromArray(array(
    'name' => 'name',
    'namespace' => 'namespace',
    'iscondition' => false,
    'iscomplete' => true,
    'userid' => null,
    'search' => $aSearch,
));

var_dump($oSearch);
