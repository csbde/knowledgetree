<?php
/**
 * $Id$
 *
 * Add a link between a document type and document field UI functions.
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

global $default;

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
	require_once("addDocTypeFieldsLinkUI.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTypeFieldLink.inc");
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
			
	$oPatternCustom = & new PatternCustom();
		
	if (isset($fAdd)){
		if (isset($fDocTypeID)){			
			
			if ($bMandatory){ $bMandatory = true; } 
			else { $bMandatory = false; }
			
			if ($fFieldID > 0){	// Use Existing Non-generic field				
				
				$oDocTypeFieldLink = new DocumentTypeFieldLink($fDocTypeID,$fFieldID,$bMandatory);
								
				if ($oDocTypeFieldLink->create()){
					$oPatternCustom->setHtml(addSuccessPage($fDocTypeID));
				}else {
					$oPatternCustom->setHtml(addFailPage($fDocTypeID));;
				}				
			
			} else if (strlen($fNewField) > 0){  // Create a New Field
						
				if ($bHasLookup){ $bHasLookup = true; }
				else { $bHasLookup = false;	}
				
				$oDocField = new DocumentField($fNewField, $fDataType, false, $bHasLookup);
				if ($oDocField->create()) {
					$fFieldID = $oDocField->getID();
					$oDocTypeFieldLink = new DocumentTypeFieldLink($fDocTypeID,$fFieldID,$bMandatory);
										
					if ($oDocTypeFieldLink->create()){
						$oPatternCustom->setHtml(getCreateNewSuccess($fDocTypeID));
					}else {
						$oPatternCustom->setHtml(getCreateNewFail($fDocTypeID));
					}					
				} else {
					$oPatternCustom->setHtml(getCreateNewFail($fDocTypeID));					
				}					
			} else {
				$oPatternCustom->setHtml(getFail_NoFieldID($fDocTypeID));
			}		
		}else{ //error
			$oPatternCustom->setHtml(getMissingDocTypeIDPage($fDocTypeID));
		}
	
	} else if (isset($fDocTypeID)){	     
	    if (isset($fFromList)){
		    $sNewTableName = $default->document_fields_table;
		    $sNewDisplayColumn = "name";
		    $sNewValueColumn = "id";
		    $sNewSelectName = "fFieldID";
		    $sNewWhereClause = "is_generic != 1";
		    $bNewOrderAsc = true;
		   
		    $oSelectBox = & new PatternListBox($sNewTableName, $sNewDisplayColumn, $sNewValueColumn, $sNewSelectName, $sNewWhereClause , $bNewOrderAsc );	    
			if (count($oSelectBox->getEntries()) > 0) {			
		    
			    $main->setFormAction($_SERVER['PHP_SELF'] . "?fAdd=1&fDocTypeID=$fDocTypeID");
			    
			    $htmlListBox = $oSelectBox->render();
			    $oPatternCustom->addHtml(getFirstPage($htmlListBox, $fDocTypeID));	
			
			} else { //Go to -> no Non-generic fields exist
				$oPatternCustom->addHtml(getListFailPage($fDocTypeID));
			}
		} else if(isset($fNewField)){//A new Field Entry
			$main->setFormAction($_SERVER['PHP_SELF'] . "?fAdd=1&fDocTypeID=$fDocTypeID");
			$oPatternCustom->addHtml(getFirstPage($Nothing, $fDocTypeID, true));
	    } else { // get OptionPage
			$oPatternCustom->addHtml(getOptionPage($fDocTypeID));			
		}
	} else {
		$oPatternCustom->setHtml(getMissingDocTypeIDPage());
	}
			
	$main->setCentralPayload($oPatternCustom);	
    $main->setHasRequiredFields(true);
	$main->render();
}
?>
