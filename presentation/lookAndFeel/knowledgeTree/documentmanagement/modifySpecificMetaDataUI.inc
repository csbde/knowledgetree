<?php
/**
 * $Id$
 *
 * Presentation information used by modifySpecificMetaDataBL.php.
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

require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentField.inc');

function getEditableTypeSpecificMetaData($iDocumentID, $iDocumentTypeID) {
    global $default;
    /*ok*/ $sQuery = array("SELECT DF.id AS id " .
              "FROM document_fields AS DF LEFT JOIN document_type_fields_link AS DTFL ON DTFL.field_id = DF.id " .
              "WHERE DF.is_generic = ? " .
              "AND DTFL.document_type_id = ?", array(false, $iDocumentTypeID));

    $aIDs = DBUtil::getResultArrayKey($sQuery, 'id');

    $aFields = array();
    $aValues = array();
    foreach ($aIDs as $iID) {
        $aFields[] =& call_user_func(array('DocumentField', 'get'), $iID);
        $aValues[$iID] = DBUtil::getOneResultKey(array(
            "SELECT value FROM document_fields_link WHERE document_id = ? AND document_field_id = ?",
            array($iDocumentID, $iID),
        ), 'value');
    }
    $aTemplateData = array(
        'caption' => _('Type specific meta data'),
        'empty_message' => _("No Type Specific Meta Data"),
        'fields' => $aFields,
        'values' => $aValues,
    );
    $oTemplating = KTTemplating::getSingleton();
    $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/editable_metadata_fields");
    return $oTemplate->render($aTemplateData);
}

function getPage($iDocumentID, $iDocumentTypeID, $bFirstEdit) {
	global $default;
	$sToRender .= renderHeading(_("Edit Type Specific Meta Data"));
	$sToRender .= displayDocumentPath($iDocumentID);	
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";		
	$sToRender .= "<td>\n";
	$sToRender .= getEditableTypeSpecificMetaData($iDocumentID, $iDocumentTypeID);
	$sToRender .= "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";		
	$sToRender .= "<td>\n";
	
	$sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>\n";
	$sToRender .= "<input type=\"image\" src=\"" . KTHtml::getUpdateButton() . "\" border=\"0\"/>\n";
	$sToRender .= "</td>\n";
    if (!isset($bFirstEdit)) {    
        $sToRender .= "<td>\n";
        $sToRender .= generateControllerLink("viewDocument", "fDocumentID=$iDocumentID&fShowSection=typeSpecificMetaData", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\"/>");
        $sToRender .= "</td>\n";
    }
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	$sToRender .= "</td>\n";	
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}
?>
