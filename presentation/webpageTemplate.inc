<?php
/**
 * $Id$
 *
 * Used in conjunction with the navbar to create the north, west and north west payloads
 * and in doing so creating the webpage template. 
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
 */

require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternImage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableLinks.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/NavBar.inc");

// create the navbar and north payload
$navbar = & new NavBar($fBrowseType, $fFolderID, $fDocumentID, $fCategoryName, $fDocumentTypeID);

//get the section name if not specified in the url
if ($sectionName == "") {
    $sectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
}

//highlight the respective section
$navbar->setHighlightedSection($sectionName);

// setup nw payload
$colourBar = $navbar->getNWColourbar();
$nwestLoad   = "<table cellspacing=0 cellpadding=0 border=0>\n" . 
               "<tr><td><img valign=\"top\" src=\"$default->graphicsUrl/tree.gif\"/></td></tr>\n" .
               "<tr><td><img valign=\"top\" src=\"$colourBar\"/></td></tr>\n" .
               "</table>";

$nwPayLoad = new PatternCustom;
$nwPayLoad ->setHtml($nwestLoad);

// set up the west payload
//get section links
if ($sectionName != "General") {

	$bSortLinks = !($sectionName == "Administration");

    $sectionArray = $default->siteMap->getSectionLinks($sectionName, $bSortLinks);

    $output1["links"] = $sectionArray["links"];
    $output2["descriptions"] = $sectionArray["descriptions"];

    //create links
    $westLinks = new PatternTableLinks($sectionArray["links"], $sectionArray["descriptions"], 15 , 1 ,1,null);
    $westRender = $westLinks->render();

} else {
    $westLinks = new PatternTableLinks(array(), array(), 20, 1 ,1,null);
    $westRender = $westLinks->render();
}

//set up links
$cAdmin = "$default->graphicsUrl/leftnav/cadmin.gif" ;
$uAdmin = "$default->graphicsUrl/leftnav/uadmin.gif" ;

$oUnitAdmin = User::getUnitAdminUser();
$sUnitAdminEmail = $oUnitAdmin ? $oUnitAdmin->getEmail() : $default->emailAdmin; 
//setup the westpayload
$westLoad = "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" width=\"100%\" height=\"75%\">\n" .
            "\t<tr valign=\"top\"><td>$westRender</td></tr>\n" .
            "</table>" .
            "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n" .            
            "\t<tr valign=\"bottom\"><td align=left><a href=\"mailto:$default->emailAdmin\"><img src=\"$cAdmin\"/ border=\"0\"></a></td></tr>\n" .
            "\t<tr valign=\"top\"><td align=left><a href=\"mailto:$sUnitAdminEmail\"><img src=\"$uAdmin\"/ border=\"0\"></a></td></tr>\n" .
            "</table>";

$westPayLoad = new PatternCustom;
$westPayLoad->setHtml($westLoad);

$main = new PatternMainPage();
$main->setNorthPayload($navbar);
$main->setNorthWestPayload($nwPayLoad);
$main->setWestPayload($westPayLoad);
$main->setWestPayloadBackGroundColour("#F0F0E4");
?>
