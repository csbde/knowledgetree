<?php
/**
* Presentation information for adding a new step to the folder collaboration flow
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getPage() {

	$aDisplayRowNames = array("User name", "Name", "Password", "Max. Quota", "Email", "Mobile no.","Email notification", "SMS notification", "LDAP DN", "Max. sessions", "Language");
	$aParameterNumbers = array(0,1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
	$aDisplayColumnTypes = array(1,1,1,1,1,1,2,2,1,1,3);
	$aRequired = array(1,1,1,1,1,1,1,1,1,1,1);
	$aDropDownListTableNames = array(10=>"document_types_lookup");

	$oPatternCreate = & new PatternCreate("User", "users\\User.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired, $aDropDownListTableNames);
	$oPatternCreate->setUniqueName("user");

	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPatternCreate->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><input type=\"submit\" value=\"Submit\" /></td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "<table>\n";
	
	return $sToRender;
	
	
	
}
?>
