<?php
/**
 * $Id$
 *
 * Edit document field UI functions.
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
 * @package administration.docfieldmanagement
 */

// if its the manual edit page..then display normally
function getEditPage($iDocFieldID) {
    global $default;
    $oDocField = null;
    if (isset($iDocFieldID)) {
        $oDocField = DocumentField::get($iDocFieldID);
        $checkBox = $oDocField->isFieldLinkedToType();
        $aDataTypes =  $oDocField->getAllDataTypes();
        $currentDataType = $oDocField->getDataType();
    }
    
    $sToRender .= renderHeading(_("Edit Document Field"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Document Field Name:") . " </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    
    // if a DocField selected...show checkboxes
    if (isset($iDocFieldID)) {
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>" . _("Data Type") . ": </td><td>" . getDataTypesList($aDataTypes, $currentDataType ) . "</td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        
        // allow changing the generic status if the field is not linked to a document type
        // and if this isn't the category field
        if ( ($checkBox == false) && $oDocField->getName() != "Category") {
            $sToRender .= "<td>" . _("Is Generic") . ": </td><td>" . getIsGenericCheckBox($oDocField) . "</td>\n";
        } else {
            $sToRender .= "<input type=\"hidden\" name=\"fDocFieldIsGeneric\" value=\"" . $oDocField->getIsGeneric() . "\">\n";
        }
        
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<td>" . _("Has MetaData Lookup:") . " </td><td>" . getHasLookupCheckBox($oDocField) . "</td>\n";
        $sToRender .= "</tr>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "</tr>\n";
    }
    
    
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<td></td>" . getUpdateButton($oDocField);
    $sToRender .= getCancelButton($oDocField);
    $sToRender .= "</table>\n";
    
    $sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
    $sToRender .= "function validateForm(theForm) {\n";
    $sToRender .= "\tif (!(validRequired(theForm.fDocFieldName, 'Document Field Name'))) {\n";
    $sToRender .= "\t\treturn false;\n";
    $sToRender .= "\t}\n";
    $sToRender .= "return true;\n}\n";
    $sToRender .= "//-->\n</script>\n\n";
    
    return $sToRender;
}

// if edition is successful print otu success page
function getEditPageSuccess() {
    global $default;
    
    $sToRender .= renderHeading(_("Edit Document Field"));
    $sToRender .= "<b>" . _("Success") . "!</b>\n";
    $sToRender .= "<table>\n";
    $sToRender .= "<br></br>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>" . _("The Document Field was successfully modified") . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listDocFields\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    
    return $sToRender;
}


// gets the page if edition fails
function getEditPageFail() {
    global $default;
    
    $sToRender .= renderHeading(_("Edit Document Field"));
    $sToRender .= "<b>" . strtoupper(_("Failure")) . "!</b>\n";
    $sToRender .= "<table>\n";
    $sToRender .= "<br></br>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td>" . _("The Document Field modification Failed!") . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listDocFields\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    
    return $sToRender;
}

// show the list box when no DocField selected
// then shows a text box with the DocField's name
function getDocFieldDisplay($oDocField) {
    global $default;
    if (!isset($oDocField)) {
        $oPatternListBox = & new PatternListBox($default->document_fields_table, "name", "id", "fDocFieldID");
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        $sToRender .= "<input type=\"hidden\" name=\"fDocFieldID\" value=\"" . $oDocField->iId . "\">\n";
        if ($oDocField->getName() == "Category") {
        	$sToRender .= "<input type=\"hidden\" name=\"fDocFieldName\" value=\"" . $oDocField->getName() . "\">\n";
        	$sToRender .= $oDocField->getName();
        } else {
        	$sToRender .= "<input size=\"40\" type=\"text\" name=\"fDocFieldName\" value=\"" . $oDocField->getName() . "\">";
        }
		return $sToRender;
    }
}

// show Group details on confirm deletion page
function getDataTypesList($aDataTypes, $sCurrent) {
    global $default;
    
    $sDataype = "<Select Name=\"fDocFieldDataType\">";
    
    for ($i=0;$i < count($aDataTypes);$i++) {
        
        if($aDataTypes[$i]["name"] == $sCurrent) {
            $Options .=  "<OPTION value= \"". $aDataTypes[$i]["name"]. "\" SELECTED>". $aDataTypes[$i]["name"] ."</OPTION>\n";
        } else {
            // $oDocField= DocField::get($aAllSpecific[$i]["id"]);
            $Options .=  "<OPTION value= \"". $aDataTypes[$i]["name"] . "\">". $aDataTypes[$i]["name"] ."</OPTION>\n";
        }
    }
    $sDataype .= $Options . "</SELECT>";
    return $sDataype;
}


// returns the checkbox ticked or unticked
function getIsGenericCheckBox($oDocField) {
    if (!isset($oDocField)) {
        return "";
    } else {
        return "<input type=\"checkbox\" name=\"fDocFieldIsGeneric\" " . ($oDocField->getIsGeneric() ? "CHECKED " : " ") . " />";
    }
}

// returns the checkbox ticked or unticked
function getHasLookupCheckBox($oDocField) {
    if (!isset($oDocField)) {
        return "";
    } else {
        return "<input type=\"checkbox\" name=\"fDocFieldHasLookup\" " . ($oDocField->getHasLookup() ? "CHECKED " : " ") . " />";
    }
    
}

// gets the updatebutton when a DocField is selected
function getUpdateButton($oDocField) {
    global $default;
    if (!isset($oDocField)) {
        return _("Please select a Document Field:") . " ";
    } else {
        return "<td align = right><input type=\"image\" src =\"" . KTHtml::getUpdateButton() . "\" value=\"submit\" border=\"0\"/></td>\n";
    }
}

// gets the cancel button when a DocField is selected
function getCancelButton($oDocField) {
    global $default;
    if (!isset($oDocField)) {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=docfield\"><img src =\"" . KTHtml::getCancelButton() . "\" value=\"cancel\" border=\"0\"/></a></td>\n";
    }
    else {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=listDocFields\"><img src =\"" . KTHtml::getCancelButton() . "\" border=\"0\" /></a></td>\n";
    }
}
?>
