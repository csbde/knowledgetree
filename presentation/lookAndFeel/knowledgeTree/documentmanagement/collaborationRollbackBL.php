<?php	
/**
 * $Id$
 *
 * Business logic used for the rollback of a collaboration step.
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

KTUtil::extractGPC('fDocumentID', 'fComment', 'fForStore');

require_once("$default->fileSystemRoot/lib/security/Permission.inc");

require_once("$default->fileSystemRoot/lib/email/Email.inc");

require_once("$default->fileSystemRoot/lib/users/User.inc");

require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentCollaboration.inc");

require_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/FolderUserRole.inc");
require_once("$default->fileSystemRoot/lib/roles/Role.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/collaborationRollbackUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");

if (checkSession()) {
    if (isset($fDocumentID)) {
		if (DocumentCollaboration::userIsPerformingCurrentCollaborationStep($fDocumentID)) {	
			if (isset($fForStore)) {
				//user has entered a comment
				//create the transaction and rollback the step
				$oDocumentTransaction = & new DocumentTransaction($fDocumentID, $fComment, COLLAB_ROLLBACK);
				if ($oDocumentTransaction->create()) {					
					DocumentCollaboration::rollbackCollaborationStep($fDocumentID, $fComment);					
					redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID");
				} else {
					$oDocument = Document::get($fDocumentID);
					require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
					$oPatternCustom = & new PatternCustom();
					$oPatternCustom->setHtml(getPage($oDocument->getFolderID(), $oDocument->getID(), $oDocument->getName()));
					$main->setCentralPayload($oPatternCustom);
					$main->setHasRequiredFields(true);
					$main->setErrorMessage(_("An error occured while creating the document transaction"));
					$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fForStore=1");
					$main->render();
				}				
			} else {
				$oDocument = Document::get($fDocumentID);
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getPage($oDocument->getFolderID(), $oDocument->getID(), $oDocument->getName()));
				$main->setCentralPayload($oPatternCustom);
				$main->setHasRequiredFields(true);
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentID=$fDocumentID&fForStore=1");
				$main->render();
			}		
		} else { 
			redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID");
		}
	}
}


?>
