<?php
// main library routines and defaults
require_once("../../../config/dmsDefaults.php");

/**
* No access page
*
*
*
*/

echo "<center><b>GO AWAY you bloody idiot.  You do not have rights to access this page.<br>";
echo "  Your harddrive is now being formated<br>Have a nice day :-)<br>";
echo generateControllerLink("logout") . "logout</a>";
echo "</b></center>";

?>
