<?php
/**
 * $Id$
 *
 * Presentation information used by deleteFolderDocTypeUI.inc.
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
 * @package foldermanagement
 */

function getPage($iFolderID) {
	global $default;
	$sToRender = "<table border=\"0\" width=\"100%\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderPath($iFolderID, generateControllerUrl("editFolder", "fFolderID=$iFolderID&fShowSection=documentTypes", false), false) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    $sToRender .= "<table border=\"0\">\n";    
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . generateControllerLink("editFolder", "fFolderID=$iFolderID&fShowSection=documentTypes", "<img src=\"" . KTHtml::getBackButton() . "\" border=\"0\">") . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table><br>\n";
	
	return $sToRender;
}

?>
