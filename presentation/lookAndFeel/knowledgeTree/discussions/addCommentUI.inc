<?php
/**
 * $Id$
 *
 * Add a discussion comment UI functions.
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
 
/** 
 *	Submission of comment SUCCESS page   
 *
 *	@param $iDocumentID -> a valid Document ID 
 */
function getSubmitSuccessPage($iDocumentID){	
	global $default;
	$sMessage = "Your submission has been successful.";	
	$sToRender .= "$sMessage<br><br><a href=\"" . generateControllerLink("viewDiscussion", "fDocumentID=" . $iDocumentID . "&fForDiscussion=1") . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>";
 	
	return $sToRender;	
}

/** 
 *	Renders the path for the document
 *
 *	@param $fDocumentID -> a valid document ID
 */
function renderDocumentPath($iDocumentID) {
    global $default;
	
	$oDocument = & Document::get($iDocumentID);
	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	$sTDBGColour = $default->siteMap->getSectionColour($sSectionName, "td");	
    $sDocumentPath = displayFolderPathLink(Folder::getFolderPathAsArray($oDocument->getFolderID()), Folder::getFolderPathNamesAsArray($oDocument->getFolderID()), "$default->rootUrl/control.php?action=browse") . " > ";
    $sDocumentPath .= "<a href=\"$default->rootUrl/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewBL.php?fDocumentID=" . $oDocument->getID() . "&fForInlineView=1\">" . $oDocument->getName() . "</a>";    
    return "<table border=0 cellpadding=\"5\" width = 100%><tr><td bgcolor=\"$sTDBGColour\">$sDocumentPath</td></tr></table>\n";
}

/** 
 *	Display the ADD COMMENT page   
 *
 *	@param $fDocumentID -> a valid Document ID
 *	@param $sSubject -> a Subject text
 *	@param $sBody -> a Body text 
 */
function getAddComment($fDocumentID, $sSubject, $sBody, $iCommentID, $fNewComment = null) {
	global $default;
				
	$sHeading = "Add a Comment";	
	$sToRender .= renderHeading($sHeading);
	$sToRender .= renderDocumentPath($fDocumentID);	
	$sToRender .= "<table width=\"100%\" border=\"0\" cellpadding=0 ><tr><td></td>\n";	
	$sToRender .= "<td align=right width=500>";
	$sToRender .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$sToRender .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$sToRender .= "<input onmouseover=\"this.style.cursor='hand'\" type=\"image\" src=\"$default->graphicsUrl/widgets/submit.gif\" border=0></td>";	
	if($fNewComment>0){	// If user is creating a new comment
		$sToRender .= "<td width=\"10\" valign=top><a href=\"" . generateControllerLink("viewDiscussion", "fForDiscussion=1&fDocumentID=$fDocumentID") . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=0 ></a></td></tr>\n";
	}else {				// If the user is replying to a comment
		$sToRender .= "<td width=\"10\" valign=top><a href=\"" . generateControllerLink("viewDiscussion", "fViewComment=1&iDocumentID=$fDocumentID&iCommentID=$iCommentID") . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=0 ></a></td></tr>\n";		
	}
	$sToRender .= "<br><tr><td valign=\"top\" width=10><b>Subject</b></td><td colspan=2>\n";
	$sToRender .= "<input type=\"text\" style=\"width:385\"  name=\"NewCommentSubject\" id=\"NewCommentSubject\" value=\"$sSubject\"></td></tr>\n";
	$sToRender .= "<tr><td valign=\"top\"><b>Body</b></td><td colspan=2><textarea cols=60 rows=\"21\" name=\"NewComment\" id=\"NewComment\" wrap=\"physical\" >$sBody</textarea></td></tr></table>\n";
	return $sToRender;	
}

/** 
 *	Display the comment: Body text included 
 *  Prints out 1 comment 
 *  User needs to call this function many times to list all comments  
 *
 *	@param $sCommentBody -> Some body text
 *	@param $iComment Subject -> Some subject text
 *	@param $dDate -> Date comment was created
 *	@param $iDocumentID -> a valid document ID
 *	@param $sUserName -> Name of creator of comment
 */
function getCommentBody($sCommentBody, $sCommentSubject,$dDate,$iDocumentID, $sUserName) {
	global $default;
	
	$sHeading = "Document Comment";
		
	$sToRender .= renderHeading($sHeading);
	$sToRender .= renderDocumentPath($iDocumentID);
	$sToRender .= "<br>&nbsp;<b>Author: </b>$sUserName";
	$sToRender .= "<table width=\"96%\" border=\"1\"><tr>"; 
	$sToRender .= "<td width=\"1\" style=\"background-color:#F5F6EE;\" valign=\"top\"><b>Subject: </b></td><td width=405 style=\"background-color:#F5F6EE;\"> <font color=#056DCE>$sCommentSubject</font></td>";
	$sToRender .= "<td><b>Date: </b><font color=\"#056DCE\">$dDate</font></td></tr><tr><td colspan=3><br>$sCommentBody</a></td>";
	$sToRender .= "</tr></table>";
	return $sToRender;	
}

?>