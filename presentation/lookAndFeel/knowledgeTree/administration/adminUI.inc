<?php
/**
 * $Id$
 *
 * Contains UI methods common to all administration screens.
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration
 */
 
function getAddLink($sAddAction, $sAddText) {
	global $default;
	$sBGColor = $default->siteMap->getSectionColour("Administration", "td");
	return "<table width=\"100%\"><tr><td bgcolor=\"$sBGColor\">" . generateControllerLink("$sAddAction", "", "<font color=\"#ffffff\">$sAddText</font>") . "</td></tr></table>\n";	
}

function getCancelButton($sCancelAction, $sQueryString = "") {
	global $default;
	return generateControllerLink($sCancelAction, $sQueryString, "<img border=\"0\" src=\"" . KTHtml::getCancelButton() . "\"/>");
}
?>