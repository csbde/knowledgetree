<?php
/**
* Page used by PatternCreate.  Creates the actual object and stores it
*
* Expected form variables:
*	o $fRedirectURL - URL to redirect to after object creation (must be URL encoded)
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree
*
*/

require_once("../../../config/dmsDefaults.php");
require_once("$default->owl_fs_root/presentation/Html.inc");

$aKeys = array_keys($_POST);
$aParameterValues = array();
$sObjectName;
$sObjectFolderName;
//parse the information in the html page
for ($i = 0; $i < count($aKeys); $i++) {	
	$sRowStart = $aKeys[$i];		
	$pos = strncasecmp("unique_start", $sRowStart, 12);
	if ($pos == 0) {		
		$i++;
		//get the object to create
		$sObjectName = $_POST[$aKeys[$i]];
		$i++;
		//get the object folder name
		$sObjectFolderName = $_POST[$aKeys[$i]];		
		$i++;
		
		while ((strncasecmp("unique_end", $sRowStart, 10) != 0)  && ($i < count($aKeys))) {			
			//get the paramater number
			$iParameterNumber = $_POST[$aKeys[$i]];			
			$i++;
			$iType = $_POST[$aKeys[$i]];			
			$value;
			switch ($iType) {
				case 1:					
					$i++;					
					$value = $_POST[$aKeys[$i]];
					break;
				case 2:			
					//check boxes don't post back any values if they are unchecked
					//so we have to do a special check
					if (strpos($aKeys[$i + 1], "parnum") != 0) {
						//if the next key is one of type parnum, then the checkbox
						//didn't post anything back and is obviously unchecked
						$value = 0;
					} else {
						//the checkbox did post back a value and was therefore checked
						//the checkbox posts back a value of "on" so don't actually use the postback
						//value, rather just set value to true
						$i++;
						$value = 1;					
					}
					break;
				case 3:				
					$i++;
					$value = $_POST[$aKeys[$i]];
					break;
			}			
			$aParameterValues[$iParameterNumber] = $value;		
			$i++;
			$sRowStart = $aKeys[$i];		
		}		
	}
}


//include the correct file for the object
include_once("$default->owl_fs_root/lib/$sObjectFolderName");

$oObject = call_user_func("createFromArray",$aParameterValues);
$oObject->create();

//redirect the user
if (isset($fRedirectURL)) {
	redirect(urldecode($fRedirectURL) . $oObject->iId);
} else {
	redirect("$default->owl_root_url/control.php");
}

?>
