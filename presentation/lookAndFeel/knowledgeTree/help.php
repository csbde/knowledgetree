<?php
require_once("../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/presentation/Html.inc");
global $default;
$heading = "$default->graphicsUrl/heading.gif";
$hStretched = "$default->graphicsUrl/hrepeat.gif";
$row1 = "<img src = ". $heading. ">";

//Output a title bar
$headingBar  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"1\" width=\"100%\" height=\"10%\">\n";
$headingBar .= "\t<tr height=\"20%\">\n";
$headingBar .= "\t\t<td background=\"$hStretched\" width=\"100%\"><img src=\"$heading\"/></td>\n";
$headingBar .= "\t</tr>\n";
$headingBar .= "</table>\n";
echo $headingBar;

//Query the database for the helpURL based on the current action
$sQuery = "SELECT HLP.help_info as helpinfo ".
		"FROM $default->help_table AS HLP WHERE '$fAction' = HLP.fSection";
		
$sql = $default->db;
$sql->query($sQuery);

if ($sql->next_record()) {
	require_once("$default->uiDirectory/help/" . $sql->f("helpinfo"));
} else {
	echo "No help available for $fAction";
}
?>

