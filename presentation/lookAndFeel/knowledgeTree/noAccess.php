<?php
// main library routines and defaults
require_once("../../../config/dmsDefaults.php");

/**
* No access page
*
*
*
*/

echo "<center><b>You do not have permission to access this page.<br>";
echo "<a href=\"javascript:history.go(-1)\">Back</a> OR " . generateControllerLink("logout", "", "logout");
echo "</b></center>";

?>
