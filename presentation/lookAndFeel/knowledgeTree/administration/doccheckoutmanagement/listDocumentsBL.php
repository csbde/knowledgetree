<?php
/**
 * $Id$
 *
 * List checked out documents.
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
 * @package administration.doccheckoutmanagement
 */

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
	
if (checkSession()) {

	$oPatternCustom = & new PatternCustom();
	$aDocuments = Document::getList("is_checked_out = 1");
	$sToRender .= renderHeading("Document Checkout Management");
	$sToRender .= "<table><tr/>";
	if (count($aDocuments) > 0) {
		for ($i=0; $i<count($aDocuments); $i++) {
			if ($aDocuments[$i]) {
				$sToRender .= "<tr bgcolor=\"" . getColour($i) . "\"><td width=\"80%\">" . $aDocuments[$i]->getDisplayPath() . "</td>";
				$sToRender .= "<td align=\"right\">" . generateControllerLink("editDocCheckout", "fDocumentID=" . $aDocuments[$i]->getID(), "Check In") . "</td></tr>"; 
			}
		}
	}  else {
		$sToRender .= "<tr><td colspan=\"3\">There are no checked out document</td></tr>";
	}
	$sToRender .= "</table>";

    $oPatternCustom->setHtml($sToRender);
   	    
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    	    
	$main->setCentralPayload($oPatternCustom);
	$main->setFormAction($_SERVER['PHP_SELF']);	
    $main->render();
}
?>