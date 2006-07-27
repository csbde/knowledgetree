<?php
/**
 * $Id$
 *
 * Online context-sensitive help page.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 */
 
require_once("../../../config/dmsDefaults.php");

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

require_once(KT_LIB_DIR . '/help/help.inc.php');
print KTHelp::getHelpStringForSection($_REQUEST['fAction']);
?>
