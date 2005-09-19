<?php
/**
 * $Id$
 *
 * Presentation data used to add a document.  Used by addDocumentBL.php.
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

require_once(KT_LIB_DIR . '/visualpatterns/PatternTableSqlQuery.inc');
require_once(KT_LIB_DIR . '/visualpatterns/PatternMetaData.inc');
require_once(KT_LIB_DIR . '/visualpatterns/PatternEditableTableSqlQuery.inc');

require_once(KT_LIB_DIR . '/documentmanagement/DocumentField.inc');

function getDocumentType($iFolderID, $iDocumentTypeID, $iDependantDocumentID = null) {
	global $default;
	
	$sWhereClause = "FDL.folder_id = $iFolderID";
	$oPatternListBox = & new PatternListBox("$default->document_types_table", "name", "id", "fDocumentTypeID",$sWhereClause);
	// #3548 removed default document type
	$oPatternListBox->setIncludeDefaultValue(false);
	$oPatternListBox->setFromClause("INNER JOIN $default->folder_doctypes_table AS FDL ON ST.id = FDL.document_type_id");
	$oPatternListBox->setEmptyErrorMessage(_("No document types defined! Please contact an Administrator"));
	
	if ($iDocumentTypeID) {
		$oPatternListBox->setSelectedValue($iDocumentTypeID);
		$sHeading = _("Document Type");
	} else {
		$sHeading = _("Please select the document type:");
	}
	
	$sToRender = "<table><tr><td><b>$sHeading<b></td></tr><tr><td>" . $oPatternListBox->render() . "</td></tr></table>";
	
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";
    $sToRender .= "\tif (!(validRequired(theForm.fDocumentTypeID, 'Document Type'))) {\n";
    $sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";	
	return $sToRender;	
}

/**
 * Displays the file browse widget
 */
function getUploadDocument() {
	global $default;
	$sToRender .= "<table border=\"0\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td colspan=\"2\"><b>" . _("Document to add") . ":<b></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr><td>" . _("File") . "</td><td><input type=\"file\" name=\"fFile\"></td></tr>\n";
	$sToRender .= "<tr><td>" . _("Title") . "</td><td><input type=\"text\" name=\"fName\" size=\"20\"></td></tr>\n";	
	$sToRender .= "</table>\n";
	return $sToRender;
}	

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

function getPage($iFolderID, $iDocumentTypeID, $iDependantDocumentID = null, $sMessage = "") {
    global $default;
    
    $sQueryString = "fFolderID=$iFolderID" . (isset($iDependantDocumentID) ? "&fDependantDocumentID=$iDependantDocumentID" : "");
    
    if (isset($iDependantDocumentID)) {
        $sToRender .= renderHeading(_("Add Dependant Document"));
    } else {
        $sToRender .= renderHeading(_("Add Document"));
    }
    	    	
    $sToRender .= renderFolderPath($iFolderID, generateControllerUrl("addDocument", $sQueryString, false), true);
    	
	if ($sMessage) {
		$sToRender .= "<table border=\"0\" width=\"100%\">\n";
		$sToRender .= "<tr>\n";	
		$sToRender .= "\t<td><font color=\"red\">$sMessage</font></td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "</table>\n";
	}
	
	if ($iDocumentTypeID) {
		$sToRender .= "<table><tr><td><b>" . _("Document Type") . "<b></td></tr><tr><td>" . lookupName($default->document_types_table, $iDocumentTypeID) . "</td></tr></table>";
		$sToRender .= getUploadDocument();
		$sToRender .= getMetaDataForm($iFolderID, $iDocumentTypeID);
		$sActionButtons = "<input type=\"image\" src=\"" . KTHtml::getAddButton() . "\" border=\"0\"/>";
		$sToRender .= "<input type=\"hidden\" name=\"fStore\" value=\"1\">";
		$sToRender .= "<input type=\"hidden\" name=\"postReceived\" value=\"1\">";
		$sActionButtons .= generateControllerLink("addDocument", $sQueryString , "<img src=\"" . KTHtml::getBackButton() . "\" border=\"0\"/>") .
						   generateControllerLink("browse", "fFolderID=$iFolderID", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/>");

		$sToRender .= "<table border=\"0\" width=\"100%\" >\n";
		$sToRender .= "<tr><td>$sActionButtons</td></tr>";
		$sToRender .= "</table>";
		
	} else {
		// only allow document type choosing if the user has write permission
		$oFolder = Folder::get($iFolderID);
		if (Permission::userHasFolderWritePermission($oFolder)) {
			$sToRender .= getDocumentType($iFolderID, $iDocumentTypeID, $iDependantDocumentID);
			$sActionButtons .= generateControllerLink("browse", "fFolderID=$iFolderID", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/>");
			$sActionButtons .= "<input type=\"image\" src=\"" . KTHtml::getNextButton() . "\" border=\"0\"/>";			
            $sToRender .= "<input type=\"hidden\" name=\"postReceived\" value=\"1\">";

			$sToRender .= "<table border=\"0\" width=\"100%\" >\n";
			$sToRender .= "<tr><td>$sActionButtons</td></tr>";
			$sToRender .= "</table>";
		}			
		// display folders for browsing
		$sToRender .= "<table border=\"0\" width=\"100%\">\n";
		$sToRender .= "<tr>\n";
		$sToRender .= "\t<td>" . renderFolderList($iFolderID, generateControllerUrl("addDocument", $sQueryString, false)) . "<td>\n";
		$sToRender .= "</tr>\n";	
		$sToRender .= "<tr>\n";
		$sToRender .= "</tr>";
		$sToRender .= "</table><br>\n";
	}

	return $sToRender;
}

function getStatusPage($iFolderID, $sMessage) {
    $sToRender .= renderHeading(_("Add Document"));
    $sToRender .= renderFolderPath($iFolderID, generateControllerUrl("addDocument", "fFolderID=$iFolderID", false), true);

	$sToRender .= "<table border=\"0\" width=\"100%\">\n";
	$sToRender .= "<tr>\n";	
	$sToRender .= "\t<td><font color=\"red\">$sMessage</font></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	return $sToRender;
}
?>
