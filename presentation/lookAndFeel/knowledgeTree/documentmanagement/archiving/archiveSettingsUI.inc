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

/**
 * Displays the edit archive settings form
 *
 * @param ArchiveSettings the archive settings to modify
 */
function renderAddArchiveSettingsForm($oArchiveSettings) {
	global $default, $fDocumentID;
	
	$sToRender  = "<table border=\"0\">";
	// TODO: form instructions	
	$sToRender .= "<tr><th colspan=\"2\">Archive By Date</th></tr>";
	$sToRender .= "<tr><td>Expiration Date:</td></tr>";	
	//$sToRender .= "<tr><td><input type=\"text\" name=\"fExpirationDate\" size=\"11\"></td><td align=\"right\"><a href=\"javascript: void(0);\" onmouseover=\"if (timeoutId) clearTimeout(timeoutId);window.status='Show Calendar';return true;\" onmouseout=\"if (timeoutDelay) calendarTimeout();window.status='';\" onclick=\"g_Calendar.show(event,'MainForm.fExpirationDate', false, 'yyyy-mm-dd', new Date()); return false;\"><img src=\"$default->graphicsUrl/calendar/calendar.gif\" name=\"imgCalendar\" width=\"34\" height=\"21\" border=\"0\" alt=\"\"></a></td></tr>";
	$sToRender .= "<tr><td><input type=\"text\" name=\"fExpirationDate\" value=\"" . ($oArchiveSettings ? $oArchiveSettings->getExpirationDate() : "2003-06-06") . "\" size=\"11\"></td></tr>";
	$sToRender .= "<tr><td colspan=\"3\">Expiration Period:</td></tr>";
	
	$sToRender .= "<tr><td><input type=\"text\" size=\"11\" name=\"fExpirationUnits\"></td>";
	$sToRender .= "<td><select name=\"fExpirationDatePart\"><option value=\"day\">Days</option><option value=\"month\">Months</option><option value=\"year\">Years</option></select>";
	$sToRender .= "</td></tr>";
	
	$sToRender .= "<tr><th colspan=\"2\">Archive By Utilisation</th></tr>";
	$sToRender .= "<tr><td colspan=\"2\">Utilisation Threshold:</td></tr>";
	$sToRender .= "<tr><td>Document Transaction:</td><td>" . renderDocumentTransactionListBox() . "</td></tr>"; 	
	$sToRender .= "<tr><td><input type=\"text\" size=\"11\" name=\"fUtilisationUnits\"></td>";
	$sToRender .= "<td><select name=\"fUtilisationDatePart\"><option value=\"day\">Days</option><option value=\"month\">Months</option><option value=\"year\">Years</option></select></td></tr>";
	
	// hidden archive settings references
	$sToRender .= "<input type=\"hidden\" name=\"fStore\" value=\"1\">\n";
	$sToRender .= "<input type=\"hidden\" name=\"fDocumentID\" value=\"$fDocumentID\">\n";
			
	$sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/submit.gif\" border=\"0\">\n";
	$sToRender .= "<a href=\"$default->rootUrl/control.php?action=viewDocument&fDocumentID=$fDocumentID\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\" /></a></td></tr>\n";
	$sToRender .= "</table>";

	/*
	$sToRender .= "\n\n<script language=\"javascript\">\n<!--\n";
	$sToRender .= "function validateForm(theForm) {\n";	
	$sToRender .= "\tif (!(validRequired(theForm.fSynopsis,'Synopsis'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
    $sToRender .= "\tif (!(validRequired(theForm.fBody,'Body'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "\tif (!(validNum(theForm.fRank,'Rank')) || !(validRequired(theForm.fRank,'Rank'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";	
	$sToRender .= "return true;\n}\n";
	$sToRender .= "//-->\n</script>\n\n";
	*/	
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