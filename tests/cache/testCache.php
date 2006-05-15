<?php

require_once(dirname(__FILE__) . '/../test.php');

class CacheTestCase extends KTUnitTestCase {
    function testListCache() {
        $f = Group::getList();
        $iNumGroups = count($f);
        DBUtil::startTransaction();
        $g = Group::createFromArray(array(
            'name' => 'foo',
        ));
        if (!$this->assertGroup($g)) {
            return;
        }
        $f = Group::getList();
        $iNowNumGroups = count($f);
        $this->assertEqual($iNumGroups + 1, $iNowNumGroups, 'New group not in list');
        DBUtil::rollback();
    }

    function testRollback() {
        $f = Group::getList();
        $iNumGroups = count($f);
        DBUtil::startTransaction();
        $g = Group::createFromArray(array(
            'name' => 'rollback',
        ));
        if (!$this->assertGroup($g)) {
            return;
        }
        $f = Group::getList();
        $iNowNumGroups = count($f);
        $this->assertEqual($iNumGroups + 1, $iNowNumGroups, 'New group not in list');
        DBUtil::rollback();
        $f = Group::getList();
        $iRollbackNumGroups = count($f);
        $this->assertEqual($iNumGroups, $iRollbackNumGroups, 'New group still in list (should be rolled back)');
    }
}
