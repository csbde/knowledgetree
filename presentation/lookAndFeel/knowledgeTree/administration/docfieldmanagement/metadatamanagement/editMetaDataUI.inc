<?php
/**
 * $Id$
 *
 * Edit MetaData UI functions.
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
 * @package administration.docfieldmanagement.metadatamanagement
 */
function getEditMetaDataFailPage($iDocFieldID){
    global $default;
    

    $sToRender .= renderHeading(_("Edit Document Field Lookup"));
    $sToRender .= "<table width=300>\n";
    $sToRender .= "<tr><td>" . _("Document has no lookups to edit.") . "</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td><a href=\"$default->rootUrl/control.php?action=editDocFieldLookups&fDocFieldID=$iDocFieldID\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";    
    $sToRender .= "</table>\n";

    return $sToRender;
}
// if its the manual edit page..then display normally
function getSelectFieldPage($iDocFieldID) {
    global $default;
    $oDocField = null;
    if (isset($iDocFieldID)) {
        $oDocField = DocumentField::get($iDocFieldID);
    }

    $sToRender .= renderHeading(_("Edit Document Field Lookup"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>" . _("Please Select a Field") . "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Document Field") . ": </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}


// if its the manual edit page..then display normally
function getSelectMetaDataPage($iDocFieldID, $iMetaDataID) {
    global $default;
    $oDocField = null;
    $oMetaData = null;

    if (isset($iDocFieldID)) {
        $oDocField = DocumentField::get($iDocFieldID);
    }

    if (isset($iMetaDataID)) {
        $oMetaData = MetaData::get($iMetaDataID);
    }

	getMetaDataDisplay($oMetaData,$iDocFieldID);

    $sToRender .= renderHeading(_("Edit Document Field Lookup"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>" . _("Please Select the Document Field Lookup you wish to Edit") . "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Document Field") . ": </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Lookup") . ": </td><td>" . getMetaDataDisplay($oMetaData,$iDocFieldID) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}


// if its the manual edit page..then display normally
function getEditMetaDataPage($iDocFieldID, $iMetaDataID) {
    global $default;
    $oDocField = null;
    $oMetaData = null;

    if (isset($iDocFieldID)) {
        $oDocField = DocumentField::get($iDocFieldID);
    }

    if (isset($iMetaDataID)) {
        $oMetaData = MetaData::get($iMetaDataID);
    }

    $sToRender .= renderHeading(_("Edit Document Field Lookup"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>" . _("Enter a Different Value") . "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Document Field") . ": </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . _("Lookup") . ": </td><td>" . getMetaDataDisplay($oMetaData,$iDocFieldID) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td>" . getUpdateButton($oDocField);
    $sToRender .= getCancelButton($oDocField);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    $sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";
	$sToRender .= "\tif (!(validRequired(theForm.fMetaDataName, 'MetaData Value'))) {\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";	
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
    
    return $sToRender;
}

// if edition is successful print otu success page
function getSuccessPage($iDocFieldID = null) {
    global $default;

    $sToRender .= renderHeading(_("Edit Document Field Lookup"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>" . _("Success") . "!</b></td></tr>\n";
    $sToRender .= "<tr><td>" . _("The Document Field Lookup was successfully edited") . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editDocFieldLookups&fDocFieldID=$iDocFieldID\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// if edition is successful print otu success page
function getFailPage($iDocFieldID = null) {
    global $default;

    $sToRender .= renderHeading(_("Edit Document Field Lookup"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>" . strtoupper(_("Failure")) . "!</b></td></tr>\n";
    $sToRender .= "<tr><td>" . _("The Document Field Lookup was not updated") . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editDocFieldLookups&fDocFieldID=$iDocFieldID\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}


// if edition is successful print otu success page
function getTextPage($iDocFieldID = null) {
    global $default;

    $sToRender .= renderHeading(_("Edit Document Field Lookup"));
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>" . strtoupper(_("Failure")) . "!</b></td></tr>\n";
    $sToRender .= "<tr><td>" . _("Please Enter a Value in the Text Box") . "</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=editDocFieldLookups&fDocFieldID=$iDocFieldID\"><img src =\"" . KTHtml::getBackButton() . "\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
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
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=listDocFields\"><img src =\"" . KTHtml::getCancelButton() . "\" value=\"cancel\" /></a></td>\n";
    }
    else {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=editDocFieldLookups&fDocFieldID=" . $oDocField->getId() . "\"><img src =\"" . KTHtml::getCancelButton() . "\" border = \"0\" /></a></td>\n";
    }
}

// show the list box when no DocField selected
// then shows a text box with the DocField's name
function getDocFieldDisplay($oDocField) {
    global $default;
    if (!isset($oDocField)) {

        $sWhereClause = " ST.has_lookup = 1" ;
        $oPatternListBox = & new PatternListBox($default->document_fields_table, "name", "id", "fDocFieldID");
        //$oPatternListBox->setIncludeDefaultValue(true);
        $oPatternListBox->setWhereClause($sWhereClause);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fDocFieldID\" value=\"" . $oDocField->iId . "\">\n" .
               $oDocField->getName();
    }

}

// show the list box when no DocField selected
// then shows a text box with the DocField's name
function getMetaDataDisplay($oMetaData,$iDocFieldID) {
    global $default;
    if (!isset($oMetaData)) {

        $sWhereClause = " ST.document_field_id = " . $iDocFieldID;
        $oPatternListBox = & new PatternListBox($default->metadata_table, "name", "id", "fMetaDataID");
        //$oPatternListBox->setIncludeDefaultValue(true);
        $oPatternListBox->setWhereClause($sWhereClause);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fMetaDataID\" value=\"" . $oMetaData->iId . "\">\n" .
               "<input type=\"text\" name=\"fMetaDataName\" value=\"" . $oMetaData->getName() . "\">\n" .
               "<input type=\"hidden\" name=\"fOldMetaDataName\" value=\"" . $oMetaData->getName() . "\">\n" ;
    }
}
?>
