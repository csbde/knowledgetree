<?php
/**
 * $Id$
 *
 * Add an organisation success page.
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
 * @package administration.orgmanagement
 */
require_once("../../../../../config/dmsDefaults.php");

global $default;

if(checkSession()) {

    // include the page template (with navbar)
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

    $sToRender .= renderHeading(_("Add Organisation"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    if($fSuccess) {
    	$sToRender .= "<td>" . _("Organisation added Successfully!") . "</td>\n";
    } else {
    	$sToRender .= "<td>" . _("Organisation not added. Organisation may already exist!") . "</td>\n";
    }
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=listOrg\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml($sToRender);
    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
