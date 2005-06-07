<?php
/**
 * $Id$
 *
 * Contains HTML information required to build the bulk upload page.
 * Will be used by bulkUploadBL.php
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
 * @author Adam Monsen
 * @package documentmanagement
 */

/**
 * Displays the generic meta data fields form
 */
function getGenericMetaDataForm() {
    global $default;
    /*ok*/ $sQuery = "SELECT DISTINCT -1 AS document_id, DF.id AS document_field_id, DF.name AS field_name, -1 AS id " .
              "FROM document_fields AS DF LEFT OUTER JOIN document_fields_link AS DFL ON DF.id = DFL.document_field_id " .              
              "WHERE DF.is_generic = 1 ";

    $aStoreColumnNames = array("document_id", "document_field_id", "value");
    $aDisplayColumnNames = array("document_id", "field_name", "value");
    $aMetaDataColumnNames = array(2=>"field_name");
    $aColumnsEditable = array(0,0,1);
    $aColumnsVisible = array(0,1,1);
    $aColumnDisplayTypes = array(1,1,4);
    $aColumnDatabaseTypes = array(4,0,1);

    $oPatternTableSqlQuery = & new PatternEditableTableSqlQuery($sQuery, "document_fields_link", $aStoreColumnNames, $aDisplayColumnNames, $aColumnsEditable, $aColumnsVisible, $aColumnDisplayTypes, $aColumnDatabaseTypes);
    $oPatternTableSqlQuery->setTableCaption("Generic Meta Data");
    $oPatternTableSqlQuery->setUniqueName("gmd");
    $oPatternTableSqlQuery->setRequiredColumnNames(array("value"));
    $oPatternTableSqlQuery->setMetaDataFields($aMetaDataColumnNames);
    $oPatternTableSqlQuery->setEmptyTableMessage("No Generic Meta Data");
    $oPatternTableSqlQuery->setRenderJavascriptValidation(false);
    return $oPatternTableSqlQuery;
}

/**
 * Displays the type specific meta data fields form
 */
function getTypeSpecificMetaDataForm($iFolderID, $iDocumentTypeID) {
    global $default;
    /*ok*/ $sQuery = array("SELECT DISTINCT -1 AS document_id, DF.id AS document_field_id, DF.name AS field_name, DTFL.is_mandatory AS is_mandatory, -1 AS id " .
              "FROM document_type_fields_link AS DTFL INNER JOIN document_fields AS DF ON DTFL.field_id = DF.id " .
              "LEFT OUTER JOIN document_fields_link AS DFL ON DFL.document_field_id = DTFL.field_id " .
              "WHERE DF.is_generic = 0 " .
              "AND DTFL.document_type_id = ?", $iDocumentTypeID);

    $aStoreColumnNames = array("document_id", "document_field_id", "value");
    $aDisplayColumnNames = array("document_id", "field_name", "value");
    $aColumnsEditable = array(0,0,1);
    $aColumnsVisible = array(0,1,1);
    $aColumnDisplayTypes = array(1,1,4);
    $aColumnDatabaseTypes = array(4,0,1);
    $aMetaDataColumnNames = array(2=>"field_name");

    $oPatternTableSqlQuery = & new PatternEditableTableSqlQuery($sQuery, "document_fields_link", $aStoreColumnNames, $aDisplayColumnNames, $aColumnsEditable, $aColumnsVisible, $aColumnDisplayTypes, $aColumnDatabaseTypes);
    $oPatternTableSqlQuery->setTableCaption("Type specific meta data");
    $oPatternTableSqlQuery->setEmptyTableMessage("No Type Specific Meta Data");
    $oPatternTableSqlQuery->setUniqueName("tsmd");
    $oPatternTableSqlQuery->setMetaDataFields($aMetaDataColumnNames);   
    $oPatternTableSqlQuery->setRenderJavascriptValidation(false);
    return $oPatternTableSqlQuery;
}

function getMetaDataForm($iFolderID, $iDocumentTypeID) {
    $oGenericPattern = getGenericMetaDataForm();
    $oTypeSpecificPattern = getTypeSpecificMetaDataForm($iFolderID, $iDocumentTypeID);
    $sToRender .= "<table border=\"0\" width=\"100%\">\n";
    $sToRender .= "<tr><td><b>Document Meta Data<b></td></tr>\n";   
    $sToRender .= "<tr><td valign=\"top\">" . $oGenericPattern->render() . "</td><td valign=\"top\">" . $oTypeSpecificPattern->render() . "</td></tr>";
    $sToRender .= "</table>";

    // validation starts
    $sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
    $sToRender .= "function validateForm(theForm) {\n";
    // filename
    $sToRender .= "\tif (!(validRequired(theForm.fFile, 'Bulk Upload Zipfile'))) {\n";
    $sToRender .= "\t\treturn false;\n\t}\n";
    // metadata
    $sToRender .= $oGenericPattern->getValidationJavascript();
    $sToRender .= $oTypeSpecificPattern->getValidationJavascript();
    $sToRender .= "return true;\n}\n";
    $sToRender .= "//-->\n</script>\n\n";   

    return $sToRender;
}

function getDocumentType($iFolderID) {
    global $default;

    $sWhereClause = "FDL.folder_id = $iFolderID";
    $oPatternListBox = & new PatternListBox("$default->document_types_table", "name", "id", "fDocumentTypeID",$sWhereClause);
    $oPatternListBox->setIncludeDefaultValue(false);
    $oPatternListBox->setFromClause("INNER JOIN $default->folder_doctypes_table AS FDL ON ST.id = FDL.document_type_id");
    $oPatternListBox->setEmptyErrorMessage(_("No document types defined") . "!  " . _("Please contact an Administrator"));

    $sHeading = "Please select the document type:";

    $sToRender = "<table><tr><td><b>$sHeading<b></td></tr><tr><td>" . $oPatternListBox->render() . "</td></tr></table>";

    return $sToRender;  
}

function getChooseDocumentTypePage($iFolderID) {
    global $default;

    $oFolder = Folder::get($iFolderID);
    if (Permission::userHasFolderWritePermission($oFolder)) {
        $sToRender .= getDocumentType($iFolderID);
        $sActionButtons .= getCancelButton($iFolderID);
        $sActionButtons .= "<input type=\"image\" src=\"" . KTHtml::getAddButton() . "\" border=\"0\"/>";           

        $sToRender .= "<table border=\"0\" width=\"100%\" >\n";
        $sToRender .= "<tr><td>$sActionButtons</td></tr>";
        $sToRender .= "</table>";
    }

    // display subfolders, if any
    $sToRender .= getSubfolderBrowser($iFolderID, $iDocumentTypeID);

    return $sToRender;
}

// display subfolders, if any
function getSubfolderBrowser($iFolderID, $iDocumentTypeID) {
    $sToRender .= "<table border=\"0\" width=\"100%\">\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "\t<td>" . renderFolderList($iFolderID, generateControllerUrl("bulkUpload", "fDocumentTypeID=$iDocumentTypeID", false)) . "<td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "</tr>";
    $sToRender .= "</table><br>\n";
    return $sToRender;
}

function getPage($iFolderID, $iDocumentTypeID) {
    global $default;
    
    $sToRender .= renderFolderPath($iFolderID, generateControllerUrl("bulkUpload", "fDocumentTypeID=$iDocumentTypeID", false), true);

    $sToRender .= "<input type=\"hidden\" name=\"fDocumentTypeID\" value=\"$iDocumentTypeID\">";
    $sToRender .= "<input type=\"hidden\" name=\"fStore\" value=\"1\">";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td colspan=\"2\"><b>" . _("Bulk Upload Zipfile") . ": <b></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "<tr><td>File: </td><td><input type=\"file\" name=\"fFile\"></td></tr>\n";
    $sToRender .= "<tr><td colspan=\"2\">(" . _("Note: file should be a ZIP file without subdirectories. Details entered below apply to ALL documents to be added.") . ")</td></tr>\n";
    $sToRender .= "</table>\n";

    $sToRender .= "<table><tr><td><b>" . _("Document Type") . "<b></td></tr><tr><td>" . lookupName($default->document_types_table, $iDocumentTypeID) . "</td></tr></table>";
    $sToRender .= getMetaDataForm($iFolderID, $iDocumentTypeID);

    // create "BACK" button (to go back to choose document type id)
    $sToRender .= getBackButton($iFolderID);
    // create "CANCEL" button (to go back to browsing)
    $sToRender .= getCancelButton($iFolderID);
    // create "SUBMIT" button (to actually do the upload)
    $sToRender .= "<input type=\"image\" src=\"" . KTHtml::getSubmitButton() . "\" border=\"0\"/>";

    // display subfolders, if any
    $sToRender .= getSubfolderBrowser($iFolderID, $iDocumentTypeID);

    return $sToRender;
}

function getStatusPage ($iFolderID, $aFileStatus) {
    global $default;

    $sToRender = sprintf(_("Bulk Upload completed for %s documents.  <br/>"), count($aFileStatus));
    if ($aFileStatus) {
        $sToRender .= "<table>\n";
        $sToRender .= "<tr>\n";
        $sToRender .= "<th> " . _("Document") . " </th>\n";
        $sToRender .= "<th> &nbsp; </th>\n";
        $sToRender .= "<th> " . _("Status") . " </th>\n";
        $sToRender .= "</tr>\n";
        foreach ($aFileStatus as $sDocumentName => $sUploadStatus) {
            $sToRender .= "<tr>\n";
            $sToRender .= "<td> $sDocumentName </td>\n";
            $sToRender .= "<td> : </td>\n";
            $sToRender .= "<td> $sUploadStatus </td>\n";
            $sToRender .= "</tr>\n";
        }
        $sToRender .= "</table>\n";
    }

    // create "DONE" button (to go back to browsing)
    $sToRender .= getDoneButton($iFolderID);

    return $sToRender;
}

function getInvalidBulkUploadErrorMsg() {
    return _("You did not select a valid document to upload") . ". <br />\n" .
           _("Bulk upload currently only supports .ZIP files without subdirectories") . ".<br />\n";
}

// image link ... BACK (try bulk upload again)
function getRetryUploadButton($iFolderID, $iDocumentTypeID) {
    global $default;
    $sQueryString = "fFolderID=$iFolderID"."&"."fDocumentTypeID=$iDocumentTypeID";
    return generateControllerLink("bulkUpload", $sQueryString, "<img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"/>");
}

// image link ... BACK (choose document type id again)
function getBackButton($iFolderID) {
    global $default;
    return generateControllerLink("bulkUpload", "fFolderID=$iFolderID", "<img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"/>");
}

// image link ...  DONE, return to browsing
function getDoneButton($iFolderID) {
    global $default;
    return generateControllerLink("browse", "fFolderID=$iFolderID", "<img src=\"" . KTHtml::getDoneButton() . "\" border=\"0\"/>");
}

// image link ...  return to browsing
function getCancelButton($iFolderID) {
    global $default;
    return generateControllerLink("browse", "fFolderID=$iFolderID", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/>");
}

?>
