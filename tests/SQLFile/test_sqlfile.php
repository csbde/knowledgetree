<?php
require_once(dirname(__FILE__) . "/../test.php");

require_once(KT_DIR . "/lib/database/sqlfile.inc.php");

class SQLFileTestCase extends KTUnitTestCase {
    function testSQLFile() {
        $aExpected = array(
            "SELECT \"as;\";",
            "SELECT \"as\\\";\";",
            "SELECT \"as\\\\\";",
            "SELECT \"as\\\\\";",
            "SELECT \"as\\\\\";",
            "SELECT \"as\\\\\";",
            "SELECT 'as\\\\';",
            "SELECT 'as\\\\';",
            "SELECT 'as\\\\';",
            "SELECT 'as\\\\';",
            "SELECT 'as\\\\';",
            "SELECT 'as\\';';",
            "SELECT \"'as\\'\"';';",
            "SELECT 'as\\\\';",
            "SELECT 'as\\\\';",
            "SELECT 'as\\\\';",
            "SELECT 'as\\\\';",
            "SELECT 'as\\\\';",
            "SELECT 'as\\\\';",
            "SELECT '
asdf; \"\\\"  \\'

asdf; ';",
        );

        $aReceived = SQLFile::sqlFromFile(dirname(__FILE__) . "/test_sqlfile.sql");

        $this->assertExpectedResults($aExpected, $aReceived);
    }
}

?>
