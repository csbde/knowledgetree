<?php
/**
 * $Id$
 *
 * Check in document UI functions.
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
 * Prompts the user for a check in comment and revision type
 *
 * @param object the document we're checking in
 */
function renderCheckInPage($oDocument) {
	global $default;
	
	$sToRender  = "<table>\n";
	$sToRender .= "<tr><td>" . _("Select the file to check in") . ":</td></tr>\n";
	$sToRender .= "<tr><td><input type=\"file\" name=\"fFile\">";
    /*
	$sToRender .= "<tr><td><input type=\"file\" name=\"browse\" style=\"display:none\">";
    $sToRender .= "<input type=\"text\" name=\"fFile\"\>\n";
    $sToRender .= "<a href=\"#\" onClick=\"document.MainForm.browse.click();document.MainForm.fFile.value=document.MainForm.browse.value;return false;\">";
    $sToRender .= "<img src=\"" . KTHtml::getBrowseButton() . "\" alt=\"browse\" border=\"0\"></a>\n";
    */
    $sToRender .= "</td></tr>\n";
    $sToRender .= "</table>\n";
    
	$sToRender .= "<table>\n";  
	$sToRender .= "<tr><td>" . _("Enter a check in comment for the document") . "</td></tr>\n";
	$sToRender .= "<tr/>\n";
	$sToRender .= "<tr><td><input type=\"text\" name=\"fCheckInComment\" size=\"30\"/></td></tr>\n";
    $sToRender .= "<input type=\"hidden\" name=\"fForStore\" value=\"1\"/>\n";
    $sToRender .= "<input type=\"hidden\" name=\"fDocumentID\" value=\"" . $oDocument->getID() . "\"/>\n";     
    // if collaboration has started then revisions are minor
    if (DocumentCollaboration::documentCollaborationStarted($oDocument->getID())) {
        $sToRender .= "<input type=\"hidden\" name=\"fCheckInType\" value=\"minor\"/>\n";
    // else there is no collaboration, revisions are major (or override?)        
    } else {
        $sToRender .= "<input type=\"hidden\" name=\"fCheckInType\" value=\"major\"/>\n";
    }

    /*
    $sToRender .= "<tr><td>\n";
    $sToRender .= "<input type=\"radio\" name=\"fCheckInType\" value=\"minor\" checked /> Minor Revision\n";    
    $sToRender .= "</tr></td>\n";
    $sToRender .= "<tr><td>\n";
    $sToRender .= "<input type=\"radio\" name=\"fCheckInType\" value=\"major\"/> Major Revision\n";
    $sToRender .= "</tr></td>\n";
    */
    $sToRender .= "</table>\n";
    
    $sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	// FIXME: if the document name contains an apostrophe this js doesn't work
	$sToRender .= "<td><input type=\"image\" border=\"0\" src =\"" . KTHtml::getCheckInButton() . "\" value=\"Submit\" onClick=\"if (validRequired(document.MainForm.fFile, 'Check-In File') && validRequired(document.MainForm.fCheckInComment, 'Check-In Comment')) { return confirm('Are you sure you want to overwrite " . $oDocument->getFileName() . " with ' + document.MainForm.fFile.value + '?'); } else return false;\"/>\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID() . "\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a></td>\n";	    
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";

	/*
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateCheckIn() {\n";
	$sToRender .= "alert('blah'); return false;\n";
	$sToRender .= "  if (validRequired(document.MainForm.fFile, 'Check-In File') && validRequired(document.MainForm.fCheckInComment, 'Check-In Comment')) {\n";
	$sToRender .= "    var directoryTokensArray=document.MainForm.fComment.split('\');\n";
	$sToRender .= "    if (directoryTokensArray.length == 0) {\n";
	$sToRender .= "      directoryTokensArray = document.MainForm.fComment.split('/');\n";
	$sToRender .= "    }\n";
    //$sToRender .= "alert('filename=' + directoryTokensArray[directoryTokensArray.length-1]);\n";	
	$sToRender .= "    if ('" . $oDocument->getFileName() . "' <> directoryTokensArray[directoryTokensArray.length-1]) {\n";
    $sToRender .= "      alert('Please check in the same file, you attempted to check in ' + document.MainForm.fComment + '!');\n";
    $sToRender .= "      return false;\n";
	$sToRender .= "    } else {\n";    
	$sToRender .= "      return confirm('Are you sure you want to overwrite " . $oDocument->getName() . " with ' + document.MainForm.fFile.value + '?');\n";
	$sToRender .= "    }\n";
	$sToRender .= "  } else {\n";
	$sToRender .= "    return false;\n";
	$sToRender .= "  }\n";
	$sToRender .= "}\n";	
	$sToRender .= "//-->\n</script>\n\n";
	*/
	return $sToRender;
}

/**
 * Displays the document view page with additional input controls
 * for checking in the document
 *
 * @param object the document to view and check out
 */
function getCheckInPage($oDocument) {
    $sToRender .= renderHeading(_("Check In Document"));
    $sToRender .= renderDocumentPath($oDocument, false) . "\n\n";
	$sToRender .= renderCheckInPage($oDocument);
	$sToRender .= renderDocumentData($oDocument, $bEdit);   
	return $sToRender;
}
?>
