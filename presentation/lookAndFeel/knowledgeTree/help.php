<?php
/**
 * $Id$
 *
 * Online context-sensitive help page.
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
 */
 
require_once("../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/presentation/Html.inc");
global $default;
$heading = "$default->graphicsUrl/heading.gif";
$hStretched = "$default->graphicsUrl/hrepeat.gif";
$row1 = "<img src = ". $heading. ">";

//Output a title bar
$headingBar  = "<table cellpadding=\"0\" cellspacing=\"0\" border=\"1\" width=\"100%\" height=\"10%\">\n";
$headingBar .= "\t<tr height=\"20%\">\n";
$headingBar .= "\t\t<td background=\"$hStretched\" width=\"100%\"><img src=\"$heading\"/></td>\n";
$headingBar .= "\t</tr>\n";
$headingBar .= "</table>\n";
echo $headingBar;

//Query the database for the helpURL based on the current action
$sQuery = "SELECT HLP.help_info as helpinfo ".
		"FROM $default->help_table AS HLP WHERE '$fAction' = HLP.fSection";
		
$sql = $default->db;
$sql->query($sQuery);

if ($sql->next_record()) {
	require_once("$default->uiDirectory/help/" . $sql->f("helpinfo"));
} else {
	echo "No help available for $fAction";
}
?>