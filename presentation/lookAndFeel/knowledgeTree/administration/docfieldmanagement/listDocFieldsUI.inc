<?php
/**
 * $Id$
 *
 * List document fields UI functions.
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
 * @author Omar Rahbeeni, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.docfieldmanagement
 */
 
function getDocumentFields() {
	global $default;
	
	$sQuery = 	"SELECT id as DocFieldID, name as DocFieldName, data_type, is_generic, has_lookup, " . 
				"'Edit', 'Delete', 'Edit Lookups' " .
				"FROM " . $default->document_fields_table . " " .
				"ORDER BY name";
	
    $aColumns = array("DocFieldName", "data_type", "is_generic", "has_lookup","Edit", "Delete", "Edit Lookups" );
    $aColumnNames = array(_("Name"), _("Data Type"), _("Generic?"), _("Lookup") . "?", _("Edit"), _("Delete"), _("Edit Lookups"));
    $aColumnTypes = array(1,1,2,2,3,3,3);
    $aDBColumnArray = array("DocFieldID");
    $aQueryStringVariableNames = array("fDocFieldID");
    	    
    $aHyperLinkURL = array(	4=> "$default->rootUrl/control.php?action=editDocField",                       			
                     			5=> "$default->rootUrl/control.php?action=removeDocField",
                     			6=> "$default->rootUrl/control.php?action=editDocFieldLookups");                       			
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);	    
	$oSearchResults->setDisplayColumnHeadings(true);	
    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading("Document Field Management");

	// add user link
	$sToRender .= getAddLink("addDocField", _("Add A Document Field"));
	$sToRender .= getDocumentFields();
	return $sToRender;
}
?>
