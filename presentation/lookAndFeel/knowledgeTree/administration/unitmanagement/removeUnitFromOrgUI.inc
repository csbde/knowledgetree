<?php
/**
* Presentation information for adding a unit
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getRemoveFailPage(){
	global $default;
	
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	
	$sToRender .= "Failure while trying to remove Unit from the Organisation.\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=listUnits\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a>\n";

	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
	
}
function getRemoveSuccessPage(){
	global $default;
	
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	
	$sToRender .= "Unit has been successfully removed from the Organisation.\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=listUnits\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a>\n";
		
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";

	return $sToRender;
}


function getRemoveUnitsPage($oLink) {
	global $default;
		
	if($oLink){
		$oOrg = Organisation::get($oLink->getOrgID());
		$oUnit = Unit::get($oLink->getUnitID());			
	
		$sToRender .= "<table>\n";
		$sToRender .= "<th colspan=2> Do you wish to remove the Unit from the Orgaisation?</th>\n";
		$sToRender .= "<tr colspan=2> &nbsp;</tr>\n";
		$sToRender .= "<th colspan=2> </th>\n";
		$sToRender .= "<tr>\n"; 
		$sToRender .= "<td>\n"; 
		$sToRender .= "<b>Unit Name: </b>";
		$sToRender .= "</td>\n";
		$sToRender .= "<td>\n"; 
		$sToRender .= $oUnit->getName();
		$sToRender .= "</td>\n";
		$sToRender .= "</tr>\n";		
		
		$sToRender .= "<th></th>\n";
		$sToRender .= "<tr>\n"; 
		$sToRender .= "<td>\n"; 
		$sToRender .= "<b>Organisation </b>";
		$sToRender .= "</td>\n";
		$sToRender .= "<td valign=top>\n"; 
		$sToRender .= $oOrg->getName();	
		$sToRender .= "</td>\n";
		$sToRender .= "<td valign=middle>\n";	
		$sToRender .= "<input type=\"image\" src =\"$default->graphicsUrl/widgets/remove.gif\" value=\"Submit\" border=\"0\"/> &nbsp;";
		$sToRender .= "<a href=\"$default->rootUrl/control.php?action=editUnitOrg&fUnitID=" . $oUnit->getID() . "\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a>\n";
		$sToRender .= "</td>\n";
		$sToRender .= "</tr>\n";		
		$sToRender .= "</table>\n";
	} else {
		$sToRender .= "Link object is empty.\n";
	}
	
	return $sToRender;
	
}

function getOrgDropdown(){
	global $default;
	
	$oPatternListBox = & new PatternListBox($default->owl_organisations_table, "name", "id", "fOrgID");
	$sToRender .= $oPatternListBox->render();
	
	return $sToRender;
}


// get add pages
function getAddPage($unitID) {
	global $default;
	
	if (isset($iOrgID)) {
        $oOrg = Organisation::get($iOrgID);
	}
	
    $sToRender .= renderHeading("Add Unit");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Unit Name: </td><td><input size = \"40\" type=\"text\" name=\"fUnitName\"> </td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Select an Organisation: </td><td>" . getOrgDisplay($oOrg) ;
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td><td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" value=\"Submit\" border=\"0\"/></td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

// if edition is successful print out success page
function getAddPageSuccess() {	
	global $default;
			
	$sToRender .= renderHeading("Add Unit");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>Addition Successful!</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>New Unit was successfully added</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

// if edition is successful print out failure page
function getAddPageFail() {	
	global $default;
			
    $sToRender .= renderHeading("Add Unit");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>Addition Unsuccessful</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The Unit was not added. Unit Name Already exists!</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// if edition is successful print otu success page
function getPageFail() {	
	global $default;
			
    $sToRender .= renderHeading("Add Unit");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>Please enter a name AND Select the Organisation</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}
// if edition is successful print otu success page
function getAddPageToOrgFail() {	
	global $default;
			
    $sToRender .= renderHeading("Add Unit");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Addition to Organisation Unsuccessful</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The Unit was not added to the Organisation.</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}


// show the list box when no unit selected
// then shows a text box with the unit's name
function getOrgDisplay($oOrg) {
	global $default;
	if (!isset($oOrg)) {
		$oPatternListBox = & new PatternListBox($default->owl_organisations_table, "name", "id", "fOrgID");
		return $oPatternListBox->render();
	} else {
		return "<input type=\"hidden\" name=\"fOrgID\" value=\"" . $oOrg->iId . "\">\n" .
		"" . $oOrg->getName() . "\">";
	}
	
}

?>