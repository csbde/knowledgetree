<?php
/**
 * $Id$
 *
 * Contains HTML information required to build the document history view page.
 * Will be used by viewHistoryBL.php
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */

function getDocumentHistory($iDocumentID) {
    global $default;
    /*ok*/ $sQuery = array("SELECT DTT.name AS transaction_name, U.name AS user_name, DT.version AS version, DT.comment AS comment, DT.datetime AS datetime " .
			"FROM $default->document_transactions_table AS DT INNER JOIN $default->users_table AS U ON DT.user_id = U.id " .
			"INNER JOIN $default->transaction_types_table AS DTT ON DTT.id = DT.transaction_id " . 
			"WHERE DT.document_id = ? ORDER BY DT.datetime DESC", $iDocumentID);
	$sql = $default->db;
    $sToRender  = "<table cellpadding=\"5\" width=\"100%\">\n";
    $sToRender .= "<caption align=\"top\" colspan=\"5\" align=\"left\"><b>" . _("Transaction History") . "</b></caption>\n";
    $sql->query($sQuery);
    if ($sql->num_rows() == 0) {
        $sToRender .= "<tr>\n";	
        $sToRender .= "\t<td colspan=\"5\">" . _("No Transaction History data") . "</td>\n";
        $sToRender .= "</tr>\n";
    } else {
        $sToRender .= "<tr><th align=\"left\">" . _("Type") . "</th>\n";
        $sToRender .= "<th align=\"left\">" . _("Users") . "</th>\n";
        $sToRender .= "<th align=\"left\">" . _("Version") . "</th>\n";
        $sToRender .= "<th align=\"left\">" . _("Comment") . "</th>\n";
        $sToRender .= "<th align=\"left\">" . _("Datetime") . "</th></tr>\n";
        $i = 0;
        while ($sql->next_record()) {
            if ($sql->f("transaction_name") == "Check Out") {
	        	$sVersion = generateControllerLink("downloadDocument", "fDocumentID=$iDocumentID&fVersion=" . $sql->f("version"), "<strong>" . $sql->f("version") . "</strong>");
            } else {
                $sVersion = $sql->f("version");
            }
            $sToRender .= "<tr bgcolor=\"" . getColour($i++) . "\">\n";
            $sToRender .= "<td>" . $sql->f("transaction_name") . "</td><td>" . $sql->f("user_name") . "</td><td>$sVersion</td>\n";
            $sToRender .= "<td>" . displaySpace($sql->f("comment")) . "</td><td>" . $sql->f("datetime") . "</td></tr>\n";
        }
        $sToRender .= "</table>\n";
    }
    return $sToRender;
}

function displaySpace($sHtml) {
	if ($sHtml == "") {
		return "&nbsp;";
	} else {
		return $sHtml;
	}
}

function getPage($iDocumentID, $iFolderID, $sDocumentName) {
    global $default;
    $sToRender  = renderHeading(_("Document History"));
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . displayDocumentPath($iDocumentID) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getDocumentHistory($iDocumentID) . "</td>\n";
	$sToRender .= "</tr>\n";
    $sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=$iDocumentID\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\" /></a></td></tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}

?>
