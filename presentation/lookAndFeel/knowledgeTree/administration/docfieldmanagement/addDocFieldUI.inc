<?php
/**
 * $Id$
 *
 * Add document field UI functions.
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
 * @package administration.docfieldmanagement
 */

// get add pages
function getPage() {

	global $default;
	$aDisplayRowNames = array(_("Document Field Name") . ":", _("Data Types") . ": ", _("Generic") . ": ", _("Has a Lookup") . ": ");
	$aParameterNumbers = array(0,1,2,3);
	$aDisplayColumnTypes = array(1,3,2,2);
	$aRequired = array(1,1);
	$aDropDownListTableNames = array(1 => "$default->data_types_table");
	$aDropDownListValueColumns = array(1=>'name');
	$aDropDownListDisplayColumns = array(1=>'name');

	// create a text field that is required 
	$oPatternCreate = & new PatternCreate("DocumentField", "documentmanagement/DocumentField.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired,$aDropDownListTableNames,$aDropDownListValueColumns,$aDropDownListDisplayColumns);
	$oPatternCreate->setUniqueName("DocField");
	
	$sToRender .= renderHeading(_("Add Document Field"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPatternCreate->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td align = right><input type=\"image\" src =\"" . KTHtml::getAddButton() . "\" value=\"Submit\" border=\"0\"/></td>\n";
    $sToRender .= "<td align = right>" . getCancelButton("listDocFields") . "</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
}
?>
