<?php
/**
 * $Id$
 *
 * Page used by PatternCreate.  Creates the actual object and stores it
 *
 * Expected form variables:
 *	o $fRedirectURL - URL to redirect to after object creation (must be URL encoded)
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once("../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/presentation/Html.inc");

if (!checkSession()) {
    exit(0);
}
    
$aKeys = array_keys($_POST);
$aParameterValues = array();
// $sObjectName;
// $sObjectFolderName;
//parse the information in the html page
for ($i = 0; $i < count($aKeys); $i++) {	
	$sRowStart = $aKeys[$i];		
	$pos = strncasecmp("unique_start", $sRowStart, 12);
	if ($pos == 0) {		
		$i++;
		//get the object to create
		//$sObjectName = $_POST[$aKeys[$i]];
		//$i++;
		//get the object folder name
		//$sObjectFolderName = $_POST[$aKeys[$i]];		
		//$i++;
                $sRandomString = substr($sRowStart, 13);
                $sObjectName = $_SESSION["pelfq_" . $sRandomString . "_object"];
                $sObjectFolderName = $_SESSION["pelfq_" . $sRandomString . "_fn"];
		
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
					if ((strpos($aKeys[$i + 1], "parnum") != 0) || (substr($aKeys[$i + 1],0,10) == "unique_end")) {						
						//if the next key is one of type parnum or is the end of the section, then the checkbox
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
include_once("$default->fileSystemRoot/lib/$sObjectFolderName");

$oObject = call_user_func(strtolower($sObjectName) . "createFromArray",$aParameterValues);
if ($oObject->create()) { 
	$bSuccess = true;
} else {
	$bSuccess = false;
}

//redirect the user
if (array_key_exists('fRedirectURL', $_REQUEST)) {
	redirect(strip_tags(urldecode($_REQUEST['fRedirectURL'])) . $oObject->iId . "&fSuccess=" . $bSuccess);
} else {
	redirect("$default->rootUrl/control.php");
}

?>
