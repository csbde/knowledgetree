<?php
/**
 * $Id$
 *
 * Add a website UI functions.
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
 * @package administration.websitemanagement
 */
// get add pages
function getPage() {
	global $default;

	$aDisplayRowNames = array(_("Website Name") . ":", _("Website URL") . ": ", _("WebMaster"));
	$aParameterNumbers = array(0,1,2);
	$aDisplayColumnTypes = array(1,1,3);
	$aRequired = array(1,1,1);
	$aDropDownListTableNames = array(2 => "$default->users_table");
	$aDropDownListValueColumns = array(2=>'id');
	$aDropDownListDisplayColumns = array(2=>'username');

	// create a text field that is required 
	$oPatternCreate = & new PatternCreate("Website", "web/WebSite.inc",$aDisplayRowNames, $aParameterNumbers, $aDisplayColumnTypes, $aRequired,$aDropDownListTableNames,$aDropDownListValueColumns,$aDropDownListDisplayColumns);
	$oPatternCreate->setUniqueName("website");
    $oPatternCreate->setDefaultValues(array(1=>"http://"));
	
    $sToRender .= renderHeading(_("Add Website"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . $oPatternCreate->render() . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<td></td><td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" value=\"Submit\" border=\"0\"/>";
	$sToRender .= getCancelButton("listWebsites") . "</td>\n";	
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

 // TODO .. do a check if rank already taken...as well as do a check if name already exists when inserting
 // if edition is successful print otu success page
function getAddPageFail() {	
	global $default;
			
    $sToRender .= renderHeading(_("Add Website"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr><td><b>Addition Unsuccessful</b></td></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addWebsite\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}
?>
