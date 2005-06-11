<?php

require_once("../../config/dmsDefaults.php");
require_once(KT_DIR . "/lib/database/sqlfile.inc.php");

var_dump(SQLFile::sqlFromFile("test_sqlfile.sql"));

?>
