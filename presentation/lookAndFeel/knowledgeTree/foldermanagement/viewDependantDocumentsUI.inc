<?php


function renderHeading($sHeading) {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"600\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\">$sHeading</th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

function getFolderPath($iFolderID) {
	global $default;
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");
	$sFolderPathLink = displayFolderPathLink(Folder::getFolderPathAsArray($iFolderID), Folder::getFolderPathNamesAsArray($iFolderID),  "$default->rootUrl/control.php?action=browse");	    
	return "<table border=\"0\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sFolderPathLink</td></tr></table>\n";    
}

function getDocumentLinkFolderCollaborationData($iFolderCollaborationID) {
	global $default;	
	$sQuery = "SELECT DDT.id, U.name AS username, DDT.document_title, D.name AS template_document_name, DDT.group_folder_approval_link_id, 'Edit' AS edit " .
				"FROM dependant_document_template AS DDT INNER JOIN groups_folders_approval_link AS GFAL ON DDT.group_folder_approval_link_id = GFAL.id " .
				"INNER JOIN users AS U ON U.id = DDT.default_user_id " .
				"LEFT OUTER JOIN documents AS D ON D.id = DDT.template_document_id " . 
				"WHERE DDT.group_folder_approval_link_id = $iFolderCollaborationID";
				
	//echo $sQuery;			
	$aColumns = array("document_title", "username", "template_document_name","edit");
	$aColumnHeaders = array("Document title", "Default creator", "Template document", "Edit");
	$aColumnTypes = array(1,1,1,3);
	$aDBColumnArray = array("id","group_folder_approval_link_id");
	$aQueryStringVariableNames = array("fDependantDocumentTemplateID","fFolderCollaborationID");
	$aLinkURLs = array(3=>"$default->rootUrl/control.php?action=linkDocFolderCollaboration");
	
	$oPatternTableSqlQuery = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnHeaders, "90%", $aLinkURLs, $aDBColumnArray,$aQueryStringVariableNames);
	$oPatternTableSqlQuery->setTableHeading("Linked Documents");
	$oPatternTableSqlQuery->setDisplayColumnHeadings(true);
	$oPatternTableSqlQuery->setEmptyTableMessage("No dependant documents");
	
	return $oPatternTableSqlQuery->render();
	

}

function getViewPage($iFolderCollaborationID, $iFolderID) {
	global $default;	
	$sToRender = renderHeading("Dependant documents");
    $sToRender .= getFolderPath($iFolderID);
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>" . getDocumentLinkFolderCollaborationData($iFolderCollaborationID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\"/></td><td></td></tr></table>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";	
	
	return $sToRender;
}

function getEditPage() {
	
}

function getAddPage($iFolderCollaborationID, $iFolderID, $iUnitID, $sDocumentTitle) {
	global $default;
	$sToRender = renderHeading("Dependant documents");
    $sToRender .= getFolderPath($iFolderID);    
	$sToRender .= "<table border=\"0\" width=\"100%\">\n";	
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td>Document title</td><td><input type=\"text\" name=\"fDocumentTitle\" value=\"$sDocumentTitle\" /></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>User's Unit</td><td>" . getUnitDropDown($iFolderCollaborationID, $iFolderID, $iUnitID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>User</td><td>" . getUserDropDown($iUnitID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Template document</td><td><input type=\"text\" /></td>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<td><table><tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/add.gif\" border=\"0\"/></td><td></td></tr></table>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";	
	
	return $sToRender;
}

function getUserDropDown($iUnitID) {
	global $default;	
	$oPatternListBox = & new PatternListBox($default->owl_users_table, "Name", "id", "fUserID", "GUL.unit_id = $iUnitID");
	$sFromClause = "INNER join users_groups_link AS UGL ON UGL.user_id = ST.id " .
					"INNER JOIN groups_units_link AS GUL ON GUL.group_id = UGL.group_id ";
	$oPatternListBox->setFromClause($sFromClause);
	return $oPatternListBox->render();
	
	
}

function getUnitDropDown($iFolderCollaborationID, $iFolderID, $iUnitID) {
	global $default;	
	$oPatternListBox = & new PatternListBox($default->owl_units_table, "Name", "id", "fUnitID");	
	$oPatternListBox->setPostBackOnChange(true);
	$oPatternListBox->setOnChangeAction("setActionAndSubmit('" . $_SERVER["PHP_SELF"] . "?fFolderID=$iFolderID&fFolderCollaborationID=$iFolderCollaborationID&fForAdd=1')");
	$oPatternListBox->setSelectedValue($iUnitID);
	return $oPatternListBox->render();
}


?>