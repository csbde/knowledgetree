<?php
/**
 * $Id$
 *
 * This page holds all presentation code for displaying document archiving searching. 
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
 * Displays the status of restored documents
 */
function renderStatusPage($aSuccessDocuments, $aErrorDocuments) {
	global $default;
	
	$sToRender  = renderHeading(_("Archived Documents Restoration Status"));
	$sToRender .= "<table>";
	if (count($aSuccessDocuments) > 0) {
	    $sToRender .= "<tr><td>" . _("The following documents were successfully restored:") . "</td></tr>\n";
	    $sToRender .= "<tr/><tr/>";
	    for ($i=0; $i<count($aSuccessDocuments); $i++) {
			$sToRender .= "<tr><td>" . generateControllerLink("viewDocument", "fDocumentID=" . $aSuccessDocuments[$i]->getID(), $aSuccessDocuments[$i]->getDisplayPath()) . "</td></tr>\n";
	    }
	}
	if (count($aErrorDocuments) > 0) {
    	$sToRender .= "<tr><td>" . _("There were errors restoring the following documents:") . "</td></tr>\n";
	    $sToRender .= "<tr/><tr/>";    	
	    for ($i=0; $i<count($aErrorDocuments); $i++) {    	
    		$sToRender .= "<tr><td>" . $aErrorDocuments[$i]->getDisplayPath(true) . "</td></tr>\n";
	    }
	}
	$sToRender .= "<tr/><tr/>";	
    $sToRender .= "<tr><td>" . generateControllerLink("archivedDocuments", "", "<img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"></td></tr>");
	$sToRender .= "</table>";
    return $sToRender;	
}

/**
 * Gives the user a last chance to bail out before restoring the documents
 */
function renderRestoreConfirmationPage($aDocuments) {
	global $default;
	
	$sToRender  = renderHeading(_("Restore Archived Documents"));
	$sToRender .= "<table>";
    $sToRender .= "<tr><td>" . _("The documents you have chosen to restore from the archive are listed below.") . "</td></tr>\n";
    $sToRender .= "<tr><td>" . _("Click 'Restore' to confirm restoration, or 'Cancel' to abort.") . "</td></tr>\n";
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
    $sToRender .= "<tr><td><input type=\"image\" src=\"" . KTHtml::getRestoreButton() . "\" border=\"0\"/></a>\n";
    $sToRender .= "<a href=\"javascript:history.go(-1)\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a></td></tr>";
	$sToRender .= "</table>";
    return $sToRender;	
}

/**
 * Displays the archived document search form
 */ 
function renderSearchPage($aMetaTagIDs = array()) {
	global $default;
    $sToRender  = renderHeading(_("Archived Documents Search"));
    
	$sToRender .= "<table width=\"600\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Search text:") . " <input type=\"text\" size=\"60\" name=\"fSearchString\" value=\"$sSearchString\" />\n";
	$sToRender .= "<input type=\"image\" src=\"" . KTHtml::getSubmitButton() . "\" border=\"0\"></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getMetaData($aMetaTagIDs) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";
	
	$sToRender .= "\n\n<SCRIPT LANGUAGE=\"javascript\">\n ";
	$sToRender .= "<!--\n";		
	$sToRender .= "function validateForm() {\n";	
	$sToRender .= "\tif (!(validRequired(document.MainForm.fSearchString, 'Search text'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "\treturn true;\n}\n";
	$sToRender .= "-->\n";
	$sToRender .= "</SCRIPT>\n\n";
	
	return $sToRender;
}

/**
 * Performs the search and displays the results
 */
function renderArchivedDocumentsResultsPage($aDocuments) {
	global $default;

	$sToRender  = renderHeading(_("Archived Documents Search Results"));
	$sToRender .= "<table>";			
    $sToRender .= "<tr><td>" . _("Select the archived documents you'd like to restore, and click 'Restore', or 'Cancel' to abort") . "</td></tr>\n";
	for ($i=0; $i<count($aDocuments); $i++) {
		// and print document paths with checkboxes
        $sToRender .= "\t<tr>\n";
        $sToRender .= "\t\t<td bgcolor=\"" . getColour($i) . "\">\n";
        $sToRender .= "<input type=\"checkbox\" name=\"fDocumentIDs[]\" value=\"" . $aDocuments[$i]->getID() . "\"/>\n";
        $sToRender .= $aDocuments[$i]->getDisplayPath(true) . "\n";
        $sToRender .= "\t\t</td>\n";
        $sToRender .= "\t</tr>\n";			
	}
	$sToRender .= "<tr><td><input type=\"image\" src=\"" . KTHtml::getRestoreButton() . "\" border=\"0\"/></a>\n";
	$sToRender .= generateControllerLink("archivedDocuments", "", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></td></tr>");			
	$sToRender .= "</table>";
	
	return $sToRender;
}
?>
