<?php

$GLOBALS['kt_test'] = true;
require_once(dirname(__FILE__) . '/../config/dmsDefaults.php');
require_once('simpletest/autorun.php');
//require_once('simpletest/unit_tester.php');
//require_once('simpletest/mock_objects.php');
//require_once('simpletest/reporter.php');

class KTUnitTestCase extends UnitTestCase {
    function assertExpectedResults($aExpected, $aReceived) {
        if ($aReceived == $aExpected) {
            $this->pass('Expected results received');
            return;
        }

        $iLen = count($aExpected);
        for ($c = 0; $c < $iLen; $c++) {
            if ($aReceived[$c] != $aExpected[$c]) {
                $this->fail(sprintf("Failure.  Expected %s, but got %s\n", $aExpected[$c], $aReceived[$c]));
            }
        }
    }

    function assertEntity($oEntity, $sClass) {
        if (is_a($oEntity, $sClass)) {
            return $this->pass(sprintf('Object is a %s', $sClass));
        }
        return $this->fail(sprintf('Object is not a %s', $sClass));
    }

    function assertNotError($oObject) {
        if(PEAR::isError($oObject)) {
            return $this->fail(sprintf('Object is a PEAR Error: '.$oObject->getMessage() ));
        }
        return $this->pass(sprintf('Object is not a PEAR Error'));
    }
    
    function assertError($oObject) {
        if(PEAR::isError($oObject)) {
            return $this->pass(sprintf('Object is a PEAR Error: '.$oObject->getMessage() ));
        }
        return $this->fail(sprintf('Object is not a PEAR Error'));
    }

    function assertGroup($oGroup) {
        return $this->assertEntity($oGroup, 'Group');
    }
}
