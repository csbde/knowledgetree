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

function startTable($border, $width) {
    return "<table border=\"$border\" width=\"$width\">\n";
}

function stopTable() {
    return "</table>\n";
}

function tableRow($align, $bgcolor, $html) {
    return "<tr align=\"$align\" bgcolor=\"$bgcolor\">$html</tr>\n";
}

function tableHeading($cssClassName, $colspan, $text) {
    return "<th colspan=\"$colspan\">\n
            <span class=\"$cssClassName\">$text</span>\n
            </th>\n";
}

function tableData($html) {
    return "<td>$html</td>\n";
}

function tableCssData($html, $cssClass) {
    return "<td><span class=\"$cssClass\">$html</span></td>\n";
}

function startTableRowCell() {
    return "<tr><td>\n";
}

function endTableRowCell() {
    return "</td></tr>\n";
}

function generateLink($sTargetPage, $sQueryString, $sLinkText) {
    return "<a href=\"$sTargetPage?$sQueryString\">$sLinkText</a>\n";
}

function generateImage($sImageSource) {
    return "<img border=\"0\" src=\"$sImageSource\">";
}

?>
