<?php
/**
 * $Id$
 *
 * Contains the business logic required to build the document history view page.
 * Will use viewHistoryUI.php for HTML
 *
 * Expected form varaibles:
 *   o $fDocumentID - Primary key of document to view
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

require_once("../../../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/lib/security/Permission.inc");

require_once("$default->fileSystemRoot/lib/users/User.inc");

require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentUI.inc");

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewHistoryUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");


if (checkSession()) {	
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    if (isset($fDocumentID)) {		
    	$oDocument = & Document::get($fDocumentID);
		if (Permission::userHasDocumentReadPermission($oDocument)) {			
			
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getPage($oDocument->getID(), $oDocument->getFolderID(), $oDocument->getName()));
			$main->setCentralPayload($oPatternCustom);   
			$main->render();
		} else {			
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml("");
			$main->setErrorMessage("You do not have permission to view this document's history");
			$main->setCentralPayload($oPatternCustom);   
			$main->render();
		}
		
	} else {
		$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml("");
			$main->setErrorMessage("No document currently selected");
			$main->setCentralPayload($oPatternCustom);   
			$main->render();
	}
}

?>
