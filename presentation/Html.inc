<?php
/**
 * $Id$
 *  
 * Generic html building functions.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation
 */

function renderHeading($sHeading, $sSectionName = "") {
	global $default;
	if (strlen($sSectionName) == 0) {
    	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	}
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"100%\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">$sHeading</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
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

?>
