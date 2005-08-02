<?php
/**
 * $Id$
 *
 * Contains UI functions to display the generic meta data edit page..
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */
 
function getEditableGenericMetaData($iDocumentID, $iDocumentTypeID) {
    global $default;
    /*ok*/ $sQuery = array("SELECT DISTINCT ? AS document_id, COALESCE(DFL.id, -1) AS id, DF.id AS document_field_id, DFL.value AS value, DF.name AS field_name " .
        "FROM document_fields AS DF LEFT OUTER JOIN document_fields_link AS DFL ON DF.id = DFL.document_field_id AND DFL.document_id = ? " .				
        "WHERE DF.is_generic = 1 ", array($iDocumentID, $iDocumentID));

    $aStoreColumnNames = array("document_id", "document_field_id","value");
    $aDisplayColumnNames = array("document_id", "field_name", "value");
    $aMetaDataColumnNames = array(2=>"field_name");
    $aColumnsEditable = array(0,0,1);
    $aColumnsVisible = array(0,1,1);
    $aColumnDisplayTypes = array(1,1,4);
    $aColumnDatabaseTypes = array(0,0,1);
    
    $oPatternTableSqlQuery = & new PatternEditableTableSqlQuery($sQuery, "document_fields_link", $aStoreColumnNames, $aDisplayColumnNames, $aColumnsEditable, $aColumnsVisible, $aColumnDisplayTypes, $aColumnDatabaseTypes);
    $oPatternTableSqlQuery->setTableCaption(_("Generic Meta Data"));
    $oPatternTableSqlQuery->setUniqueName("gmd");
	$oPatternTableSqlQuery->setRequiredColumnNames(array("value"));
	$oPatternTableSqlQuery->setMetaDataFields($aMetaDataColumnNames);
	$oPatternTableSqlQuery->setEmptyTableMessage(_("No Generic Meta Data"));
	$oPatternTableSqlQuery->setPreCode(sprintf('require_once(KT_LIB_DIR .  "/documentmanagement/documentutil.inc.php"); KTDocumentUtil::createMetadataVersion(%d);', (int)$iDocumentID));
	$oPatternTableSqlQuery->setPostCode(sprintf('require_once(KT_LIB_DIR .  "/documentmanagement/documentutil.inc.php"); KTDocumentUtil::bumpVersion(%d); KTDocumentUtil::setModifiedDate(%d);', (int)$iDocumentID, (int)$iDocumentID));
    return $oPatternTableSqlQuery->render();

}

function getPage($iDocumentID, $iDocumentTypeID, $bFirstEdit) {
	global $default;
	
	$sToRender .= renderHeading(_("Edit Generic Meta Data"));
	$sToRender .= displayDocumentPath($iDocumentID);
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";		
	$sToRender .= "<td>\n";
	$sToRender .= getEditableGenericMetaData($iDocumentID, $iDocumentTypeID);
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";		
	$sToRender .= "<td>\n";
	
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= "<input type=\"image\" src=\"" . KTHtml::getUpdateButton() . "\" border=\"0\"/>\n";
	$sToRender .= "</td>\n";
	if (!isset($bFirstEdit)) {
		//can't cancel if you're uploading for the first time, must fill out the generic meta data
		$sToRender .= "<td>\n";
		$sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=$iDocumentID&fShowSection=genericMetaData\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\" /></a>\n";
		$sToRender .= "</td>\n";
		$sToRender .= "</tr>\n";
	}
	$sToRender .= "</table>\n";
	
	$sToRender .= "</td>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}
?>
