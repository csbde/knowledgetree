<?php

require_once("../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");

function buildPage() {
$str = <<< EOT
<!-- <html>
<head>
<script language="JavaScript" src="datePicker.js"></script>
</head>
<BODY> -->
<center>
<input type=text name="datebox" size=15>
<!-- <a href="javascript:show_calendar('MainForm.datebox',null,null,'YYYY-MM-DD');" onmouseover="window.status='Date Picker';return true;" onmouseout="window.status='';return true;"> -->
<a href="javascript:show_calendar('MainForm.datebox');">
<img src="show-calendar.gif" width=24 height=22 border=0></a>
</center>
<!--</body>
</html>-->
EOT;
	return $str;
}
//echo buildPage();

if (checkSession()) {
    // instantiate my content pattern
    $oContent = new PatternCustom();
    $oContent->setHtml(buildPage());
	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);			
	$main->render();
}

?>