<?php
/**
 * $Id$
 *
 * List document types UI functions.
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
 * @author Mukhtar Dharsey, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.doctypemanagement
 */

function getDocumentTypes() {
	global $default;
	
	$sQuery = 	"SELECT id as DocTypeID, name as DocTypeName, " . 
				"'Edit', 'Delete', 'Edit Fields' " .
				"FROM " . $default->document_types_table . " " .
				"ORDER BY name";
	
    $aColumns = array("DocTypeName", "Edit", "Delete", "Edit Fields");
    $aColumnNames = array(_("Name"), _("Edit"), _("Delete"), _("Edit Fields"));
    $aColumnTypes = array(1,3,3,3);
    $aDBColumnArray = array("DocTypeID");
    $aQueryStringVariableNames = array("fDocTypeID");
    	    
    $aHyperLinkURL = array(	1=> "$default->rootUrl/control.php?action=editDocType&fDocTypeSelected=1",                       			
                     			2=> "$default->rootUrl/control.php?action=removeDocType",
                     			3=> "$default->rootUrl/control.php?action=editDocTypeFields&fDocTypeSelected=1");
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);	    
	$oSearchResults->setDisplayColumnHeadings(true);
    return $oSearchResults->render() ;	
}

function getPage() {
	global $default;
	$sToRender .= renderHeading(_("Document Type Management"));

	// add user link
	$sToRender .= getAddLink("addDocType", _("Add A Document Type"));
	$sToRender .= getDocumentTypes();
	return $sToRender;
}
?>
