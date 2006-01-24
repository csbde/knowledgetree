<?php

$checkup = true;

require_once('../../config/dmsDefaults.php');

$s = array(
	"sql*2.99.5*0*2.99.5/dashlet_disabling.sql",
	"sql*2.99.5*0*2.99.5/role_allocations.sql",
	"sql*2.99.5*0*2.99.5/transaction_namespaces.sql",
	"sql*2.99.5*0*2.99.5/fieldset_field_descriptions.sql",
	"sql*2.99.5*0*2.99.5/role_changes.sql",
);

$sTable = KTUtil::getTableName('upgrades');

foreach ($s as $u) {
    var_dump($u);
    $f = array(
        'descriptor' => $u,
        'result' => true,
    );
    $res = DBUtil::autoInsert($sTable, $f);
    var_dump($res);
}
