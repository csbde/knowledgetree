<?php
/**
 * $Id$
 *
 * Presentation information for Deleting a DocField 
 *
 * @author Mukhtar Dharsey
 * @date 5 February 2003
 * @package presentation.lookAndFeel.knowledgeTree.
 */
 
// gets the delete stuff
function getDeletePage($fDocFieldID) {
    global $default;
    $oDocField = null;
    if (isset($fDocFieldID)) {
        $oDocField = DocumentField::get($fDocFieldID);
        $sHeading = "<b>Are you sure you wish to Remove this Document Field?:</b>\n";
    } else {
        $sHeading = "Please select a Document Field:";
    }

    $sToRender .= renderHeading("Remove Document Field");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td colspan=\"2\">$sHeading</td></tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>Document Field Name: </td><td>" . getDocFieldDisplay($oDocField) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>" . getDocFieldDataType($oDocField) . "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td></td>" . getDeleteButton($oDocField);// . getCancelButton($oDocField);
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";


    return $sToRender;
}

function getStatusPage($sHeading, $sText = "") {
	global $default;
	
    $sToRender .= renderHeading("Remove Document Field");
    $sToRender .= "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><b>$sHeading</b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr/><tr/>\n";
    $sToRender .= "<tr><td>$sText</td></tr>\n";
    $sToRender .= "<tr/><tr/>\n";
	$sToRender .= "<td align=\"right\">" . generateControllerLink("listDocFields", "", "<img src =\"$default->graphicsUrl/widgets/back.gif\" border = \"0\" />") . "</td>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

// get successfully deletion page
function getDeleteSuccessPage() {
	return getStatusPage("Document Field SuccessFully Removed!");
}

// get deletion failed page
function getDeleteFailPage() {
	return getStatusPage("Deletion Failed!", "Please Ensure that The Document Field has not been assigned");
}

function getFieldMappedPage($sDocumentFieldName, $aDocumentTypes) {
	global $default;
	
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td>Document field '$sDocumentFieldName' is mapped to the following document type(s), and can therefore not be deleted.</td></tr>\n";
    $sToRender .= "<tr><td>Click on the Document Type name to edit its fields, or click 'Back' to return to List Document Fields.</td></tr>\n";
    for ($i=0; $i<count($aDocumentTypes); $i++) {
    	$sToRender .= "<tr><td><strong>" . generateControllerLink("editDocTypeFields", "fDocTypeSelected=1&fDocTypeID=" . $aDocumentTypes[$i]->getID(), $aDocumentTypes[$i]->getName()) . "</strong></td></tr>";
    }
    $sToRender .= "</table>\n";

	return getStatusPage("Can't delete document field '$sDocumentFieldName'", $sToRender);
}

// show listbox...or the text name
function getDocFieldDisplay($oDocField) {
    global $default;
    if (!isset($oDocField)) {
        $oPatternListBox = & new PatternListBox($default->owl_fields_table, "name", "id", "fDocFieldID");
        $oPatternListBox->setFromClause("LEFT OUTER JOIN $default->owl_document_fields_table DFL ON ST.id=DFL.document_field_id");
        $oPatternListBox->setWhereClause("ISNULL(DFL.value)");
        $oPatternListBox->setPostBackOnChange(true);
        return $oPatternListBox->render();
    } else {
        return "<input type=\"hidden\" name=\"fDocFieldID\" value=\"" . $oDocField->iId . "\">\n" .
               "<b>" . $oDocField->getName() . "</b>";
    }
}

// show listbox...or the text name
function getDocFieldDataType($oDocField) {
    global $default;
    if (!isset($oDocField)) {
        return "";
    } else {
        return "Data Type: </td><td>" . $oDocField->getDataType();
    }
}

// show delete button
function getDeleteButton($oDocField) {
    global $default;
    if (isset($oDocField)) {
        return "<td align=\"right\"><input type=\"image\" src =\"$default->graphicsUrl/widgets/delete.gif\" value=\"submit\"border=\"0\" /></td>\n".
               "<td align=\"right\">" . generateControllerLink("listDocFields", "", "<img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" />") . "</td>\n";
    }
}

// gets the cancel button when a DocField is selected
function getCancelButton($oDocField) {
    global $default;
    if (!isset($oDocField)) {
        return "<td align=\"right\">" . generateControllerLink("docfield", "", "<img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" />") . "</td>\n";
    } else {
        return "<td align=\"right\">" . generateControllerLink("listDocFields", "", "<img src =\"$default->graphicsUrl/widgets/cancel.gif\" border = \"0\" />") . "</td>\n";
    }
}
?>
