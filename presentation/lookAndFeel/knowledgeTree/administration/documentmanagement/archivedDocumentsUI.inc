<?php

/**
 * $Id$
 *  
 * This page holds all presentation code for displaying document archiving searching. 
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration.dopcumentmanagement
 */

/**
 * Displays the status of restored documents
 */
function renderStatusPage($aSuccessDocuments, $aErrorDocuments) {
	global $default;
	
	$sToRender  = renderHeading("Archived Documents Restoration Status");
	$sToRender .= "<table>";
	if (count($aSuccessDocuments) > 0) {
	    $sToRender .= "<tr><td>The following documents were successfully restored:</td></tr>\n";
	    $sToRender .= "<tr/><tr/>";
	    for ($i=0; $i<count($aSuccessDocuments); $i++) {
			$sToRender .= "<tr><td>" . $aSuccessDocuments[$i]->getDisplayPath() . "</td></tr>\n";
	    }
	}
	if (count($aErrorDocuments) > 0) {
    	$sToRender .= "<tr><td>There were errors restoring the following documents:</td></tr>\n";
	    $sToRender .= "<tr/><tr/>";    	
	    for ($i=0; $i<count($aErrorDocuments); $i++) {    	
    		$sToRender .= "<tr><td>" . $aErrorDocuments[$i]->getDisplayPath() . "</td></tr>\n";
	    }
	}
	$sToRender .= "<tr/><tr/>";	
    $sToRender .= "<tr><td>" . generateControllerLink("archivedDocuments", "", "<img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"></td></tr>");
	$sToRender .= "</table>";
    return $sToRender;	
}

/**
 * Gives the user a last chance to bail out before restoring the documents
 */
function renderRestoreConfirmationPage($aDocuments) {
	global $default;
	
	$sToRender  = renderHeading("Restore Archived Documents");
	$sToRender .= "<table>";
    $sToRender .= "<tr><td>The documents and folders you have chosen to restore from the archive are listed below.</td></tr>\n";
    $sToRender .= "<tr><td>Click 'Restore' to confirm restoration, or 'Cancel' to abort.</td></tr>\n";
    $sToRender .= "<tr/><tr/>";

    // loop through them    
    for ($i = 0; $i < count($aDocuments); $i++) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "\t\t<td bgcolor=\"" . getColour($i) . "\">\n";
        $sToRender .= "<input type=\"hidden\" name=\"fDocumentIDs[]\" value=\"" . $aDocuments[$i]->getID() . "\"/>\n";        
        $sToRender .= $aDocuments[$i]->getDisplayPath() . "\n";
        $sToRender .= "\t\t</td>\n";
        $sToRender .= "\t</tr>\n";
    }
    $sToRender .= "<tr/><tr/>";
    $sToRender .= "<input type=\"hidden\" name=\"fConfirm\" value=\"1\">";
    $sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/restore.gif\" border=\"0\"/></a>\n";
    $sToRender .= generateControllerLink("archivedDocuments", "", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></td></tr>");
	$sToRender .= "</table>";
    return $sToRender;	
}

/**
 * Displays the archived document search form
 */ 
function renderSearchPage() {
	global $default;
    $sToRender  = renderHeading("Archived Documents Search");	
	$sToRender .= "<table width=\"600\">\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "<tr><td>Search for archived documents by entering the document name:</td></tr>\n";
	$sToRender .= "<td>Document name: <input type=\"text\" size=\"60\" name=\"fSearchString\" value=\"$sSearchString\" />\n";
	$sToRender .= "<input type=\"image\" src=\"$default->graphicsUrl/widgets/submit.gif\" border=\"0\"></td>\n";
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
function renderArchivedDocumentsResultsPage($sKeywords, $iStartIndex) {
	global $default;
	$sQuery = 	"SELECT DISTINCT D.id AS document_id, D.name AS document_name " . 
			  	"FROM search_document_user_link AS SDUL " .
				"INNER JOIN documents AS D ON D.id = SDUL.document_id " .
				"INNER JOIN status_lookup AS SL ON D.status_id = SL.id " .
				"WHERE SDUL.user_id = " . $_SESSION["userID"] . " " .
				"AND SL.name='Archived' " .
				"AND (D.name like '%$sKeywords%' " .
				"OR D.filename like '%$sKeywords%')"; 

	$sToRender  = renderHeading("Archived Documents Search Results");
	$sToRender .= "<table>";
	$sql = $default->db;
	// perform query	
	if ($sql->query($sQuery)) {
		if ($sql->num_rows() > 0) {
		    $sToRender .= "<tr><td>The following archived documents meet your search string.";
		    $sToRender .= "<tr><td>Select the documents you'd like to restore, and click 'Restore', or 'Cancel' to abort</td></tr>\n";
			
			while ($sql->next_record()) {
				// and print document paths with checkboxes
				$oDocument = Document::get($sql->f("document_id"));
		        $sToRender .= "\t<tr>\n";
		        $sToRender .= "\t\t<td bgcolor=\"" . getColour($i) . "\">\n";
		        $sToRender .= "<input type=\"checkbox\" name=\"fDocumentIDs[]\" value=\"" . $oDocument->getID() . "\"/>\n";        
		        $sToRender .= $oDocument->getDisplayPath() . "\n";
		        $sToRender .= "\t\t</td>\n";
		        $sToRender .= "\t</tr>\n";			
			}
			$sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/restore.gif\" border=\"0\"/></a>\n";
			$sToRender .= generateControllerLink("archivedDocuments", "", "<img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></td></tr>");			
		} else {
		    $sToRender .= "<tr><td>Your query did not return any archived documents.";
		    $sToRender .= "<tr><td>" . generateControllerLink("archivedDocuments", "", "<img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\">") . "</td></tr>\n";
		}	
	} else {
		// query failed
	    $sToRender .= "<tr><td>There was an error processing your query- please try again later.";
	    $sToRender .= "<tr><td>" . generateControllerLink("archivedDocuments", "", "<img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\">") . "</td></tr>\n";
	}	
	$sToRender .= "</table>";
	
	return $sToRender;
}
?>