<?php
/**
 * $Id$
 *
 * Add document type UI functions.
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
 
// get add pages
function getPage() {

	global $default;
	$aDisplayRowNames = array(_("Document Type Name") . ":");
	$aParameterNumbers = array(0);
	$aDisplayColumnTypes = array(1);
	$aRequired = array(1);
	//$aDropDownListTableNames = array(10=>"document_types_lookup");

	// create a text field that is required 
	$oPatternCreate = & new PatternCreate("DocumentType", "documentmanagement/DocumentType.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired);
	$oPatternCreate->setUniqueName("documentType");
	
	$sToRender .= renderHeading(_("Add Document Type"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPatternCreate->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td align = right><input type=\"image\" src =\"" . KTHtml::getAddButton() . "\" value=\"submit\" border=\"0\"/>";
	$sToRender .= getCancelButton("listDocTypes") . "</td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
	
	
	
}
?>
