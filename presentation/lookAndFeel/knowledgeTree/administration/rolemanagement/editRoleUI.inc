<?php
/**
* Presentation information for adding a group
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// get hte edit page if it comes from the add page
function getCreatePage($iRoleID) {
	
	global $default;
	
	
	$sQuery =  "SELECT * FROM $default->owl_roles_table WHERE id = $iRoleID";
	$aDisplayColumns = array("name","can_read","can_write");
	$aStoreColumns = array("name","can_read","can_write");
	$aColumnNames = array("Name:","Readable:", "Writable:");
	$aDisplayColumnTypes = array(1,2,2);
	$aDatabaseColumnTypes = array(1,2,2);
	
	// get list of role properties...name...text field...is_Sys_admin and is_unit_admin..checkboxes
	$oPattern = & new PatternEditableListFromQuery($sQuery, $default->owl_roles_table, $aDisplayColumns, $aStoreColumns, $aColumnNames, $aDisplayColumnTypes, $aDatabaseColumnTypes) ;
	$oPattern->setUniqueName("roleEdit");
	$oPattern->setColumnsRequired(array(1,0,0));
		
	$sToRender .= "<b>Edit Role Properties:</b>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<br></br>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPattern->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	
	$sToRender .= "<td align = right><input type=\"image\" src =\"$default->owl_graphics_url/widgets/update.gif\" value=\"Update\" /></td>\n";	
	$sToRender .= "</table>\n";
		
	
		
	return $sToRender;
	
	
}
?>