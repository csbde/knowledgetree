<?php
/**
 * $Id$
 *
 * Move document presentation functions.
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
 * @package documentmanagement
 */
 
function getConfirmationPage($iFolderID, $sRememberDocumentID) {
	global $default;
	$iDocumentIDs = $_SESSION['documents'][$sRememberDocumentID];

    $sToRender = renderHeading(_("Move Document"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n<td>" . _("Are you sure you want to move") . ": </td></tr>";

	for ($i = 0; $i < count($iDocumentIDs); $i++) {

	  $oDocument = & Document::get($iDocumentIDs[$i]);
	
	  $sToRender .= "<tr><td><strong>" . $oDocument->getDisplayPath() . "</strong> <td>&nbsp;" . _("to") . "&nbsp;</td> </td>\n";
	  $sToRender .= "<td><strong>" . Folder::getFolderDisplayPath($iFolderID) . " > " . $oDocument->getFileName() . "</strong></td>\n";
	}
    $sQueryString .= "fRememberDocumentID=$sRememberDocumentID&";

	$sToRender .= "</tr>\n";
	$sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=moveDocument&" . $sQueryString . 
                  "fFolderID=$iFolderID" .
                            "&fForMove=1&fConfirmed=1\">" .
                            "<img src=\"" . KTHtml::getMoveButton() . "\" border=\"0\"/>" . 
                            "</a>&nbsp;" . 
                  "<a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$iFolderID\">" . 
                  "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a></td></tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}


function getPage($iFolderID, $sRememberDocumentID, $sErrorMessage = "") {
	global $default;
	$iDocumentIDs = $_SESSION['documents'][$sRememberDocumentID];

    $sToRender = renderHeading(_("Move Document"));
	$sToRender .= "<table>\n";
	if ($sErrorMessage) {
		$sToRender .= "<tr><td><font color=\"red\">$sErrorMessage</font></td></tr>";
	}
  $sToRender .= "<tr><td>" . _("Move document(s)") . ":<br></td>\n";

  for ($i = 0; $i < count($iDocumentIDs); $i++) {
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>&nbsp;&nbsp;'" . Document::getDocumentDisplayPath($iDocumentIDs[$i]) . "'</td>\n";
  }
    $sQueryString .= "fRememberDocumentID=$sRememberDocumentID&";

  $sToRender .= "<tr><td>" . _("To Folder") . ":</td></tr>";
	$sToRender .= "<tr>\n";
  $sToRender .= "<td>" . renderFolderPath($iFolderID, "/control.php?action=moveDocument&" . $sQueryString) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table width=\"100%\">\n";
	$sToRender .= "<tr>\n";
  $sToRender .= "<td>" . renderFolderList($iFolderID, "/control.php?action=moveDocument&" . $sQueryString) . "</td>\n";
	$sToRender .= "</tr>\n";	
  $sToRender .= "<td><table><tr><td><a href=\"" . $_SERVER["PHP_SELF"] . "?$sQueryString&fFolderID=$iFolderID&fForMove=1\"><img src=\"" . KTHtml::getMoveHereButton() . "\" border=\"0\"></a></td> <td><a href=\"$default->rootUrl/control.php?action=browse&fFolderID=$iFolderID\"><img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"></a></td></tr></table></td>";

	$sToRender .= "</table>\n";
	return $sToRender;
}
function renderErrorPage($sErrorMessage, $iDocumentID = "") {
  global $default;
  if ($iDocumentID) {
    return statusPage(_("Delete Document"), "", $sErrorMessage, "viewDocument", "fDocumentID=$iDocumentID");
  } else {
    $sToRender  = renderHeading(_("Delete Document"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><p class=\"errorText\">$sErrorMessage</p>\n" .
      "<a href=\"javascript:history.go(-2)\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"/></a>\n" .
      "</p></td></tr></table>\n";
    return $sToRender;
  }
}

?>
