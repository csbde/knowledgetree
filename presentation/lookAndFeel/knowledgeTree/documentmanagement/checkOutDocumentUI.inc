<?php
/**
 * $Id$
 *
 * Check out document UI functions.
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
 * @package documentmanagement
 */

/**
 * Prompts the user for a checkout comment
 *
 * @param object the document we're checking out
 */
function renderCheckOutPage($oDocument) {
	global $default;
	
	$sToRender  = "<table>\n";
	$sToRender .= "<tr><td>" . _("Enter a check out comment for the document") . "</td></tr>\n";
	$sToRender .= "<tr/>\n";
	$sToRender .= "<tr><td><input type=\"text\" name=\"fCheckOutComment\" size=\"30\"/></td></tr>\n";
    $sToRender .= "<tr><td><input type=\"hidden\" name=\"fForStore\" value=\"1\"/></td></tr>\n";
    $sToRender .= "<tr><td><input type=\"hidden\" name=\"fDocumentID\" value=\"" . $oDocument->getID() . "\"/></td></tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
    $sToRender .= "<td><input type=\"image\" border=\"0\" src =\"$default->graphicsUrl/widgets/checkout.gif\" value=\"Submit\"/>\n";
	$sToRender .= generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID(), "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\">") . "</td>\n";	    
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;    
}

/**
 * Displays the document view page with additional input controls
 * for checking out the document
 *
 * @param object the document to view and check out
 */
function getCheckOutPage($oDocument, $bCheckedOut = false) {
    global $default;

	$sToRender .= renderHeading(_("Check Out Document"));
	$sToRender .= renderDocumentPath($oDocument, false) . "\n\n";
    if ($bCheckedOut) {
    	$sToRender .= "<table>";
        $sToRender .= "<tr><td>" . _("You have now checked out this document. No one else can make updates to the document while you have it checked out. Click the Download button to get the latest version of the file. Please check it back in, as soon as you finish working on it.") . "</td></tr>\n";
        $sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=downloadDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/download.gif\" border=\"0\" /></a>\n";    
        $sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a></td>\n";
        $sToRender .= "</table>";        
    } else {
        $sToRender .= renderCheckOutPage($oDocument);
    }
   	$sToRender .= renderDocumentData($oDocument, $bEdit);       	
    return $sToRender;
}
?>
