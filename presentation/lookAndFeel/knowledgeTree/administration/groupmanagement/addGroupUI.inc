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
	$aDisplayRowNames = array("Group Name:");
	$aParameterNumbers = array(0);
	$aDisplayColumnTypes = array(1);
	$aRequired = array(1);

	// create a text field that is required 
	$oPatternCreate = & new PatternCreate("Group", "groups/Group.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired);
	$oPatternCreate->setUniqueName("group");
	
	$sToRender .= renderHeading("Add Group");
	
	$sToRender .= "<table>\n";
    $sToRender .= "<tr><td>Add a New Group to the System</td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPatternCreate->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td align=\"right\"><input type=\"image\" src=\"$default->graphicsUrl/widgets/add.gif\" value=\"Submit\" border=\"0\"/>";
	$sToRender .= getCancelButton("listGroups") . "</td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
	
	
	
}
?>