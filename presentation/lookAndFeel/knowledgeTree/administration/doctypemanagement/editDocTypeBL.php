<?php
/**
 * $Id$
 *
 * Edit document type.
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
 * @author Mukhtar Dharsey, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.doctypemanagement
 */
 
require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fAdd', 'fDocFieldTypeID', 'fDocTypeID', 'fDocTypeName');
KTUtil::extractGPC('fDocTypeSelected', 'fEdit', 'fFieldID', 'fIsMandatory');
KTUtil::extractGPC('fMandatory', 'fRemove', 'fUpdate', 'fUpdateMandatory');

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("editDocTypeUI.inc");
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTypeFieldLink.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	
	$oPatternCustom = & new PatternCustom();		
	
	// if a new DocType has been added
	// coming from manual edit page	
	if (isset($fUpdate)) {
		
		$oDocType = DocumentType::get($fDocTypeID);
		$oDocType->setName($fDocTypeName);
				
		if ($oDocType->update()) {
				// if successfull print out success message
				$oPatternCustom->setHtml(getEditPageSuccess());
				
		} else {
				// if fail print out fail message
				$oPatternCustom->setHtml(getEditPageFail());
		}
	} else if (isset($fDocTypeSelected)){		
					
		$oPatternCustom->setHtml(getDetailsPage($fDocTypeID));
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fUpdate=1");		
	}else if(isset($fAdd))
		{	
			if($fIsMandatory == 1){
				$iMandatory = 1;
			}else{
				$iMandatory = 0;
			}
	
			$oDocTypeField = new DocumentTypeFieldLink($fDocTypeID, $fDocFieldTypeID, $iMandatory);
			
			$oDocTypeField->create();
			
							
			// post back on DocType select from manual edit page	
			// store type generic field id's in an array
			//$oDocFieldType = new DocumentTypeFieldLink(
			$aGenericFields  = DocumentField::getGenericFields();
		
			// get all specific fields
			$aAllSpecificFields  = DocumentField::getAllSpecificFields();
		
		
			// store type specific field id's and names in an array for specifc doctype
			$aSpecificFields = DocumentTypeFieldLink::getSpecificFields($fDocTypeID);
			
			$oPatternCustom->setHtml(getDetailsPage($fDocTypeID, $aGenericFields, $aSpecificFields,  $aAllSpecificFields));

			$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocTypeSelected=1");					
			//$oPatternCustom->setHtml(getEditPageSuccess());
			
		
	}else if(isset($fRemove)){
			
			$fFieldID = $fRemove;
			
			// does'nt matter wot ismandatory is..not checking for it when deleting
			$iMandatory = 0;
			//create new object
			$oDocTypeField = new DocumentTypeFieldLink($fDocTypeID, $fFieldID, $iMandatory);
			
			//delete it by first getting hte corresponding id
			$oDocTypeField ->setDocTypeFieldID($fDocTypeID, $fFieldID);
			$oDocTypeField->delete();
			
			// post back on DocType select from manual edit page	
			// store type generic field id's in an array
			//$oDocFieldType = new DocumentTypeFieldLink(
			$aGenericFields  = DocumentField::getGenericFields();
		
			// get all specific fields
			$aAllSpecificFields  = DocumentField::getAllSpecificFields();
		
		
			// store type specific field id's and names in an array for specifc doctype
			$aSpecificFields = DocumentTypeFieldLink::getSpecificFields($fDocTypeID);
			
			$oPatternCustom->setHtml(getDetailsPage($fDocTypeID, $aGenericFields, $aSpecificFields,  $aAllSpecificFields));
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocTypeSelected=1");
	
	}else if(isset($fEdit)){ 
		$fFieldID = $fEdit;
		
		 $iMandatory = 0;
		 
		$oDocTypeField = new DocumentTypeFieldLink($fDocTypeID, $fFieldID, $iMandatory);
			
			//delete it by first getting hte corresponding id
		$oDocTypeField ->setDocTypeFieldID($fDocTypeID, $fFieldID);
		
		$iDocTypeFieldID = $oDocTypeField->getID();
				
		$oPatternCustom->setHtml(getMandatoryPage($fFieldID,$iDocTypeFieldID));
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fUpdateMandatory=1");
		
		
	
	
	}else if(isset($fUpdateMandatory)){ 
			
		
		
					 
		$oDocTypeField = new DocumentTypeFieldLink($fDocTypeID, $fFieldID, $fMandatory);
			
			//delete it by first getting hte corresponding id
		$oDocTypeField->setDocTypeFieldID($fDocTypeID, $fFieldID);
		
		if(isset($fIsMandatory)){
			$oDocTypeField->setIsMandatory(true);
		}else{
			$oDocTypeField->setIsMandatory(false);
		}
		
		$oDocTypeField->update();
		
		
						
			// post back on DocType select from manual edit page	
			// store type generic field id's in an array
			//$oDocFieldType = new DocumentTypeFieldLink(
			$aGenericFields  = DocumentField::getGenericFields();
		
			// get all specific fields
			$aAllSpecificFields  = DocumentField::getAllSpecificFields();
		
		
			// store type specific field id's and names in an array for specifc doctype
			$aSpecificFields = DocumentTypeFieldLink::getSpecificFields($fDocTypeID);
			
			$oPatternCustom->setHtml(getDetailsPage($fDocTypeID, $aGenericFields, $aSpecificFields,  $aAllSpecificFields));
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocTypeSelected=1");
		
		
	
	
	}else {
		// if nothing happens...just reload edit page
		$oPatternCustom->setHtml(getEditPage(null));
		$main->setFormAction($_SERVER["PHP_SELF"] ."?fDocTypeSelected=1");
			
	
	}
	

		
		
	
	
	//render the page
	$main->setCentralPayload($oPatternCustom);
	$main->render();	
}
?>
