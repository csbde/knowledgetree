<?php
/**
 * $Id$
 *
 * Add a comment.
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
 * @author Michael Joseph, Jam Warehouse (Pty) Ltd, South Africa
 * @package discussions
 */
 
require_once("../../../../config/dmsDefaults.php");
require_once("viewCommentUI.inc");     
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");       
require_once("$default->fileSystemRoot/lib/discussions/DiscussionThread.inc");  
require_once("$default->fileSystemRoot/lib/discussions/DiscussionComment.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

KTUtil::extractGPC('fCommentID', 'fDocumentID', 'fThreadID');
 
if (checkSession()) {
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();	
 
	// validate input parameters
	if (isset($fCommentID) && isset($fDocumentID)) {
		$oComment = DiscussionComment::get($fCommentID);
	  	$oUser = User::get($oComment->getUserID());		
	  	$main->setFormAction("$default->rootUrl/presentation/lookAndFeel/knowledgeTree/discussions/addCommentBL.php?fDocumentID=$iDocumentID&fCommentID=$iCommentID&fReplyComment=1");		
	  	$oPatternCustom->setHtml(getCommentBody($oComment,$fDocumentID,$oUser,$fThreadID));	  	
	} else {
		$main->setErrorMessage(_("You didn't specify a comment to view"));
	}
	$main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
