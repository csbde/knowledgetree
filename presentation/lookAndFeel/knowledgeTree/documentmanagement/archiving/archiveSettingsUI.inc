<?php
/**
 * $Id$
 *
 * This page holds all presentation code for display document archiving settings.
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement.archiving
 */

KTUtil::extractGPC('fArchivingTypeID', 'fDocumentID');

/**
 * Displays an error message
 */
function renderErrorMessage($sErrorMessage) {
	$sToRender  = "<table>";
	$sToRender .= "<tr><td><p><font color=\"red\">$sErrorMessage</font></p></td></tr>";
	$sToRender .= "</table>";
	return $sToRender;
}

function renderDocumentTransactionListBox($iDocumentTransactionID) {
	global $default;	
    $oPatternListBox = & new PatternListBox($default->transaction_types_table, "name", "id", "fDocumentTransactionID");
    $oPatternListBox->setWhereClause("ST.name in ('View','Download')");
    if (isset($iDocumentTransactionID)) {
    	$oPatternListBox->setSelectedValue($iDocumentTransactionID);    	
    }    
    return $oPatternListBox->render();	
}

function renderArchivingTypeListBox() {
	global $default, $fArchivingTypeID;
    $oPatternListBox = & new PatternListBox($default->archiving_type_lookup_table, "name", "id", "fArchivingTypeID");
    $oPatternListBox->setPostBackOnChange(true);
    $oPatternListBox->setSelectedValue($fArchivingTypeID);
    return $oPatternListBox->render();		
}

function renderTimeUnitsListBox($iTimeUnitID) {
	global $default;
    $oPatternListBox = & new PatternListBox($default->time_unit_lookup_table, "name", "id", "fTimeUnitID");
    if (isset($iTimeUnitID)) {
    	$oPatternListBox->setSelectedValue($iTimeUnitID);    	
    }
    return $oPatternListBox->render();		
}	
	
function renderChooseArchivingTypeForm() {
	global $default, $fDocumentID;
	
	$sToRender  = "<table border=\"0\">";
	$sToRender .= "<tr><td colspan=\"2\">" . _("Choose the archiving type for this document") . "</td></tr>";
	$sToRender .= "<tr><td>" . _("Archiving Type") . ":</td><td>" . renderArchivingTypeListBox() . "</td></tr>";
	// hidden archive settings references
	$sToRender .= "<input type=\"hidden\" name=\"fDocumentID\" value=\"$fDocumentID\">\n";			
	$sToRender .= "<tr><td>" . generateControllerLink("viewDocument", "fDocumentID=$fDocumentID&fShowSection=archiveSettings", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\" />") . "</td></tr>\n";
	$sToRender .= "</table>";
	return $sToRender;
}

function getDateArchivingJavascript() {
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";
	$sToRender .= "\tif (isBlank(theForm.fExpirationDate) && isBlank(theForm.fUnits) && isBlank(theForm.fTimeUnitID)) {\n";
	$sToRender .= "\t\talert('You must specify an expiration date OR an expiration period');\n";
	$sToRender .= "\t\treturn false;\n";
	$sToRender .= "\t}\n";
	// if expirationDate is not blank
	// and either units or unit id are not blank
	$sToRender .= "\tif (!isBlank(theForm.fExpirationDate) && (!isBlank(theForm.fUnits) || !isBlank(theForm.fTimeUnitID))) {\n";
	$sToRender .= "\t\talert('You must specify EITHER an expiration date OR an expiration period');\n";
	$sToRender .= "\t\treturn false;\n";	
	$sToRender .= "\t}\n";	

	$sToRender .= "\tif (!isBlank(theForm.fUnits) || !isBlank(theForm.fTimeUnitID)) {\n";		
	$sToRender .= "\t\tif (!validNum(theForm.fUnits, 'Units', true) ) {\n";
	$sToRender .= "\t\t\treturn false;\n";
	$sToRender .= "\t\t}\n";
	$sToRender .= "\t\tif (!validRequired(theForm.fTimeUnitID, 'Date Unit')) {\n";
	$sToRender .= "\t\t\treturn false;\n";
	$sToRender .= "\t\t}\n";		
	$sToRender .= "\t} else {\n";		
	$sToRender .= "\t\tif (isBlank(theForm.fExpirationDate)) {\n";
	$sToRender .= "\t\t\tif (!validDate(theForm.fExpirationDate, 'Expiration Date', true)) {\n";
	$sToRender .= "\t\t\t\treturn false;\n";
	$sToRender .= "\t\t\t}\n";
	$sToRender .= "\t\t}\n";
	$sToRender .= "\t}\n";				
	$sToRender .= "\treturn true;\n";		
	$sToRender .= "}\n";
	$sToRender .= "//-->\n</script>\n\n";
	return $sToRender;
}

function getUtilisationArchivingJavascript() {
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";
	$sToRender .= "\tif (!validRequired(theForm.fDocumentTransactionID,'Document Transaction')) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
    $sToRender .= "\tif (!validNum(theForm.fUnits,'Units', true)) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "\tif (!validRequired(theForm.fTimeUnitID,'Date Unit')) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";	
	return $sToRender;
}
	
function renderTimePeriod($sHeading, $sFooter = "", $oTimePeriod = null) {
	global $default;
	$sToRender .= "<tr valign=\"middle\">";
	if (strlen($sHeading) > 0) {
		$sToRender .= "<td>$sHeading:</td>";
	}
	$sToRender .= "<td><input type=\"text\" size=\"11\" name=\"fUnits\" value=\"" . ($oTimePeriod ? $oTimePeriod->getUnits() : "") . "\">";
	$sToRender .= "&nbsp;&nbsp;" . renderTimeUnitsListBox( ($oTimePeriod ? $oTimePeriod->getTimeUnitID() : null) ) . $sFooter;
	$sToRender .= "&nbsp;&nbsp;<a href=\"javascript:void();\" onclick=\"document.MainForm.fUnits.value='';document.MainForm.fTimeUnitID.value='';\"><img src=\"" . KTHtml::getResetButton() . "\" border=\"0\"></a></td>";
	$sToRender .= "</tr>";	
	return $sToRender;		
}
	
/**
 * Displays the edit archive settings form
 *
 * @param integer the archive settings to modify, null display an add form
 */
function renderArchiveSettingsForm($iDocumentID, $oArchiveSettings, $iArchivingTypeID = -1) {
	global $default, $fDocumentID;

	if ($oArchiveSettings) {
		$sToRender .= "(" . _("in order to change the archiving type for this document, click the 'Remove' button and add new archiving settings") . ")";
		$iArchivingTypeID = $oArchiveSettings->getArchivingTypeID();
	}
	
	$sArchivingType = lookupName($default->archiving_type_lookup_table, $iArchivingTypeID);
					
	$sToRender .= "<table border=\"0\">";
	// TODO: calendar control integration
	switch ($sArchivingType) {
		case "Date" : 
			
			$sToRender .= "<tr><th align=\"left\" colspan=\"2\">Archive By Date</th></tr>";						
			$sToRender .= "<tr><td>" . _("Expiration Date") . ":</td>";
			$sToRender .= "<td valign=bottom><input type=\"text\" name=\"fExpirationDate\" size=\"10\" maxlength=\"10\" " . ($oArchiveSettings ? "value=\"" . ($oArchiveSettings->getExpirationDate() == "0000-00-00" ? "" : $oArchiveSettings->getExpirationDate()) . "\"" : "") . ">";			
			$sToRender .= "&nbsp;&nbsp;<a href=\"javascript:show_calendar('MainForm.fExpirationDate',null,null,'YYYY-MM-DD');\" onmouseover=\"window.status='Date Picker';return true;\" onmouseout=\"window.status='';return true;\"><img src=\"$default->graphicsUrl/calendar/calendar.gif\" name=\"imgCalendar\" width=\"34\" height=\"21\" border=\"0\" alt=\"\"></a>";
			$sToRender .= "&nbsp;&nbsp;<a href=\"javascript:void();\" onclick=\"document.MainForm.fExpirationDate.value=''\"><img src=\"" . KTHtml::getResetButton() . "\" border=\"0\"></a></td></tr>";
					
			if ($oArchiveSettings) {
				$oTimePeriod = TimePeriod::get($oArchiveSettings->getTimePeriodID());
				$sToRender .= renderTimePeriod(_("Expires after"), _("from document creation date"), $oTimePeriod);
			} else {
				$sToRender .= renderTimePeriod(_("Expires after"), _("from document creation date"));
			}
			$sToRender .= getDateArchivingJavascript();
			break;		
		case "Utilisation" :
							
			$sToRender .= "<tr><th align=\"left\" colspan=\"2\">Archive By Utilisation</th></tr>";	
			$sToRender .= "<tr><td colspan=\"2\">Archive document </td></tr>";
			if ($oArchiveSettings) {
				$oTimePeriod = TimePeriod::get($oArchiveSettings->getTimePeriodID());
				$sToRender .= renderTimePeriod("", "", $oTimePeriod);
			} else {
				$sToRender .= renderTimePeriod("");
			}
			if ($oArchiveSettings) {
				$iDocumentTransactionID = $oArchiveSettings->getDocumentTransactionID();
			}
			$sToRender .= "<tr><td colspan=\"2\">" . sprintf(_("after the last %s transactions"), renderDocumentTransactionListBox($iDocumentTransactionID)) . " </td></tr>";
			$sToRender .= getUtilisationArchivingJavascript();
			break;		
	}	
	
	// hidden archive settings references
	$sToRender .= "<input type=\"hidden\" name=\"fDocumentID\" value=\"$fDocumentID\">\n";
	$sToRender .= "<input type=\"hidden\" name=\"fArchivingTypeID\" value=\"$iArchivingTypeID\">\n";
	$sToRender .= "<input type=\"hidden\" name=\"fStore\" value=\"1\">\n";	
	if ($oArchiveSettings) {		
		$sToRender .= "<tr><td colspan=\"2\"><input type=\"image\" src=\"" . KTHtml::getUpdateButton() . "\" border=\"0\">\n";
		$sToRender .= generateControllerLink("modifyDocumentArchiveSettings", "fDocumentID=$fDocumentID&fDelete=1", "<img src=\"" . KTHtml::getRemoveButton() . "\" border=\"0\"/>");
	} else {
		$sToRender .= "<tr><td colspan=\"2\"><input type=\"image\" src=\"" . KTHtml::getSubmitButton() . "\" border=\"0\">\n";
		$sToRender .= generateControllerLink("addDocumentArchiveSettings", "fDocumentID=$fDocumentID", "<img src=\"" . KTHtml::getBackButton() . "\" border=\"0\">");
	}	
	$sToRender .= generateControllerLink("viewDocument", "fDocumentID=$fDocumentID&fShowSection=archiveSettings", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\" />") . "</td></tr>\n";
	$sToRender .= "</table>";

	return $sToRender; 
} 
	
/**
 * Displays the edit document archive settings page
 */
function renderEditArchiveSettingsPage($iDocumentID, $oArchiveSettings, $sErrorMessage = "") {
	global $default;
	
	$sToRender .= renderHeading(_("Edit Document Archive Settings"));
	$sToRender .= displayDocumentPath($iDocumentID);	
	if (strlen($sErrorMessage) > 0) {
		$sToRender .= renderErrorMessage($sErrorMessage);
	}
    $sToRender .= "<table>\n";
	$sToRender .= renderArchiveSettingsForm($iDocumentID, $oArchiveSettings);
	$sToRender .= "</table>\n";
	return $sToRender;
}

/**
 * Displays the add document archive settings page
 */
function renderAddArchiveSettingsPage($iDocumentID, $iArchivingTypeID = false, $sErrorMessage = "") {
	global $default;
	
	$sToRender .= renderHeading(_("Add Document Archive Settings"));
	$sToRender .= displayDocumentPath($iDocumentID);
	if (strlen($sErrorMessage) > 0) {
		$sToRender .= renderErrorMessage($sErrorMessage);
	}
    $sToRender .= "<table>\n";
    if ($iArchivingTypeID) {
		$sToRender .= renderArchiveSettingsForm($iDocumentID, null, $iArchivingTypeID);
    } else {
		$sToRender .= renderChooseArchivingTypeForm();    	
    }
	$sToRender .= "</table>\n";
	return $sToRender;
}


/**
 * Display the confirmation page for manual archiving of a document
 */ 
function renderArchiveConfirmationPage($iDocumentID, $sErrorMessage = "") {
	global $default;
		
	$sToRender = renderHeading(_("Archive Document"));
	if (strlen($sErrorMessage) > 0) {
		$sToRender .= renderErrorMessage($sErrorMessage);
	}	
    $sToRender .= "<table>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("You have chosen to archive the document entitled") . " '" . Document::getDocumentName($iDocumentID) . "'.</td>\n";
	$sToRender .= "<tr>\n<tr>\n";
	$sToRender .= "<td>" . _("Select 'Archive' to confirm, or 'Cancel' to cancel.") . "</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td><table><tr><td><a href=\"" . $_SERVER["PHP_SELF"] . "?fDocumentID=$iDocumentID&fConfirmed=1\"><img src=\"" . KTHtml::getArchiveButton() . "\" border=\"0\"></a></td><td>" . generateControllerLink("viewDocument", "fDocumentID=$iDocumentID&fShowSection=archiveSettings", "<img src=\"" . KTHtml::getCancelButton() . "\" border=\"0\">") . "</td></tr></table></td>";	
	$sToRender .= "</tr>";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}
?>
