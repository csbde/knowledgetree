<?php

/**
* Presentation data used to modify documents (will be used by modifyBL.inc)
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 24 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentmanagement
*
*/

function renderDocumentPath($oDocument) {
    $sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), Folder::getFolderPathNamesAsArray($oDocument->getFolderID())) . " > " . $oDocument->getName();
    return "<table border=1 width = 100%><tr><td>$sDocumentPath</td></tr></table>\n";
}

function renderEditableDocumentData($oDocument, $iDocumentTypeID) {
    global $default;
    $sQuery = "SELECT D.id AS id, D.name AS name, D.document_type_id AS document_type_id " .
              "FROM $default->owl_documents_table AS D " .
              "WHERE D.id = " . $oDocument->getID();
			  
	$sql = $default->db;
	$sql->query($sQuery);
	$sToRender;
	if ($sql->next_record()) {
		$sToRender = "<table>\n";
		$sToRender .= "<tr>\n";		
		$sToRender .= "<td>\n";
		$sToRender .= "<b>Document Title </b>\n";
		$sToRender .= "</td>\n";
		$sToRender .= "<td>\n";
		$sToRender .= "<input type=\"text\" name=\"fDocumentName\" value=\"" . $sql->f("name") . "\" />\n";
		$sToRender .= "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr>\n";	
		$sToRender .= "<td>\n";
		$sToRender .= "<b>Document Type </b>\n";
		$sToRender .= "</td>\n";
		$sToRender .= "<td>\n";
		$sToRender .= getDocumentType($oDocument->getFolderID(), $iDocumentTypeID);
		$sToRender .= "</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "</table>\n";
		
		return $sToRender;
	}
	return "";
}

function getDocumentType($iFolderID, $iDocumentTypeID) {
	global $default;
	$sWhereClause = "FDL.folder_id = $iFolderID";
	$oPatternListBox = & new PatternListBox("$default->owl_document_types_table", "name", "id", "fDocumentTypeID",$sWhereClause);
	$oPatternListBox->setIncludeDefaultValue(false);
	$oPatternListBox->setFromClause("INNER JOIN $default->owl_folder_doctypes_table AS FDL ON ST.id = FDL.document_type_id");
	//$oPatternListBox->setPostBackOnChange(true);	
	if (isset($iDocumentTypeID)) {
		$oPatternListBox->setSelectedValue($iDocumentTypeID);
	}
	//$oPatternListBox->setOnChangeAction("setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "&fDocumentID=$iDocumentID')");
	return $oPatternListBox->render();
}



function renderPage($oDocument, $iDocumentTypeID) {
    global $default;
    $sToRender = "<table border = 0>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    $sToRender .= wrapInTable(renderEditableDocumentData($oDocument, $iDocumentTypeID)) . "\n";
    $sToRender .= "</tr>\n";    
    $sToRender .= "</table>\n";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><input type=\"image\" src=\"$default->graphicsUrl/widgets/update.gif\" /><td>\n";
    $sToRender .= "<td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a><td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
		
    return $sToRender . getValidationJavaScript();
}

function getValidationJavaScript() {
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";
	$sToRender .= "\tif (!(validRequired(document.MainForm.fDocumentName, 'Document Title'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";	
	$sToRender .= "return true;\n}\n";		
	$sToRender .= "//-->\n</script>\n\n";
	
	return $sToRender;
}

function wrapInTable($sHtml) {
    return "\n\t\t\t<table border = 1, width = 100%><tr><td>$sHtml</td></tr></table>\n";
}?>
