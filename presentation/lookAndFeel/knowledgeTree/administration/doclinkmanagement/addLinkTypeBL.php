<?php
/**
 * $Id$
 *
 * Add a document link type
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
 
require_once("../../../../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");

require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/administration/adminUI.inc");
require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

require_once("$default->fileSystemRoot/lib/documentmanagement/LinkType.inc");
require_once("addLinkTypeUI.inc");

if (!checkSession()) {
    die("Session failed");
}

KTUtil::extractGPC('fLinkID');

if ($submitted) {
    // include the page template (with navbar)

    $sToRender = renderHeading(_("Add Link Type"));
    $sToRender .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $sToRender .= "<tr>\n";
    if ($fLinkID != -1) {
        $sToRender .= "<td><b>" . _("New Link Type Added SuccessFully") . "!<b></td></tr>\n";
    } else {
        $sToRender .= "<td><b>" . _("Addition Unsuccessful") . "</b>...</td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr></tr>\n";
        $sToRender .= "<tr></tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>Please Check Name and Rank for duplicates!</td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .="<td>&nbsp;</td>\n";
    }

    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td align = right><a href=\"$default->rootUrl/control.php?action=addLinkType\">" . KTHtml::getBackButton() . "</a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml($sToRender);
    $main->setCentralPayload($oPatternCustom);
    $main->render();
} else {
	$oPatternCustom = & new PatternCustom();
	$oPatternCustom->setHtml(getPage());
	$main->setCentralPayload($oPatternCustom);
	$main->setFormAction("$default->rootUrl/presentation/lookAndFeel/knowledgeTree/create.php?fRedirectURL=".urlencode("$default->rootUrl/control.php?action=addLinkTypeSuccess&fLinkID="));
    $main->setHasRequiredFields(true);
	$main->render();
}

?>
