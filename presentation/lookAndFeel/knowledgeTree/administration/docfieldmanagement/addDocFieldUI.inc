<?php
/**
* Presentation information for adding a DocField
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// get add pages
function getPage() {

	global $default;
	$aDisplayRowNames = array("Document Field Name:", "Data Types: ", "Generic: ", "Has a Lookup: ");
	$aParameterNumbers = array(0,1,2,3);
	$aDisplayColumnTypes = array(1,3,2,2);
	$aRequired = array(1,1);
	$aDropDownListTableNames = array(1 => "$default->owl_data_types_table");
	$aDropDownListValueColumns = array(1=>'name');
	$aDropDownListDisplayColumns = array(1=>'name');

	// create a text field that is required 
	$oPatternCreate = & new PatternCreate("DocumentField", "documentmanagement/DocumentField.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired,$aDropDownListTableNames,$aDropDownListValueColumns,$aDropDownListDisplayColumns);
	$oPatternCreate->setUniqueName("DocField");
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Add a New Document Field to the System:</b>\n";
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
	$sToRender .= "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" value=\"Submit\" /></td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
	
	
	
}
?>