<?php
/**
 * $Id$
 *
 * View discussions.
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
 * @author Omar Rahbeeni, Jam Warehouse (Pty) Ltd, South Africa
 * @package discussions
 */
 
require_once("../../../../config/dmsDefaults.php");
require_once("viewDiscussionUI.inc");     
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");    
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");    
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
require_once("$default->fileSystemRoot/lib/discussions/DiscussionThread.inc");  
require_once("$default->fileSystemRoot/lib/discussions/DiscussionComment.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentUI.inc");  
require_once("$default->fileSystemRoot/presentation/Html.inc");

if (checkSession()) {
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();	
	if (isset($fForDiscussion)) {		
		if (isset($fDocumentID)) { 	
			$aDocumentThreads = DiscussionThread::getList("document_id=$fDocumentID ORDER BY id");
			if (count($aDocumentThreads) > 0) {
				// call the ui function to display the comments
				$oPatternCustom->setHtml(getPage($fDocumentID, $aDocumentThreads));				
					
//					$sQuery = 	"SELECT 1 as ForView, dc.subject AS subject, username, date, dc.id AS commentID, dt.document_id AS documentID " .
//					 			"FROM discussion_comments AS dc " .
//					 			"INNER JOIN users AS u ON dc.user_id = u.id " .
//					 			"INNER JOIN discussion_threads AS dt ON dt.id = dc.thread_id  " .
//					 			"WHERE dt.id in ( " . implode(",", $aDocumentThreads) . ") " .
//					 			"ORDER BY dc.thread_id, dc.id, date ASC";
//
//				    $aColumns = array("subject", "username", "date");
//				    $aColumnNames = array("<font color=white>Subject </font>", "<font color=white>User</font>", "<font color=white>Date</font>");
//				    $aColumnTypes = array(3,1,1);
//				    $aQueryStringVars = array("fViewComment", "iCommentID", "iDocumentID");
//				    $aQueryStringCols = array("ForView", "commentID", "documentID");
//				    
//				    for ($i = 0; $i < $iNumMax; $i++) {
//						$aHyperLinkURL[$i] = $_SERVER['PHP_SELF'] ;		
//					}
//				    
//				    $oSearchResults = & new PatternTableSqlQuery ($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%",  $aHyperLinkURL, $aQueryStringCols, $aQueryStringVars);
//				    $sToRender .= renderHeading(_("Document Discussion Thread"));
//					$sToRender .= displayDocumentPath($fDocumentID);
//				    $oPatternCustom->addHtml($sToRender);
//				    $oPatternCustom->addHtml(getPageButtons($fDocumentID));				    
//				    $oPatternCustom->addHtml($oSearchResults->render());    
//					
					// On opening, increment the number of views of current thread & update database
//					if($_SESSION['Discussion' . $fDocumentID][0]->bViews !=true ){
//						$oThread->incrementNumberOfViews();					
//						if($oThread->Update() == false) $oPatternCustom->addHtml("Failed to update. Please Contact Database Administrator in this regard") ;
//						$_SESSION['Discussion' . $fDocumentID][0]->bViews = true;
//					} 																
			} else { // No current thread, option to create one					
				$main->setErrorMessage(_("No discussion thread is currently available"));
				$oPatternCustom->addHtml(getNewThreadOption($fDocumentID));				
			}	
		} else { // Doument id  is negative 
			$main->setErrorMessage(_("You did not specify a document."));
		}
	} else { // If no discussion exists 
		$main->setErrorMessage(_("Invalid function.  No such functionality exists for this page."));
	}
	$main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
