<?php
/**
* Presentation information for listing Documemnt Fields
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

function getSubmit() {
	global $default;
	$sToRender = "<input type=\"image\" src =\"$default->graphicsUrl/widgets/select.gif\" value=\"submit\" border=\"0\"/>\n";
	return $sToRender;
}

function getPageButtons($oThread){
	global $default;
	
	$sToRender .= "<table width=100%><tr>\n";
	$sToRender .= "<td align=right width=100%><a href=\"" . generateControllerLink("addComment", "fDocumentID=" . $oThread->getDocumentID() . "&fAddComment=1") . "\"><img src=\"$default->graphicsUrl/widgets/addcomment.gif\" border=\"0\" /></a></td>\n";
	$sToRender .= "<td align=right width=10><img src=\"$default->graphicsUrl/widgets/back.gif\" onmouseover=\"this.style.cursor='hand'\" onclick=\"javascript: history.go(-1);\" /></td>\n";
		
	$sToRender .= "</tr></table>\n";
	
	return $sToRender ; 
}

// If there are no threads to view for a document
function getViewFailPage($sMessage)
{
	global $default;
	
	if (strlen($sMessage)>0){
		$sToRender = $sMessage;
	}
	else $sToRender = "Current document has no discussion.  Press the ADD button to start a discussion.";
	
	return $sToRender;
}

?>