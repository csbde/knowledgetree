<?php

// if its the manual edit page..then display normally
function getSelectFieldPage($iDocFieldID) {
    global $default;
    $oDocField = null;
    if (isset($iDocFieldID)) {
        $oDocField = DocumentField::get($iDocFieldID);
    }

    $sToRender .= "<table width=\"600\">" . renderHeading("Add Document Field Lookup") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>Please Select a Field</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Document Field: </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
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
function getAddMetaDataPage($iDocFieldID) {
    global $default;
    $oDocField = null;
    if (isset($iDocFieldID)) {
        $oDocField = DocumentField::get($iDocFieldID);
    }

    $sToRender .= "<table width=\"600\">" . renderHeading("Add Document Field Lookup") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>Please Enter a new lookup value to add to the Selected Field</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Document Field: </td><td><b>" . getDocFieldDisplay($oDocField) . "</br></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Lookup: </td><td><input type=\"text\" name=\"fMetaDataName\">\n" ;
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td>" . getAddButton($oDocField);
    $sToRender .= getCancelButton($oDocField);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}


// if edition is successful print otu success page
function getSuccessPage() {
    global $default;

    $sToRender .= "<table width=\"600\">" . renderHeading("Add Document Field Lookup") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>Success!</b></td></tr>\n";
    $sToRender .= "<tr><td>The lookup value was successfully added</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addMetaData\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// if edition is successful print otu success page
function getFailPage() {
    global $default;

    $sToRender .= "<table width=\"600\">" . renderHeading("Add Document Field Lookup") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>FAILURE!</b></td></tr>\n";
    $sToRender .= "<tr><td>The lookup value was not added</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addMetaData\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}


// if edition is successful print otu success page
function getTextPage() {
    global $default;

    $sToRender .= "<table width=\"600\">" . renderHeading("Add Document Field Lookup") . "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><b>FAILURE!</b></td></tr>\n";
    $sToRender .= "<tr><td>Please Enter a Value in the Text Box</td></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td><td align = right><a href=\"$default->rootUrl/control.php?action=addMetaData\"><img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";

    return $sToRender;
}

// gets the updatebutton when a DocField is selected
function getAddButton($oDocField) {
    global $default;
    if (!isset($oDocField)) {
        return "Please select a Document Field: ";
    } else {
        return "<td align = right><input type=\"image\" src =\"$default->graphicsUrl/widgets/add.gif\" value=\"submit\" border=\"0\"/></td>\n";
    }
}

// gets the cancel button when a DocField is selected
function getCancelButton($oDocField) {
    global $default;
    if (!isset($oDocField)) {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=metadata\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" value=\"cancel\" /></a></td>\n";
    }
    else {
        return "<td align = right><a href=\"$default->rootUrl/control.php?action=addMetaData\"><img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" /></a></td>\n";
    }
}

// show the list box when no DocField selected
// then shows a text box with the DocField's name
function getDocFieldDisplay($oDocField) {
    global $default;
    if (!isset($oDocField)) {

        $sWhereClause = " ST.has_lookup = 1" ;
        $oPatternListBox = & new PatternListBox($default->owl_fields_table, "name", "id", "fDocFieldID");
        //$oPatternListBox->setIncludeDefaultValue(true);
        $oPatternListBox->setWhereClause($sWhereClause);
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fDocFieldID\" value=\"" . $oDocField->iId . "\">\n" .
               $oDocField->getName();
    }
}
?>