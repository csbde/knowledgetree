<?php

require_once("../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");

global $default;
	
if(checkSession())
{

// include the page template (with navbar)
require_once("$default->fileSystemRoot/presentation/webPageTemplate.inc");  	
 // when email button sent..send email
if ($submit) 
{
	
	//$success = $
	
	//if successful ..rerender the page
	if($success == True)
	{
		$Center = "<br>Group Successfully Added</br>";
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml($Center); 
		$main->setCentralPayload($oPatternCustom);
		$main->render();
	}
	Else
	{
		$Center = "<br>Group Addition Unsuccessful</br>";
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml($Center); 
		$main->setCentralPayload($oPatternCustom);
		$main->render();
	}
	
		
	
}

$Center = "
	<br>
	</br>
	<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">
	<tr>
	<td>New Group: <TD WIDTH=\"100%\"><INPUT type=\"Text\" name=\"group\" size=\"30\"></td></td>
	</tr>
	<tr>
	<tr>
	<td><center><TD WIDTH=\"80%\"><INPUT type=\"Submit\" name=\"submit\" value=\"Add Group\"></center></td></td>
	</tr>
	</table>
	";
		
$oPatternListBox = & new PatternListBox("units_lookup", "name", "id", "Units");
//echo "<html><head></head><body>" . $oPatternListBox->render() . "</body></html>";

	
	
$oPatternCustom = & new PatternCustom();
$oPatternCustom->setHtml($Center); 
$main->setCentralPayload($oPatternCustom);
$main->setFormAction($_SERVER["PHP_SELF"]);
$main->render();




}

?>