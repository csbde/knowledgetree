<?php
/**
 * $Id$
 *
 * Edit document field lookups UI functions
 *
 * Copyright (c) 2003, 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @package administration.docfieldmanagement
 */

require_once("$default->fileSystemRoot/presentation/Html.inc");
 
function getPage($oDocField) {
    global $default;

    $iDocFieldID = $oDocField->getID();
    
    $LookupDisplay .= "<b>" . _("Current Lookups") . "</b><br>\n";	
	$sQuery = array("SELECT * FROM " . $default->metadata_table .
				" WHERE document_field_id = ?",
                array($iDocFieldID));

 	$aColumns = array("name");
    $aColumnNames = array(_("Lookup name"));
    $aColumnTypes = array(1);
    $aDBColumnArray = array("id");
    $aQueryStringVariableNames = array("fDocFieldLookupID");
    	    
    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
    $oSearchResults->setEmptyTableMessage("<font color=\"red\">" . _("You must enter at least one value!") . "</font>");   
	$LookupDisplay .= $oSearchResults->render() ;

	$sFontTag  .= "<font face=\"Arial\" color=\"#0066cc\">";
	$sTableTag .= "<table width=100% border=0 cellspacing=0>";
	$sBgColor = "#F5F6EE";
	
    $sToRender .= renderHeading(_("Edit Document Field Lookups"));
	
    $sToRender .= "<table width=\"600\" border=0>";
    $sToRender .= "<tr><td width=100%><br><b>" . _("Field Name") . ": &nbsp;</b>" . $oDocField->getName() . "<br><br></td><td>";
    $sToRender .= "<a href=\"$default->rootUrl/control.php?action=listDocFields\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a>";
    
    $sToRender .= "</td></tr>\n";
    $sToRender .= "</table>";    
    
    $sToRender .= "<table width=\"600\" border=0 ><tr><td>";
    $sToRender .= "<table border=0 >";
    
	$sToRender .= "<tr ><td valign=top>" . $LookupDisplay . "</td></tr>\n";
	$sToRender .= "<tr><td height=130><br></td></tr>\n";
	$sToRender .= "</table>";    
	
	$sToRender .= "</td><td valign=top>\n";
    
    $sToRender .= "<table border=0 cellspacing=1>\n";
    $sToRender .= "<tr><td><b>Options</b></td></tr>";
    $sToRender .= "<tr><td width=200 bgcolor=$sBgColor>";
    $sToRender .= "<br><a href=\"$default->rootUrl/control.php?action=addMetaDataForField&fDocFieldID=$iDocFieldID\">&nbsp;&nbsp;" . _("Add Document Field Lookups") . "</a><br><br>";
    $sToRender .= "</td><td>"; 	
    $sToRender .= "</td></tr>";
	
	$iDocFieldLookupCount = DocumentField::getLookupCount($iDocFieldID);
	if ($iDocFieldLookupCount > 0) {
		
		$sToRender .= "<tr><td width=200 bgcolor=$sBgColor>";
	    $sToRender .= "<br><a href=\"$default->rootUrl/control.php?action=editMetadataForField&fDocFieldID=$iDocFieldID\">&nbsp;&nbsp;" . _("Edit Document Field Lookups") . "</a><br><br>";
	    $sToRender .= "</td><td>"; 	
	    $sToRender .= "</td></tr>";
	    
	    $sToRender .= "<tr><td width=200 bgcolor=$sBgColor>";
	    $sToRender .= "<br><a href=\"$default->rootUrl/control.php?action=removeMetaDataFromField&fDocFieldID=$iDocFieldID\">&nbsp;&nbsp;" . _("Remove Document Field Lookups") . "</a><br><br>";
	    $sToRender .= "</td><td>";    
	    $sToRender .= "</td></tr>";
	}
    $sToRender .= "</table>\n";
    
    $sToRender .= "</td></tr>";
    $sToRender .= "</table>\n";

    return $sToRender;
}

?>
