<?php

require_once("../../../../../config/dmsDefaults.php");

/**
 * $Id$
 *  
 * This page holds all presentation code for display document archiving settings. 
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration.news
 */

/**
 * Display the heading
 * FIXME: need to refactor this
 */
function renderHeading($sHeading) {
    global $default;
    $sSectionName = $default->siteMap->getSectionName(substr($_SERVER["PHP_SELF"], strlen($default->rootUrl), strlen($_SERVER["PHP_SELF"])));
    $sColor = $default->siteMap->getSectionColour($sSectionName, "th");
    $sToRender  = "<table border=\"0\" width=\"600\">\n";
    $sToRender .= "<tr align=\"left\"><th class=\"sectionHeading\" bgcolor=\"$sColor\"><font color=\"ffffff\">$sHeading</font></th></tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "</table>\n";
    return $sToRender;
}

/**
 * Displays an error message
 */
function renderErrorMessage($sErrorMessage) {
	$sToRender  = "<table>";
	$sToRender .= "<tr><td><p><font color=\"red\">$sErrorMessage</font></p></td></tr>";
	$sToRender .= "</table>";
	return $sToRender;
}

 
function renderDocumentTransactionListBox() {
	global $default;	
    $oPatternListBox = & new PatternListBox($default->owl_transaction_types_table, "name", "id", "fDocumentTransactionID");
    return $oPatternListBox->render();	
}

function renderArchivingTypeListBox() {
	global $default, $fArchivingTypeID;
    $oPatternListBox = & new PatternListBox($default->owl_archiving_type_lookup_table, "name", "id", "fArchivingTypeID");
    $oPatternListBox->setPostBackOnChange(true);
    $oPatternListBox->setSelectedValue($fArchivingTypeID);
    return $oPatternListBox->render();		
}

function renderTimeUnitsListBox($sVariableName) {
	global $default;
    $oPatternListBox = & new PatternListBox($default->owl_time_unit_lookup_table, "name", "id", $sVariableName);
    return $oPatternListBox->render();		
}	
	
function renderChooseArchivingTypeForm() {
	global $default, $fDocumentID;
	
	$sToRender  = "<table border=\"0\">";
	$sToRender .= "<tr><td colspan=\"2\">Choose the archiving type for this document</td></tr>";
	$sToRender .= "<tr><td>Archiving Type:</td><td>" . renderArchivingTypeListBox() . "</td></tr>";
	// hidden archive settings references
	$sToRender .= "<input type=\"hidden\" name=\"fDocumentID\" value=\"$fDocumentID\">\n";			
	$sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a></td></tr>\n";
	$sToRender .= "</table>";
	return $sToRender;
}

/**
 * Displays the edit archive settings form
 *
 * @param ArchiveSettings the archive settings to modify
 */
function renderAddArchiveSettingsForm($iArchivingTypeID) {
	global $default, $fDocumentID;

	$sArchivingType = lookupName($default->owl_archiving_type_lookup_table, $iArchivingTypeID);
		
	$sToRender  = "<table border=\"0\">";
	// TODO: form instructions and calendar control integration
	if ($sArchivingType == "Date") {	
		$sToRender .= "<tr><th colspan=\"2\">Archive By Date</th></tr>";
		$sToRender .= "<tr><td>Expiration Date:</td></tr>";	
		$sToRender .= "<tr><td><input type=\"text\" name=\"fExpirationDate\" size=\"11\"></td><td align=\"right\"><a href=\"javascript: void(0);\" onmouseover=\"if (timeoutId) clearTimeout(timeoutId);window.status='Show Calendar';return true;\" onmouseout=\"if (timeoutDelay) calendarTimeout();window.status='';\" onclick=\"g_Calendar.show(event,'MainForm.fExpirationDate', false, 'yyyy-mm-dd', new Date()); return false;\"><img src=\"$default->graphicsUrl/calendar/calendar.gif\" name=\"imgCalendar\" width=\"34\" height=\"21\" border=\"0\" alt=\"\"></a></td></tr>";
		$sToRender .= "<tr><td colspan=\"3\">Expiration Period:</td></tr>";		
		$sToRender .= "<tr><td><input type=\"text\" size=\"11\" name=\"fUnits\"></td>";
		$sToRender .= "<td>" . renderTimeUnitsListBox("fTimeUnitID") . "</td></tr>";
		
		$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
		$sToRender .= "function validateForm(theForm) {\n";
		$sToRender .= "\tif (isBlank(theForm.fExpirationDate) && isBlank(theForm.fUnits) && isBlank(theForm.fTimeUnitID)) {\n";
		$sToRender .= "\t\talert('You must specify an expiration date OR an expiration period');\n";
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
		$sToRender .= "\t\t\talert('Please enter a value for the \"Expiration Date\" field');\n";		
		$sToRender .= "\t\t\ttheForm.fExpirationDate.focus();\n\t\treturn false;\n";
		$sToRender .= "\t\t}\n";
		$sToRender .= "\t}\n";				
		$sToRender .= "\t\treturn true;\n";		
		$sToRender .= "}\n";
		$sToRender .= "//-->\n</script>\n\n";

			
	} elseif ($sArchivingType == "Utilisation") {
		$sToRender .= "<tr><th colspan=\"2\">Archive By Utilisation</th></tr>";
		$sToRender .= "<tr><td>Document Transaction:</td><td>" . renderDocumentTransactionListBox() . "</td></tr>"; 	
		$sToRender .= "<tr><td>Units</td><td colspan=\"2\"><input type=\"text\" size=\"11\" name=\"fUnits\">" . renderTimeUnitsListBox("fTimeUnitID") . "</td></tr>";
		
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
	}	
	
	// hidden archive settings references
	$sToRender .= "<input type=\"hidden\" name=\"fDocumentID\" value=\"$fDocumentID\">\n";
	$sToRender .= "<input type=\"hidden\" name=\"fArchivingTypeID\" value=\"$iArchivingTypeID\">\n";
	$sToRender .= "<input type=\"hidden\" name=\"fStore\" value=\"1\">\n";			
	$sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/submit.gif\" border=\"0\">\n";
	$sToRender .= $sJavaScript;
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=addDocumentArchiveSettings&fDocumentID=$fDocumentID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a></td></tr>\n";
	$sToRender .= "</table>";



	return $sToRender; 
} 
	
/**
 * Displays the edit document archive settings page
 */
function renderEditArchiveSettingsPage($oArchiveSettings, $sErrorMessage = "") {
	global $default;
	
	//$sToRender .= "<table width=\"600\">" . renderHeading("Edit Document Archive Settings") . "</table>";
	$sToRender .= renderHeading("Edit Document Archive Settings");
	if (strlen($sErrorMessage) > 0) {
		$sToRender .= renderErrorMessage($sErrorMessage);
	}
    $sToRender .= "<table>\n";
    if ($oArchiveSettings) {
		$sToRender .= renderEditArchiveSettingsForm($oArchiveSettings);
    } else {
		$sToRender .= renderAddArchiveSettingsForm($oArchiveSettings);    	
    }
	$sToRender .= "</table>\n";
	return $sToRender;
}

/**
 * Displays the add document archive settings page
 */
function renderAddArchiveSettingsPage($iArchivingTypeID, $sErrorMessage = "") {
	global $default;
	
	$sToRender .= renderHeading("Add Document Archive Settings");
	if (strlen($sErrorMessage) > 0) {
		$sToRender .= renderErrorMessage($sErrorMessage);
	}
    $sToRender .= "<table>\n";
    if ($iArchivingTypeID) {
		$sToRender .= renderAddArchiveSettingsForm($iArchivingTypeID);
    } else {
		$sToRender .= renderChooseArchivingTypeForm();    	
    }
	$sToRender .= "</table>\n";
	return $sToRender;
}

function wrap($html) {
	global $default;
	
	return "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
<html>
<head>
    <basefont size=\"2\" face=\"Arial,Helvetica,sans-serif\" color=\"#000000\">
	<title>Javascript Calendar Example by Lea Smart</title>
	<link rel=\"STYLESHEET\" type=\"text/css\" href=\"$default->uiUrl/calendar.css\">
	<script language=\"JavaScript\" src=\"$default->uiUrl/js/simplecalendar.js\" type=\"text/javascript\"></script>
	<style>
	td{
	  font-family : Arial,Helvetica,Sans-serif;
	  font-size : 12px;
	  color : #000000;
	}
	input{
	  font-family : Arial,Helvetica,Sans-serif;
	  font-size : 12px;
	  color : #000000;
	  width : 90px;
	}
	</style>
</head>
<body bgcolor=\"#FFFFFF\">" . $html . "</form>
</body>
</html>";
}


/**
 * Display the confirmation page for manual archiving of a document
 */ 
function renderArchiveConfirmationPage() {
}
?>