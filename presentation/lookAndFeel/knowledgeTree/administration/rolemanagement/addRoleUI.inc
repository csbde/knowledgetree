<?php
/**
* Presentation information for adding a group
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// get add pages
function getPage() {

	global $default;
	$aDisplayRowNames = array("Name: ");
	//$aStoreColumns = array("name","can_read","can_write");
	//$aColumnNames = array("Name:","Readable:", "Writable:");
	//$aDisplayRowNames = array("Name:");
	$aParameterNumbers = array(0);
	$aDisplayColumnTypes = array(1);
	$aRequired = array(1);
	//$aDropDownListTableNames = array(10=>"document_types_lookup");

	// create a text field that is required 
	$oPatternCreate = & new PatternCreate("Role", "roles/Role.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired);
	$oPatternCreate->setUniqueName("role");
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Add a New Role to the System:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPatternCreate->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" value=\"Submit\" border=\"0\"/></td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
	
	
	
}
?>