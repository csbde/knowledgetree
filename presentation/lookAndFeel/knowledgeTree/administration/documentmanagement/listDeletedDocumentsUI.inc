<?php
/**
 * $Id$
 *
 * This page holds all presentation code for displaying deleted documents management pages.
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
 * Lists all deleted documents
 */
function renderListDeletedDocumentsPage($aDocuments) {
	global $default;

	$sToRender  = renderHeading(_("Expunge Deleted Documents"));
	$sToRender .= "<table>";
	if (count($aDocuments) > 0) {
	    $sToRender .= "<tr><td>" . _("The following documents have been deleted from the DMS.") . "</td></tr>\n";
	    $sToRender .= "<tr><td>" . _("Select the documents you would like to completely remove and click 'Expunge', or click on the document name link to restore the document.") . "</td></tr>\n";
	    $sToRender .= "<tr/><tr/>";
	    // loop through them    
	    for ($i = 0; $i < count($aDocuments); $i++) {
	        $sToRender .= "\t<tr>\n";
	        $sToRender .= "\t\t<td bgcolor=\"" . getColour($i) . "\">\n";
	        $sToRender .= "<input type=\"checkbox\" name=\"fDocumentIDs[]\" value=\"" . $aDocuments[$i]->getID() . "\"/>\n";
	        // get an existing folder to restore the document to
	        $oFolder = Folder::get($aDocuments[$i]->getFolderID());
	        // if the folder no longer exists, use the root folder to start browsing from
	        $iFolderID = ($oFolder ? $aDocuments[$i]->getFolderID() : User::getUserRootFolderID());
	        $sToRender .= generateControllerLink("restoreDeletedDocument", 
	        									 "fDocumentID=" . $aDocuments[$i]->getID() . "&fFolderID=$iFolderID", 
	        									 ($aDocuments[$i]->getFolderID() == $iFolderID) ? $aDocuments[$i]->getDisplayPath(true) : $aDocuments[$i]->getIcon() . $aDocuments[$i]->getName()) . "\n";
	        $sToRender .= "\t\t</td>\n";
	        $sToRender .= "\t</tr>\n";
	    }
	    $sToRender .= "<tr/><tr/>";
	    $sToRender .= "<tr><td><input type=\"image\" src=\"" . KTHtml::getExpungeButton() . "\" border=\"0\"/></td></tr>\n";	    
	} else {
		$sToRender .= "<tr><td>" . _("There are currently no deleted documents in the system") . "</td></tr>";
	    $sToRender .= "<tr/><tr/>";		
		$sToRender .= "<tr><td><a href=\"javascript:history.go(-1)\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"/></a></td></tr>\n";		
		
	}
	$sToRender .= "</table>";
    return $sToRender;	
}
?>
