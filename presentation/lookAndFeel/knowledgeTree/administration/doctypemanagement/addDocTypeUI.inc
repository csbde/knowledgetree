<?php
/**
* Presentation information for adding a Org
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
// get add pages
function getPage() {

	global $default;
	$aDisplayRowNames = array("Document Type Name:");
	$aParameterNumbers = array(0);
	$aDisplayColumnTypes = array(1);
	$aRequired = array(1);
	//$aDropDownListTableNames = array(10=>"document_types_lookup");

	// create a text field that is required 
	$oPatternCreate = & new PatternCreate("DocumentType", "documentmanagement/DocumentType.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired);
	$oPatternCreate->setUniqueName("documentType");
	
	$sToRender .= "<br></br>\n";
	$sToRender .= "<b>Add a New Document Type to the System:</b>\n";
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
	$sToRender .= "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" value=\"submit\" /></td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
	
	
	
}
?>