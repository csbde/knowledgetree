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

/**
 * Extends the HTML reporter to display more information
 *
 */
class KTHtmlReporter extends HtmlReporter {

    /**
     * Display all test output
     *
     * @var bool
     */
    protected $show;

    /**
     *    Does nothing yet. The first output will
     *    be sent on the first test start. For use
     *    by a web browser.
     *    @access public
     */
    function KTHtmlReporter($show = false) {
        $this->HtmlReporter();
        $this->show = $show;
    }


    /**
     * Display the passed tests
     *
     * @param string $message Display a custom message for the test
     */
    function paintPass($message) {
        parent::paintPass($message);

        if($this->show){
            print "<span class=\"pass\">PASS</span>: ";
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print implode("->", $breadcrumb);
            print "->$message<br />\n";
        }
    }

    /**
     *    Paints the test failure with a breadcrumbs
     *    trail of the nesting test suites below the
     *    top level test.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     *    @access public
     */
    function paintFail($message) {
        SimpleScorer::paintFail($message);

        print "<span class=\"fail\"><b>FAIL</b></span>: ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode(" -&gt; ", $breadcrumb);
        print " -&gt; " . $this->_htmlEntities($message) . "<br />\n";
    }

    /**
     *    Paints a PHP error.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintError($message) {
        SimpleScorer::paintError($message);

        print "<span class=\"fail\"><b>EXCEPTION</b></span>: ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode(" -&gt; ", $breadcrumb);
        print " -&gt; <strong>" . $this->_htmlEntities($message) . "</strong><br />\n";
    }

    /**
     * Display the start of each method
     *
     * @param string $test_name
     */
    function paintMethodStart($test_name) {
        parent::paintMethodStart($test_name);
        if($this->show) print "<br />";
        print "<span class=\"method\"><b>Method:</b> $test_name</span><br />";
    }

    /**
     * Display the start of each test case
     *
     * @param string $test_case
     * @param int $size
     */
    function paintGroupStart($test_case, $size) {
        parent::paintGroupStart($test_case, $size);
        print "<br /><div class=\"group\"><b>Test Case:</b> $test_case</div>";
    }

    /**
     *    Paints the CSS. Add additional styles here.
     *    @return string            CSS code as text.
     *    @access protected
     */
    function _getCss() {
        return ".fail { background-color: inherit; color: red; }" .
                ".pass { background-color: inherit; color: green; }" .
                " pre { background-color: lightgray; color: inherit; }" .
                ".group { background-color: lightblue; padding: 4px; }" .
                ".method { background-color: inherit; }";
    }
}

/**
 * Extends the text (CLI) reporter to display more information
 *
 */
class KTTextReporter extends TextReporter {

    /**
     * Display the start of each test case
     *
     * @param string $test_case
     * @param int $size
     */
    function paintGroupStart($test_case, $size) {
        parent::paintGroupStart($test_case, $size);
        print "\nTest Case: $test_case\n";
    }

    /**
     * Display the start of each method
     *
     * @param string $test_name
     */
    function paintMethodStart($test_name) {
        parent::paintMethodStart($test_name);
        print "Method: $test_name\n";
    }
}