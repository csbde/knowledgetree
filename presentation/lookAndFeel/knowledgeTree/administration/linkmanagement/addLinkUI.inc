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
	
	//$oOrg = null;
	//if (isset($iLinkID)) {
	//	$oLink = Link::get($iLinkID);
	//	
	//}
	$aDisplayRowNames = array("Name:", "URL: ", "Rank:(1-5) ");
	$aParameterNumbers = array(0,1,2);
	$aDisplayColumnTypes = array(1,1,1);
	$aRequired = array(1,1,1);
	

	// create a text field that is required 
	$oPatternCreate = & new PatternCreate("Link", "links/link.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired);
	$oPatternCreate->setUniqueName("link");
	
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Add a New QuickLink:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPatternCreate->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td><td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" value=\"Submit\" /></td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
	
}

 // TODO .. do a check if rank already taken...as well as do a check if name already exists when inserting
 
 
 
 // if edition is successful print otu success page
function getAddPageFail() {	
	global $default;
			
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Addition Unsuccessful</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr><td>The Libn</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addUnit\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}




?>