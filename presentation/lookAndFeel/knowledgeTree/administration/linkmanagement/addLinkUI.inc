<?php
/**
* Presentation information for adding a unit
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// get add pages
function getPage() {

	global $default;

	$aDisplayRowNames = array("Name:", "URL: ", "Rank:(1-5) ");
	$aParameterNumbers = array(0,1,2);
	$aDisplayColumnTypes = array(1,1,1);
	$aRequired = array(1,1,1);

	// create a text field that is required 
	$oPatternCreate = & new PatternCreate("Link", "links/link.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired);
	$oPatternCreate->setUniqueName("link");
    $oPatternCreate->setDefaultValues(array(1=>"http://"));
	
	$sToRender .= "<table width=\"600\">" . renderHeading("Add QuickLink") . "</table>";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPatternCreate->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td><td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" value=\"Submit\" border=\"0\"/></td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

 // TODO .. do a check if rank already taken...as well as do a check if name already exists when inserting
 // if edition is successful print otu success page
function getAddPageFail() {	
	global $default;
			
    $sToRender .= "<table width=\"600\">" . renderHeading("Add QuickLink") . "</table>";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>Addition Unsuccessful</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}
?>