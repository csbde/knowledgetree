<?php
/**
 * $Id$
 *
 * This page holds all presentation code for expunging documents pages.
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
 * @package administration.documentmanagement
 */

/**
 * Gives the user a last chance to bail out before expunging the documents
 */
function renderConfirmDocuments($aDocuments) {
	global $default;
	
	$sToRender  = renderHeading("Expunge Deleted Documents");
	$sToRender .= "<table>";
    $sToRender .= "<tr><td>" . _("The documents you have chosen to expunge are listed below.") . "</td></tr>\n";
    $sToRender .= "<tr><td>" . _("Click 'Expunge' to confirm deletion, or 'Cancel' to abort.") . "</td></tr>\n";
    $sToRender .= "<tr/><tr/>";

    // loop through them    
    for ($i = 0; $i < count($aDocuments); $i++) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "\t\t<td bgcolor=\"" . getColour($i) . "\">\n";
        $sToRender .= "<input type=\"hidden\" name=\"fDocumentIDs[]\" value=\"" . $aDocuments[$i]->getID() . "\"/>\n";        
        $sToRender .= $aDocuments[$i]->getDisplayPath(true) . "\n";
        $sToRender .= "\t\t</td>\n";
        $sToRender .= "\t</tr>\n";
    }
    $sToRender .= "<tr/><tr/>";
    $sToRender .= "<input type=\"hidden\" name=\"fConfirm\" value=\"1\">";
    $sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/expunge.gif\" border=\"0\"/></a>\n";
    $sToRender .= generateControllerLink("deletedDocuments", "", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></td></tr>");
	$sToRender .= "</table>";
    return $sToRender;	
}

/**
 * Displays the status of expunged documents
 */
function renderStatusPage($aSuccessDocuments, $aErrorDocuments) {
	global $default;
	
	$sToRender  = renderHeading("Expunge Deleted Documents Status");
	$sToRender .= "<table>";
	if (count($aSuccessDocuments) > 0) {
	    $sToRender .= "<tr><td>" . _("The following documents were successfully expunged:") . "</td></tr>\n";
	    $sToRender .= "<tr/><tr/>";
	    for ($i=0; $i<count($aSuccessDocuments); $i++) {
			$sToRender .= "<tr><td>" . $aSuccessDocuments[$i] . "</td></tr>\n";
	    }
	}
	if (count($aErrorDocuments) > 0) {
    	$sToRender .= "<tr><td>" . _("There were errors restoring the following documents:") . "</td></tr>\n";
	    $sToRender .= "<tr/><tr/>";    	
	    for ($i=0; $i<count($aErrorDocuments); $i++) {    	
    		$sToRender .= "<tr><td>" . $aErrorDocuments[$i] . "</td></tr>\n";
	    }
	}
	$sToRender .= "<tr/><tr/>";	
    $sToRender .= "<tr><td>" . generateControllerLink("deletedDocuments", "", "<img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"></td></tr>");
	$sToRender .= "</table>";
    return $sToRender;	
}
?>
