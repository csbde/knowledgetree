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
	$aDisplayRowNames = array("Website Name:", "Website URL: ", "WebMaster");
	$aParameterNumbers = array(0,1,2);
	$aDisplayColumnTypes = array(1,1,3);
	$aRequired = array(1,1,1);
	$aDropDownListTableNames = array(2 => "$default->owl_users_table");
	$aDropDownListValueColumns = array(2=>'id');
	$aDropDownListDisplayColumns = array(2=>'username');

	// create a text field that is required 
	$oPatternCreate = & new PatternCreate("Website", "web/WebSite.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired,$aDropDownListTableNames,$aDropDownListValueColumns,$aDropDownListDisplayColumns);
	$oPatternCreate->setUniqueName("website");
	
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Add a New Website:</b>\n";
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
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addWebsite\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	
	return $sToRender;
}




?>