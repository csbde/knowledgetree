<?php
/**
 * $Id$
 *
 * Generic html building functions..
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
 * @package administration.docfieldmanagement
 */

function renderHeading($sHeading, $sSectionName = "") {
	global $default;
	if (strlen($sSectionName) == 0) {
    	$sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
	}
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"610\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">$sHeading</font></th></tr>\n";
    $sToRender .= "</table>\n";
    return $sToRender;	
}

function statusPage($sTitle, $sHeading, $sText = "", $sCancelAction, $sQueryString = "") {
    global $default;
    $sToRender .= renderHeading($sTitle);
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>$sHeading</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td>$sText</td></tr>\n";
    $sToRender .= "<tr><td align=\"right\">" . generateControllerLink($sCancelAction, $sQueryString, "<img src =\"" . KTHtml::getBackButton() . "\" border=\"0\"/>") . "</td></tr>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

function generateImage($sImageSource) {
    return "<img border=\"0\" src=\"$sImageSource\">";
}


/**
 * Candy striping helper method
 */
function getColour($iColourCount) {
	return ($iColourCount%2 == 0) ? "F5F6EE" : "FFFFFF";
}

/**
 * Localises images by checking in the relevant locale.
 *
 * @param string the image to be localised.
 */
function imgSrc($sImage) {
	global $default;
	
	$sLocalisedImage = "/i18n/" . $default->defaultLanguage . "/graphics/$sImage";

	// check for the image in the selected locale
	if (file_exists($default->fileSystemRoot . $sLocalisedImage)) {
		return $default->rootUrl . $sLocalisedImage;
	} else {
		// else return the default image
		return $default->graphicsUrl . "/$sImage";
	}
}

class KTHtml {
    function getAddButton() {
        return imgSrc('widgets/add.gif');
    }
    function getCancelButton() {
        return imgSrc('widgets/cancel.gif');
    }
    function getLoginButton() {
        return imgSrc('icons/login.jpg');
    }
    function getNextButton() {
        return imgSrc('widgets/next.gif');
    }
    function getBackButton() {
        return imgSrc('widgets/back.gif');
    }
    function getAddCommentButton() {
        return imgSrc('widgets/addcomment.gif');
    }
    function getSubscribeButton() {
        return imgSrc('widgets/subscribe.gif');
    }
    function getUnsubscribeButton() {
        return imgSrc('widgets/unsubscribe.gif');
    }
    function getRejectButton() {
        return imgSrc('widgets/reject.gif');
    }
    function getUpdatePasswordButton() {
        return imgSrc('widgets/updatePassword.gif');
    }
    function getDeleteButton() {
        return imgSrc('widgets/delete.gif');
    }
    function getSelectButton() {
        return imgSrc('widgets/select.gif');
    }
    function getUpdateButton() {
        return imgSrc('widgets/update.gif');
    }
    function getAssignButton() {
        return imgSrc('widgets/assign.gif');
    }
    function getSearchButton() {
        return imgSrc('widgets/search.gif');
    }
    function getEditButton() {
        return imgSrc('widgets/edit.gif');
    }
    function getApproveButton() {
        return imgSrc('widgets/approve.gif');
    }
    function getDoneButton() {
        return imgSrc('widgets/done.gif');
    }
    function getPublishButton() {
        return imgSrc('widgets/publish.gif');
    }
    function getMoveButton() {
        return imgSrc('widgets/move.gif');
    }
    function getRemoveButton() {
        return imgSrc('widgets/remove.gif');
    }
    function getRestoreButton() {
        return imgSrc('widgets/restore.gif');
    }
    function getSubmitButton() {
        return imgSrc('widgets/submit.gif');
    }
    function getExpungeButton() {
        return imgSrc('widgets/expunge.gif');
    }
    function getRestoreHereButton() {
        return imgSrc('widgets/restorehere.gif');
    }
    function getReplyButton() {
        return imgSrc('widgets/reply.gif');
    }
    function getNewButton() {
        return imgSrc('widgets/new.gif');
    }
    function getResetButton() {
        return imgSrc('widgets/reset.gif');
    }
    function getArchiveButton() {
        return imgSrc('widgets/archive.gif');
    }
    function getBrowseButton() {
        return imgSrc('widgets/browse.gif');
    }
    function getCheckInButton() {
        return imgSrc('widgets/checkin.gif');
    }
    function getCheckOutButton() {
        return imgSrc('widgets/checkout.gif');
    }
    function getDownloadButton() {
        return imgSrc('widgets/download.gif');
    }
    function getEmailButton() {
        return imgSrc('widgets/email.gif');
    }
    function getMoveHereButton() {
        return imgSrc('widgets/movehere.gif');
    }
    function getBeginButton() {
        return imgSrc('widgets/begin.gif');
    }
}

?>
