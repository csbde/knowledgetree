<?php
/**
 * $Id$
 *
 * Edit system settings UI functions.
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
 * @package administration.systemsettings
 */
// gets the delete stuff
function getPage() {
    global $default;
    $oSystem = $default->system;

    $sToRender .= renderHeading("System Settings");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    for($i = 0; $i < count($oSystem->aSettings); $i++) {
    	if ($oSystem->initialised()) {
    		$sValue = $oSystem->get($oSystem->aSettings[$i]);
    	} else {
    		$tmp = $oSystem->aSettings[$i];
    		$sValue = $default->$tmp;
    	}
    	if (is_array($sValue)) {
        	$sValue = implode(";", $sValue);
        }
        $sToRender .= "<tr><td>". ($i+1) .".</td><td>" . $oSystem->aLabels[$i] ." </td><td><input size=\"40\" type=\"text\" name=\"" . $oSystem->aSettings[$i] . "\" value =\"$sValue\"></td></tr>\n";
    }

    // do some validation
    $sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
    $sToRender .= "function validateForm(theForm) {\n";
    for ($i = 0; $i < count($oSystem->aSettings); $i++) {
        $sToRender .= "\tif (!(validRequired(document.MainForm." . $oSystem->aSettings[$i] . ", '" . $oSystem->aLabels[$i] . "'))) {\n";
        $sToRender .= "\t\treturn false;\n\t}\n";
    }
    $sToRender .= "return true;\n}\n";
    $sToRender .= "//-->\n</script>\n\n";

    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td></td><td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"Submit\" border=\"0\"/></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}
?>