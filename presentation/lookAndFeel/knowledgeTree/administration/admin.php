<?php
require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/presentation/Html.inc");

/**
 * $Id$
 *
 * Displays the administration splash page.
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

/**
 * Display the heading for the admin section
 */
function renderAdminHeading($sHeading, $sSectionName = "") {
	global $default;
	
    $sAction = $default->siteMap->getActionFromPage(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sCenter .= renderHeading($default->siteMap->getPageLinkText($sAction));
    
    $sCenter .= "<table width=\"600\">\n";
    $sCenter .=	"<tr><td>Please make a selection from the sidemenu.</td></tr>\n";
    $sCenter .=	"</table>\n";
    return $sCenter;	
}

if (checkSession()) {
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

	if (!isset($sectionName)) {
		$sectionName = "Administration";
	}
    $sCenter .= "<table width=\"600\">\n";
    $sCenter .= renderAdminHeading("Administration", $sectionName);	        
    $sCenter .=	"</table>\n";

	$oPatternCustom = & new PatternCustom();
	$oPatternCustom->setHtml($sCenter);
	$main->setCentralPayload($oPatternCustom);
	$main->render();
}
?>
