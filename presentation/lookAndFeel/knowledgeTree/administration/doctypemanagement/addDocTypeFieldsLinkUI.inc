<?php
/**
* Presentation information for adding a Org
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getOptionPage($fDocTypeID = null){
	global $default;
	
	$sToRender .= renderHeading("Add Type Specific Field to Document Type");
	$sToRender .= "<br>";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td>New type specific field?</td><td><a href=\"$default->rootUrl/control.php?action=addDocTypeFieldsLink&fNewField=1&fDocTypeID=$fDocTypeID\"><img src =\"$default->graphicsUrl/widgets/select.gif\" border = \"0\" /></a></td></tr>\n";
	$sToRender .= "<tr><td>Choose from existing type specific fields</td><td><a href=\"$default->rootUrl/control.php?action=addDocTypeFieldsLink&fFromList=1&fDocTypeID=$fDocTypeID\"><img src =\"$default->graphicsUrl/widgets/select.gif\" border = \"0\" /></a></td></tr>\n";		
	$sToRender .= "<tr><td></td><td>" . getCancelButton($fDocTypeID) . "</td></tr>\n";
	$sToRender .= "</table>\n";
		
	return $sToRender;
}


function genericMessage($sMsg, $fDocTypeID = null){
	global $default;
	
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td>$sMsg</td></tr>\n";
	$sToRender .= "<tr><td></td><td>" . getBackButton($fDocTypeID) . "</td></tr>\n";		
	$sToRender .= "</table>\n";
		
	return $sToRender;
}

function getCreateNewFail($fDocTypeID){	
	$sToRender = genericMessage("Error creating new Type Specific Field and Linking it", $fDocTypeID);				
	return $sToRender;
}


function getCreateNewSuccess($fDocTypeID){
	$sToRender = genericMessage("New Type specific Field Created and Linked.", $fDocTypeID);				
	return $sToRender;	
}

function getFail_NoFieldID($fDocTypeID = null){	
	$sToRender = genericMessage("Linking cannot be done. Please select a field to link to.", $fDocTypeID);				
	return $sToRender;	
}

function addSuccessPage($fDocTypeID = null){
	$sToRender = genericMessage("Field is successfully linked to the Document Type.", $fDocTypeID);				
	return $sToRender;	
}

function addFailPage($fDocTypeID = null ){
	$sToRender = genericMessage("Error: Field Could not be linked to the Document Type.", $fDocTypeID);				
	return $sToRender;	
}

function getMissingDocTypeIDPage($iDocTypeID = null ){
	$sToRender = genericMessage("Error: Page expects a Document Type ID.", $fDocTypeID);				
	return $sToRender;		
}


function getListFailPage($iDocTypeID = null){
	$sToRender = genericMessage("There are no Non-generic fields in the database.", $fDocTypeID);				
	return $sToRender;	
}

// get add pages
function getFirstPage($sHtmlListBox = null, $iDocTypeID, $bIsNew = null) {
	global $default;
		
	$sToRender .= renderHeading("Add Type Specific Field to Document Type");	
	$sToRender .= "<br>\n";
	$sToRender .= "<table>\n";
	
	if ($bIsNew) {		
		$sNewTableName = $default->owl_data_types_table;
	    $sNewDisplayColumn = "name";
	    $sNewValueColumn = "name";
	    $sNewSelectName = "fDataType";	    
	    $bNewOrderAsc = true;
	   
	    $oSelectBox = & new PatternListBox($sNewTableName, $sNewDisplayColumn, $sNewValueColumn, $sNewSelectName, $sNewWhereClause , $bNewOrderAsc );
		$oSelectBox->setIncludeDefaultValue(false); 
		$sToRender .= "<tr>";
		$sToRender .= "<td>New non-generic field: </td><td><input type=text name=fNewField></td>";		
		$sToRender .= "</tr>";		
		$sToRender .= "<tr>";
		$sToRender .= "<td>Field data type: </td><td>" . $oSelectBox->render() . "</td>";		
		$sToRender .= "</tr>";
		$sToRender .= "<tr>";
		$sToRender .= "<td align=right>Has Lookup?  </td><td><input type=checkbox name=bHasLookup></td>";		
		$sToRender .= "</tr>";		
				
	} else {
		$sToRender .= "<tr>";
		$sToRender .= "<td>Non-generic fields: </td><td>" . $sHtmlListBox . "</td>";
		$sToRender .= "</tr>";
	}
	
	$sToRender .= "<tr><td align=right>Mandatory? </td><td><input type=checkbox name=bMandatory> </td></tr>\n";	
	$sToRender .= "<tr><td></td><td align=right><input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" border=0 value=\"submit\"/></td>";
	 
	//$sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=listDocTypes\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td></tr>\n";
	$sToRender .= "<td>" . getCancelButton($iDocTypeID) . "</td></tr>\n";
	
	$sToRender .= "</table>\n";
	
	return $sToRender;	
}

// gets the cancel button
function getCancelButton($iDocTypeID = null) {
    global $default;    
    //return "<a href=\"$default->rootUrl/control.php?action=listDocTypes\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a>\n";    
	return "<a href=\"$default->rootUrl/control.php?action=editDocTypeFields&fDocTypeSelected=1&fDocTypeID=" . $iDocTypeID . "\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a>\n";
}

// gets the cancel button
function getBackButton($iDocTypeID = null) {
    global $default;    
    return "<a href=\"$default->rootUrl/control.php?action=editDocTypeFields&fDocTypeSelected=1&fDocTypeID=" . $iDocTypeID . "\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a>\n";    
}
?>