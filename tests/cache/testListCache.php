<?php

require_once('../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/groups/Group.inc');

$f = Group::getList();
var_dump($f);
DBUtil::startTransaction();
$g = Group::createFromArray(array(
    'name' => 'foo',
));
$f = Group::getList();
$f = Group::getList();
$f = Group::getList();
DBUtil::rollback();
