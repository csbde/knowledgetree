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
	
	$sToRender .= renderHeading("Add Document Field");
	$sToRender .= "<table>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPatternCreate->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" value=\"Submit\" border=\"0\"/></td>\n";
    $sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=docfield\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" border = \"0\"/></a></td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
}
?>