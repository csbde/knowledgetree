<?php
/**
 * $Id$
 *
 * Succesfull document field addition.
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

require_once("../../../../../config/dmsDefaults.php");

if(checkSession()) {
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	
	$oDocField = DocumentField::get($fDocFieldID);
	if ($oDocField) {
		// if we're setting lookup to be true, then prompt for an initial lookup value??
		if ($oDocField->getHasLookup()) {
			// and there are no metadata values for this lookup
			// there shouldn't be since this has just been added- but lets be paranoid shall we?
			if (DocumentField::getLookupCount($fDocFieldID) == 0) {
				// then redirect to the edit metadata page
				controllerRedirect("addMetaDataForField", "fDocFieldID=$fDocFieldID");
			}
		}
	}

    // include the page template (with navbar)
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	global $default;
    $Center .= renderHeading("Add Document Field");
    $Center .= "<TABLE BORDER=\"0\" CELLSPACING=\"2\" CELLPADDING=\"2\">\n";
    $Center .= "<tr>\n";
    if ($fDocFieldID == -1) {
        $Center .= "<td><b>" . _("Document Field addition Unsuccessful!") . "</b></td>\n";
        $Center .= "</tr>\n";
        $Center .= "<tr></tr>\n";
    } else {
        $Center .= "<td><b>" . _("Document Field added Successfully!") . "</b></td>\n";
        $Center .= "</tr>\n";
    }
    $Center .= "<tr></tr>\n";
    $Center .= "<tr></tr>\n";
    $Center .= "<tr></tr>\n";
    $Center .= "<tr></tr>\n";
    $Center .= "<tr>\n";
    $Center .= "<td align = right><a href=\"$default->rootUrl/control.php?action=addDocField\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $Center .= "</tr>\n";
    $Center .= "</table>\n";

    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml($Center);
    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
