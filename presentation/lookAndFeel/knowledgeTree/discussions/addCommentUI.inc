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
	$sMessage = _("Your submission has been successful.");
	$sToRender .= "$sMessage<br><br><a href=\"" . generateControllerLink("viewDiscussion", "fDocumentID=" . $iDocumentID . "&fForDiscussion=1") . "\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\" /></a>";
 	
	return $sToRender;	
}

/** 
 *	Display the ADD COMMENT page   
 *
 *	@param $iDocumentID -> a valid Document ID
 *	@param $sSubject -> a Subject text
 *	@param $sBody -> a Body text 
 */
function getAddComment($iDocumentID, $sSubject, $sBody, $iCommentID, $iNewComment = null, $iThreadID) {
	global $default;
				
	$sHeading = _("Add a Comment");
	$sToRender .= renderHeading($sHeading);
	$sToRender .= displayDocumentPath($iDocumentID);	
	$sToRender .= "<table width=\"100%\" border=\"0\" cellpadding=0 ><tr><td></td>\n";	
	$sToRender .= "<td align=right width=500>";
	$sToRender .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$sToRender .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$sToRender .= "<input onmouseover=\"this.style.cursor='hand'\" type=\"image\" src=\"" . KTHtml::getSubmitButton() . "\" border=0></td>";	
	if ($iNewComment>0) {	// If user is creating a new comment
		$sToRender .= "<td width=\"10\" valign=top><a href=\"" . generateControllerLink("viewDiscussion", "fForDiscussion=1&fDocumentID=$iDocumentID") . "\"><img src=\"" . KTHtml::getCancelButton() . "\" border=0 ></a></td></tr>\n";
		$sToRender .= "<input type=\"hidden\" name=\"iNewComment\" value=\"1\"/>";
	} else {				// If the user is replying to a comment
		$sToRender .= "<input type=\"hidden\" name=\"fInReplyTo\" value=\"$iCommentID\"/>";
		$sToRender .= "<input type=\"hidden\" name=\"fThreadID\" value=\"". $iThreadID . "\"/>";
		$sToRender .= "<td width=\"10\" valign=top><a href=\"" . generateControllerLink("viewComment", "fViewComment=1&iDocumentID=$iDocumentID&iCommentID=$iCommentID") . "\"><img src=\"" . KTHtml::getCancelButton() . "\" border=0 ></a></td></tr>\n";		
	}
	$sToRender .= "<br><tr><td valign=\"top\" width=10><b>Subject</b></td><td colspan=2>\n";
	$sToRender .= "<input type=\"text\" style=\"width:385\" name=\"fSubject\" value=\"$sSubject\"></td></tr>\n";
	$sToRender .= "<tr><td valign=\"top\"><b>Body</b></td><td colspan=2><textarea cols=60 rows=\"18\" name=\"fComment\" wrap=\"physical\">$sBody</textarea></td></tr></table>\n";
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
	
	$sHeading = _("Document Comment");
		
	$sToRender .= renderHeading($sHeading);
	$sToRender .= displayDocumentPath($iDocumentID);
	$sToRender .= "<br>&nbsp;<b>Author: </b>$sUserName";
	$sToRender .= "<table width=\"96%\" border=\"1\"><tr>"; 
	$sToRender .= "<td width=\"1\" style=\"background-color:#F5F6EE;\" valign=\"top\"><b>" . _("Subject") . ": </b></td><td width=405 style=\"background-color:#F5F6EE;\"> <font color=#056DCE>$sCommentSubject</font></td>";
	$sToRender .= "<td><b>" . _("Date") . ": </b><font color=\"#056DCE\">$dDate</font></td></tr><tr><td colspan=3><br>$sCommentBody</a></td>";
	$sToRender .= "</tr></table>";
	return $sToRender;	
}

?>
