<?php
/**
 * $Id$
 *
 * Generic html building functions..
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
 * @package administration.docfieldmanagement
 */

function renderHeading($sHeading, $sSectionName = "") {
	global $default;
	if (strlen($sSectionName) == 0) {
    	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	}
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"610\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">$sHeading</font></th></tr>\n";
    $sToRender .= "</table>\n";
    return $sToRender;	
}

function statusPage($sTitle, $sHeading, $sText = "", $sCancelAction, $sQueryString = "") {
    global $default;
    $sToRender .= renderHeading($sTitle);
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>$sHeading</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td>$sText</td></tr>\n";
    $sToRender .= "<tr><td align=\"right\">" . generateControllerLink($sCancelAction, $sQueryString, "<img src =\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"/>") . "</td></tr>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

function startTable($border, $width) {
    return "<table border=\"$border\" width=\"$width\">\n";
}

function tableCaption($sCaption) {
    return "<caption><b>$sCaption</b></caption>\n";
}

function stopTable() {
    return "</table>\n";
}

function tableRow($align, $bgcolor, $html) {
    return "<tr align=\"$align\" bgcolor=\"$bgcolor\">$html</tr>\n";
}


function tableHeading($cssClassName, $colspan, $text) {
    return "<th class=\"$cssClassName\" colspan=\"$colspan\">\n
           $text\n
           </th>\n";
}

function tableData($html, $iColspan = 1) {
    return "<td colspan = $iColspan>$html</td>\n";
}

function tableCssData($html, $cssClass) {
    return "<td><span class=\"$cssClass\">$html</span></td>\n";
}

function startTableRowCell() {
    return "<tr><td>\n";
}

function textInput($sId, $sValue) {
    return "<input type=text id=\"$sId\" value=\"$sValue\" />\n";
}

function endTableRowCell() {
    return "</td></tr>\n";
}

function generateImage($sImageSource) {
    return "<img border=\"0\" src=\"$sImageSource\">";
}

function getNumberStringValidationJavaScript() {
    return "<script language=\"JavaScript\">\n".
           "<!--\n" .
           "function validateString(field, msg, min, max) {\n" .
           "\tif (!min) { min = 1 }\n" .
           "\tif (!max) { max = 65535 }\n\n" .

           "\tif (!field.value || field.value.length < min || field.value.max > max) {\n" .
           "\t\talert(msg);\n" .
           "\t\tfield.focus();\n" .
           "\t\tfield.select();\n" .
           "\t\treturn false;\n" .
           "\t}\n" .
           "\treturn true;\n" .
           "}\n\n" .

           "function validateNumber(field, msg, min, max) {\n" .
           "\tif (!min) { min = 0 }\n" .
           "\tif (!max) { max = 255 }\n" .

           "\tif ( (parseInt(field.value) != field.value) || field.value.length < min || field.value.length > max) {\n" .
           "\t\talert(msg);\n" .
           "\t\tfield.focus();\n" .
           "\t\tfield.select();\n" .
           "\t\treturn false;\n" .
           "\t}\n" .
           "\treturn true;\n" .
           "}\n" .
           "//-->\n" .
           "</script>\n";
}

/**
 * Candy striping helper method
 */
function getColour($iColourCount) {
	return ($iColourCount%2 == 0) ? "F5F6EE" : "FFFFFF";
}
?>
