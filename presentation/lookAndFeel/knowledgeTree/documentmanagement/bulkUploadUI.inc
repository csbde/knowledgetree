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

require_once(KT_LIB_DIR . '/documentmanagement/DocumentField.inc');

/**
 * Displays the generic meta data fields form
 */
function getGenericMetaDataForm() {
    $oTemplating = KTTemplating::getSingleton();
    $oTemplate = $oTemplating->loadTemplate("ktcore/metadata_fields/editable_metadata_fields");
    $aTemplateData = array(
        'caption' => _('Generic meta data'),
        'empty_message' => _("No Generic Meta Data"),
        'fields' => DocumentField::getList(array('is_generic = ?', array(true))),
    );
    return $oTemplate->render($aTemplateData);
}

/**
 * Displays the type specific meta data fields form
 */
function getTypeSpecificMetaDataForm($iFolderID, $iDocumentTypeID) {
    global $default;
    /*ok*/ $sQuery = array("SELECT DF.id AS id " .
              "FROM document_fields AS DF LEFT JOIN document_type_fields_link AS DTFL ON DTFL.field_id = DF.id " .
              "WHERE DF.is_generic = ? " .
              "AND DTFL.document_type_id = ?", array(false, $iDocumentTypeID));

    $aIDs = DBUtil::getResultArray($sQuery);

    $aFields = array();
    foreach ($aIDs as $iID) {
        $aFields[] =& call_user_func(array('DocumentField', 'get'), $iID);
    }
    $aTemplateData = array(
        'caption' => _('Type specific meta data'),
        'empty_message' => _("No Type Specific Meta Data"),
        'fields' => $aFields,
    );
    $oTemplating = KTTemplating::getSingleton();
    $oTemplate = $oTemplating->loadTemplate("ktcore/metadata_fields/editable_metadata_fields");
    return $oTemplate->render($aTemplateData);
}

function getMetaDataForm($iFolderID, $iDocumentTypeID) {
    $oGenericPattern = getGenericMetaDataForm();
    $oTypeSpecificPattern = getTypeSpecificMetaDataForm($iFolderID, $iDocumentTypeID);
    $sToRender .= "<table border=\"0\" width=\"100%\">\n";
    $sToRender .= "<tr><td><b>" . _("Document Meta Data") . "<b></td></tr>\n";
    $sToRender .= "<tr><td valign=\"top\">" . $oGenericPattern . "</td></tr>";
    $sToRender .= "<tr><td valign=\"top\">" . $oTypeSpecificPattern . "</td></tr>";
    $sToRender .= "</table>";

    // validation starts
    $sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
    $sToRender .= "function validateForm(theForm) {\n";
    // document title
    $sToRender .= "\tif (!(validRequired(theForm.fName, 'Document Title'))) {\n";
    $sToRender .= "\t\treturn false;\n\t}\n";
    // filename
    $sToRender .= "\tif (!(validRequired(theForm.fFile, 'Document Filename'))) {\n";
    $sToRender .= "\t\treturn false;\n\t}\n";
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
    $sToRender .= "<input type=\"hidden\" name=\"postReceived\" value=\"1\">";
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
