<?php
/**
* Presentation information for renaming a DocType
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

// if its the manual edit page..then display normally
function getEditPage($iDocTypeID) {
    global $default;
    $oDocType = null;
    if (isset($iDocTypeID)) {
        $oDocType = DocumentType::get($iDocTypeID);

    }

    $sToRender .= renderHeading("Edit Document Type");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Document Type Name: </td><td>" . getDocTypeDisplay($oDocType) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td>" . getUpdateButton($oDocType);
    $sToRender .= getCancelButton($oDocType);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}

// if its the manual edit page..then display normally
function getDetailsPage($iDocTypeID,$aGeneric,$aSpecific, $aAllSpecific) {
    global $default;
    $oDocType = null;
    if (isset($iDocTypeID)) {
        $oDocType = DocumentType::get($iDocTypeID);

    }

    $sToRender .= renderHeading("Edit Document Type");
    
    $sToRender .= "<table border=0 width=100%>\n";
    $sToRender .= "<tr><td><br></br></td><td><td></td><td></td><td></td>" . getCancelButton($oDocType) . "</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Document Type Name: </td><td><input type=hidden name=\"fDocTypeID\"  value=\"" . $iDocTypeID . "\">" . getDocTypeDisplay($oDocType) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Current Generic Fields: </td><td>" . getGenericFieldsList($aGeneric) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Current Type Specific Fields: </td>";    
    $sToRender .= "</td></tr>" . getSpecificFieldsList($aSpecific, $iDocTypeID) . "</td>\n";	
    $sToRender .= "<td></td><td colspan=3>";
    $sToRender .= "<hr><a href=\"$default->rootUrl/control.php?action=addDocTypeFieldsLink&fDocTypeID=$iDocTypeID\" ><img border=0 src =\"$default->graphicsUrl/widgets/add.gif\"  /><a>";
    $sToRender .= "&nbsp;Add new Type Specific Field ";
    $sToRender .= "</td>";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr><td><br></br></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    /*if(count($aAllSpecific) > 0) {
        $sToRender .= "<td>Add New Type Specific Field: </td><td>" . addSpecificFieldsList($aAllSpecific) ;
        $sToRender .= "</td></tr><td></td><td>Mandatory: <input type=\"checkbox\" name=\"fIsMandatory\" value=\"1\" CHECKED></td><td>" . getAddSpecificFieldButton() . "</td>\n";
    }*/
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td><br></br></td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    //$sToRender .= "<td></td>" . getUpdateButton($oDocType);    
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}

// if its the manual edit page..then display normally
function getMandatoryPage($iDocFieldID,$iDocTypeFieldID) {
    global $default;
    $oDocField = null;
	
    if (isset($iDocFieldID)) {
        $oDocField = DocumentField::get($iDocFieldID);

    }

    if (isset($iDocTypeFieldID)) {
        $oDocTypeField = DocumentTypeFieldLink::get($iDocTypeFieldID);

    }
    
    $sToRender .= "<input type=\"hidden\" height=0 name=\"fDocTypeID\" value=\"" . $oDocTypeField->iDocumentTypeID . "\">\n" ;
    $sToRender .= "<input type=\"hidden\" height=0 name=\"fFieldID\" value=\"" . $oDocTypeField->iFieldID . "\">\n" .
    //$sToRender .= renderHeading("Edit Mandatory settings");
    $sToRender .= "<br></br>\n";
    
    $sToRender .= "<b>Change Mandatory Setting:</b>\n";
    $sToRender .= "<table>\n";
    $sToRender .= "<br></br>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Document Field Name: </td><td>" . $oDocField->getName() . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    if($oDocTypeField->getIsMandatory() == 1) {
        $sToRender .= "<td>Is Mandatory:  </td><td><input type=\"checkbox\" name=\"fIsMandatory\" value=\"1\" CHECKED></td><td>\n";
    } else {
        $sToRender .= "<td>Is Mandatory:  </td><td><input type=\"checkbox\" name=\"fIsMandatory\" value=\"0\" ></td><td>\n";
    }

    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td>" . getDocTypeFieldUpdateButton() ;
    $sToRender .= "</tr>\n";
    
    $sToRender .= "</table>\n";


    return $sToRender;
}

// if edition is successful print otu success page
function getEditPageSuccess() {
    global $default;

    $sToRender .= renderHeading("Edit Document Type");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Success!</b></td></tr>\n";
    $sToRender .= "<tr><td>The Document Type was successfully modified</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listDocTypes\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// gets the page if editing fails
function getEditPageFail() {
    global $default;

    $sToRender .= renderHeading("Edit Document Type");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Failure!</b></td></tr>\n";
    $sToRender .= "<tr><td>Document Type modification Failed!</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=listDocTypes\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// show the list box when no DocType selected
// then shows a text box with the DocType's name
function getDocTypeDisplay($oDocType) {
    global $default;
    if (!isset($oDocType)) {
        $oPatternListBox = & new PatternListBox($default->owl_document_types_table, "name", "id", "fDocTypeID");
        // only restrict doc type editing for non sys admins
        if (!Permission::userIsSystemAdministrator()) {
            $oPatternListBox->setFromClause("LEFT OUTER JOIN $default->owl_documents_table D on ST.id=D.document_type_id");
            $oPatternListBox->setWhereClause("ISNULL(D.id)");
        }
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return $oDocType->getName();        		
    }

}

function getGenericFieldsList($aGeneric) {
    global $default;

    $sList = "</tr><tr><td></td><td>";
    for($i = 0; $i < count($aGeneric);$i++) {
        $sList .= "<li>" . $aGeneric[$i]["name"]. "</li>\n";
    }
    $sList .= "</td></tr>";
    return $sList;
}

function getSpecificFieldsList1($aSpecific) {
    global $default;
    $sList .= "<select name=fEdit>";
    for($i = 0; $i < count($aSpecific);$i++) {
        $oDocField = DocumentField::get($aSpecific[$i]);
        $sList .= "<tr><td></td><td><li>" . $oDocField->getName() . "</li></td><td>" .  getEditSpecificFieldButton($aSpecific[$i]) ." ". getRemoveSpecificFieldButton($aSpecific[$i]) . "</td></tr>\n";
    }
    return $sList;
}

function getSpecificFieldsList($aSpecific, $iDocTypeID = null) {
    global $default;
    for($i = 0; $i < count($aSpecific);$i++) {
        $oDocField = DocumentField::get($aSpecific[$i]);
        $sList .= "<tr><td></td><td><li>" . $oDocField->getName() . "</li></td><td>" .  getEditSpecificFieldButton($aSpecific[$i],$iDocTypeID) ." ". getRemoveSpecificFieldButton($aSpecific[$i], $iDocTypeID) . "</td></tr>\n";
    }
    return $sList;
}

// show Group details on confirm deletion page
function addSpecificFieldsList($aAllSpecific) {
    global $default;

    $sDocFieldType = "<Select Name=\"fDocFieldTypeID\">";
    for ($i=0;$i < count($aAllSpecific);$i++) {
        $Options .=  "<OPTION value= \"". $aAllSpecific[$i]["id"] . "\">". $aAllSpecific[$i]["name"] ."</OPTION>\n";
    }
    $sDocFieldType .= $Options . "</SELECT>";

    return $sDocFieldType;
}

// gets the updatebutton when a DocType is selected
function getUpdateButton($oDocType) {
    global $default;
    if (!isset($oDocType)) {
        return "Please select a Document Type: ";
    } else {
        return "<td align = right><input ONCLICK=\" setActionAndSubmit('" . $_SERVER["PHP_SELF"] ."?fUpdate=1')\" type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"submit\" /></td>\n";
    }
}

// gets the updatebutton when a DocType is selected
function getDocTypeFieldUpdateButton() {
    global $default;
    return "<td align = right><input ONCLICK=\" setActionAndSubmit('" . $_SERVER["PHP_SELF"] ."?fUpdateMandatory=1')\" type=\"image\" src =\"$default->graphicsUrl/widgets/update.gif\" value=\"submit\" /></td>\n";
}

function getRemoveSpecificFieldButton($iNumber, $iDocTypeID=null) {
    global $default;
    //Return "<input ONCLICK=\" setActionAndSubmit('" . $_SERVER["PHP_SELF"] ."?fRemove=$iNumber')\" align = \"absmiddle\" type=\"image\" src =\"$default->graphicsUrl/widgets/remove.gif\" value=\"remove\" />\n";
	return "<a href=\"" . $_SERVER["PHP_SELF"] . "?fRemove=" . $iNumber . "&fDocTypeID=" . $iDocTypeID . "\"> <img border=0 src =\"$default->graphicsUrl/widgets/remove.gif\" value=\"edit\" /></a>\n";
}

function getEditSpecificFieldButton($iNumber, $iDocTypeID=null) {
    global $default;
    //return "<input ONCLICK=\" setActionAndSubmit('" . $_SERVER["PHP_SELF"] ."?fEdit=$iNumber')\" align = \"absmiddle\" type=\"image\" src =\"$default->graphicsUrl/widgets/edit.gif\" value=\"edit\" />\n";
	return "<a href=\"" . $_SERVER["PHP_SELF"] . "?fEdit=" . $iNumber . "&fDocTypeID=" . $iDocTypeID . "\"> <img border=0 src =\"$default->graphicsUrl/widgets/edit.gif\" value=\"edit\" /></a>\n";
}

function getAddSpecificFieldButton() {
    global $default;
    return "<input ONCLICK=\" setActionAndSubmit('" . $_SERVER["PHP_SELF"] ."?fAdd=1')\" align = \"absmiddle\" type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" value=\"add\" />\n";
}

// gets the cancel button when a DocType is selected
function getCancelButton($oDocType) {
    global $default;
    if (!isset($oDocType)) {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=doctypeManagement\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" border = \"0\"/></a></td>\n";
    }
    else {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=listDocTypes\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    }
}
?>