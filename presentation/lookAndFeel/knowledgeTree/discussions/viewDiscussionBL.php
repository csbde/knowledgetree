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

KTUtil::extractGPC('fDocumentID', 'fForDiscussion');

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
			$aDocumentThreads = DiscussionThread::getList(array("document_id = ? ORDER BY id", $fDocumentID));/*ok*/
			if (count($aDocumentThreads) > 0) {
				// call the ui function to display the comments
				$oPatternCustom->setHtml(getPage($fDocumentID, $aDocumentThreads));				
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
