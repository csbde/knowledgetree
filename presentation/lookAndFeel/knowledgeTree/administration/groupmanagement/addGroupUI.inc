<?php
/**
* Presentation information for adding a group
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getPage() {

	global $default;
	$aDisplayRowNames = array("Group Name:");
	$aParameterNumbers = array(0);
	$aDisplayColumnTypes = array(1);
	$aRequired = array(1);
	//$aDropDownListTableNames = array(10=>"document_types_lookup");

	$oPatternCreate = & new PatternCreate("Group", "groups\\Group.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired);
	$oPatternCreate->setUniqueName("group");
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Add a New Group to the System:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPatternCreate->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td align = right><input type=\"image\" src =\"$default->owl_graphics_url/widgets/add.gif\" value=\"Submit\" /></td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
	
	
	
}
?>