<?php
/**
 * $Id$
 *
 * This page holds all presentation code for requesting the restoration of archived documents.
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
 * @package documentmanagement.archiving
 */

/**
 * Displays the page that allows an administrator to restore an archived document
 */ 
function getRestoreArchivedDocumentPage($oDocument) {
	global $default;
	
    $sToRender = renderHeading("Restore Archived Document") . renderDocumentPath($oDocument) . "\n<br>\n";
    
    $sToRender .= "<table>\n";
    $sToRender .= "\t<tr>\n\t\t<td><strong>This document has been archived!</strong></td>\n\t</tr>\n";
    $sToRender .= "\t<tr>\n\t\t<td>Please click 'Restore' to restore this archived document.</td>\n\t</tr>\n";
    $sToRender .= "\t<tr><td><input type=\"hidden\" name=\"fDocumentIDs[]\" value=\"" . $oDocument->getID() . "\"/></td></tr>\n";    
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td><a href=\"" . generateControllerUrl("archivedDocuments", "fConfirm=1&fDocumentIDs[]=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/restore.gif\" border=\"0\" /></a>";
    $sToRender .= "<a href=\"$default->rootUrl/control.php?action=browse&fFolderID=" . $oDocument->getFolderID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a></td>\n";    
    $sToRender .= "\t</tr>";
    $sToRender .= "</table>\n";
    	
	return $sToRender;
}	

/**
 * Displays the page that allows a user to request the restoration of an archived document
 */
function getRequestRestoreDocumentPage($oDocument) {
	global $default;
	
    $sToRender = renderHeading("Request Archived Document Restoration") . renderDocumentPath($oDocument) . "\n<br>\n";
    
    $sToRender .= "<table>\n";
$sToRender .= "\t<tr>\n\t\t<td><strong>This document has been archived!</strong></td>\n\t</tr>\n";    
    $sToRender .= "\t<tr>\n\t\t<td>To request that the document be unarchived, please click the 'Restore' button.</td>\n\t</tr>\n";
    $sToRender .= "\t<tr><td><input type=\"hidden\" name=\"fDocumentIDs[]\" value=\"" . $oDocument->getID() . "\"/></td></tr>\n";    
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td><a href=\"" . generateControllerUrl("requestDocumentRestore", "&fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/restore.gif\" border=\"0\" /></a>";
    $sToRender .= "<a href=\"$default->rootUrl/control.php?action=browse&fFolderID=" . $oDocument->getFolderID() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a></td>\n";    
    $sToRender .= "\t</tr>";
    $sToRender .= "</table>\n";
        
	return $sToRender;
}

/**
 * Displays the page that allows an administrator to restore an archived document
 */ 
function renderRequestSuccessPage($oDocument) {
	global $default;
	
    $sToRender = renderHeading("Restore Archived Document");
    $sToRender .= "<table>\n";
    $sToRender .= "\t<tr><td>Your request has been processed, you will be notified when the document is restored.</td></tr>\n";
    $sToRender .= "\t<tr><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=" . $oDocument->getFolderID() . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a></td></tr>\n";    
    $sToRender .= "</table>\n";
	return $sToRender;
}	

/**
 * Display the error page for archiving request failure
 */ 
function renderRequestFailurePage($oDocument, $oUnitAdminUser) {
	global $default;
    $sToRender = renderHeading("Restore Archived Document");
    $sToRender .= "<table>\n";
    $sToRender .= "\t<tr><td>There was an error requesting the restoration of this document from the archive.  Please try again later.</td></tr>\n";
    $sToRender .= "\t<tr><td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=" . $oDocument->getFolderID() . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a></td></tr>\n";    
    $sToRender .= "</table>\n";
	return $sToRender;
}
?>