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
 
/** 
 *	Views a single thread comment with its text body  
 * 
 *	@param $iDocumentID -> a valid Document ID
 *	@param $oComment -> a valid Comment Object
 *	@param $oUser -> a valid User Object (Logged in user)
  *	@param $iThreadID -> the ThreadID passed through
 */
function getCommentBody($oComment, $iDocumentID, $oUser, $iThreadID) {
	global $default;
	
	$sHeading = _("Document Comment");
		
	$sToRender .= renderHeading($sHeading);
	$sToRender .= displayDocumentPath($iDocumentID);
	$aBody = explode("\n",$oComment->getBody());
	$sBody = "";
	$numChars = 160;  // number of chars in a line
	
	// Used for wrapping text that may be too long on one any line
	for ($i=0; $i <= sizeof($aBody) ;$i++) {
		if (strlen($aBody[$i]) >$numChars){
			$loop = (strlen($aBody[$i]) - (strlen($aBody[$i])) % $numChars)/$numChars +1;			
			
			$j=$numChars;
			for($j=0; $j < $loop; $j++ ) {
				$sBody .= "<br>" . substr($aBody[$i],($j*$numChars), $numChars) . "";							
			}  
		} else { $sBody .= "<br>"  . $aBody[$i];	} 
	}	
			
//		$sToRender .= "<td width=100><b>Views: </b> " . $oThread->getNumberOfViews() . "</td>";    		
//		$sToRender .= "<td width=20></td>";
//		$sToRender .= "<td><b>Replies: </b> " . $oThread->getNumberOfReplies() . "</td>";
			
	$sToRender .= "<br><table width=100% border=0><tr ><td colspan=2><b>Author: </b>" . $oUser->getUserName() . "</td>\n";	
	$sToRender .= "<td align=right>\n";	
	$sToRender .= "<a href=\"" . generateControllerLink("addComment", "fDocumentID=" . $iDocumentID . "&fReplyComment=1&fCommentID=" . $oComment->getID()) . "&fThreadID=" . $iThreadID .  "\"><img src=\"" . KTHtml::getReplyButton() . "\" border=\"0\" /></a>";	
	$sToRender .= "</td><td width=30><a href=\"" . generateControllerLink("viewDiscussion", "fForDiscussion=1&fDocumentID=$iDocumentID") . "\"><img src=\"" . KTHtml::getBackButton() . "\" border=0 ><a></td></tr>\n"; 
	$sToRender .= "<tr><td width=\"1\" style=\"background-color:#F5F6EE;\" ><b>" . _("Subject") . ": </b></td><td width=405 style=\"background-color:#F5F6EE;\">";	
	$sToRender .= $oComment->getSubject();
	$sToRender .= "</td><td colspan=2 nowrap style=\"background-color:#F5F6EE;\"><b>" . _("Date") . ": </b><font color=\"#056DCE\">" . $oComment->getDate() . "</font>&nbsp;&nbsp;&nbsp;</td></tr><tr><td colspan=4 valign=top>";			
	$sToRender .= "<table border=0><tr><td>";
	$sToRender .= $sBody;
	$sToRender .= "</td></tr></table>";
	$sToRender .= "</td>\n";	
		
	$sToRender .= "</tr></table> ";		
	
	return $sToRender;	
}
?>
