<?php
/**
 * $Id$
 *  
 * Generic html building functions.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
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

function tableData($html) {
    return "<td>$html</td>";
}

function startTableRowCell() {
    return "<tr><td>";
}

function endTableRowCell() {
    return "</td></tr>";
}

?>
