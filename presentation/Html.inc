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
    return "<table border=\"$border\" width=\"$width\">";
}

function stopTable() {
    return "</table>";
}

function tableRow($align, $bgcolor, $html) {
    return "<tr align=\"$align\" bgcolor=\"$bgcolor\">$html</tr>";
}

function tableHeading($cssClassName, $colspan, $text) {
    return "<th colspan=\"$colspan\">
            <span class=\"$cssClassName\">$text</span>
            </th>";
}

function tableData($html) {
    return "<td>$html</td>";
}

function startTableRowCell() {
    return "<tr><td>";
}

function endTableRowCell() {
    return "</td></tr>";
}

function generateLink($sTargetPage, $sQueryString, $sLinkText) {
    return "<a href=\"$sTargetPage?$sQueryString\">$sLinkText</a>";
}

?>
