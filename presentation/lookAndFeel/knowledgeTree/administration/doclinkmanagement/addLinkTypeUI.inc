<?php
/**
 * $Id$
 *
 * Add a document link type UI functions.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */
// get add pages
function getPage() {

	global $default;

	$aDisplayRowNames = array(_("Name") . ":", _("Description") . ":");
	$aParameterNumbers = array(0,1);
	$aDisplayColumnTypes = array(1,1);
	$aRequired = array(1,1);

	// create a text field that is required 
	$oPatternCreate = & new PatternCreate("LinkType", "documentmanagement/LinkType.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired);
	$oPatternCreate->setUniqueName("linktype");
	
	$sToRender .= renderHeading(_("Add Link Type"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPatternCreate->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr><td align = right><input type=\"image\" src =\"" . KTHtml::getAddButton() . "\" value=\"Submit\" border=\"0\"/>";
	$sToRender .= getCancelButton("listLinkTypes") . "</td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

 // TODO .. do a check if rank already taken...as well as do a check if name already exists when inserting
 // if edition is successful print out success page
function getAddPageFail() {	
	global $default;
			
    $sToRender .= renderHeading(_("Add Link Type"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>" . _("Addition Unsuccessful") . "</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addLinkType\">" . KTHtml::getBackButton() . "</a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}
?>
