<?php
/**
 * $Id$
 *
 * Presentation logic used by advancedSearchBL.php
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
 * @package search
 */

require_once("$default->fileSystemRoot/lib/browse/Criteria.inc");

function getMetaData($aMetaTagIDs) {
	global $default;	
	/*ok*/ $sQuery = "SELECT name, id FROM $default->document_fields_table ORDER BY name ASC";
	
	$sql = $default->db;
	$sql->query($sQuery);
	
	$sToRender = "<table border=\"0\">\n";
	$sToRender .= "<input type=\"hidden\" name=\"adv_search_start\" value=\"\" />\n";
	
    $iRecordCount = 0;
    $sCheckAllJavascript = "";
	while ($sql->next_record()) {
		$sCheckAllJavascript .= "document.MainForm.f_adv_$iRecordCount.checked=value;\n"; 
        $sMetaDataCheckBox = "<input type=\"checkbox\" " . wasSelected($sql->f("id"), $aMetaTagIDs) . " name=\"f_adv_$iRecordCount\" value=\"" . $sql->f("id") . "\"></td><td>" . $sql->f("name");
        $iRecordCount++;
        if (($iRecordCount % 3) == 1) {
            $sToRender .= "<tr><td>$sMetaDataCheckBox</td>\n";
        } else if (($iRecordCount % 3) == 0) {
            $sToRender .= "<td>$sMetaDataCheckBox</td></tr>\n";
        } else {
            $sToRender .= "<td>$sMetaDataCheckBox</td>\n";
        }
	}
	
	$sToRender .= "<input type=\"hidden\" name=\"adv_search_end\" value=\"\" />\n";
	$sToRender .= "\n\n<SCRIPT LANGUAGE=\"javascript\">\n ";
	$sToRender .= "<!--\n";		
	$sToRender .= "function setChecked(value) {\n";	
	$sToRender .= $sCheckAllJavascript;
	$sToRender .= "\t}\n";
	$sToRender .= "-->\n";
	$sToRender .= "</SCRIPT>\n\n";	
	$sToRender .= "<tr><td colspan=\"3\"><a href=\"javascript:setChecked(1)\"><img src=\"" . KTHtml::getCheckAllButton() . "\" border=\"0\"/></a></td><td colspan=\"3\"><a href=\"javascript:setChecked(0)\"><img src=\"" . KTHtml::getClearAllButton() . "\" border=\"0\"/></a></td></tr>"; 
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

function renderSearchSection($sSectionName, $sHeadingText, $bDisplayLink) {
    static $iColour;
    if ($bDisplayLink) {
        $sLink = generateControllerLink("advancedSearch", "fShowSection=$sSectionName", $sHeadingText);
    } else {
        $sLink = "<a href=\"#\" onClick=\"switchDiv('$sSectionName', 'search');\">$sHeadingText</a>";
    }
    return "<tr bgcolor=\"" . getColour($iColour) . "\"><td width=\"100%\">$sLink</td></tr>\n";
}

function getSearchPage($sSearchString = "", $aMetaTagIDs = array(), $sHeading = null, $bSearchArchive = false) {
	global $default;
	if (is_null($sHeading)) {
		$sHeading = _("Advanced Search");
	}

    $sToRender  = renderHeading($sHeading);	

    $sToRender .= "<div id=\"headings\">\n";
    $sToRender .= "<table width=\"100%\">";
    $sToRender .= renderSearchSection("searchLess", _("Simpler Search"), false);
    $sToRender .= renderSearchSection("searchMore", _("More Options"), false);
    $sToRender .= "</table>";
    $sToRender .= "</div>";

    $sToRender .= "<div id=\"contentDiv\" style=\"position:relative;visibility:hidden;width:500px;\" >\n";

    $sToRender .= "<div id=\"searchLess\" style=\"position:absolute;visibility:hidden;left:5px;\" >\n";

	$sToRender .= "<table width=\"600\">\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . _("Search text:") . " <input type=\"text\" size=\"50\" name=\"fSearchString\" value=\"$sSearchString\" />\n";
	$sToRender .= "<input name=\"simpleSearch\" type=\"image\" src=\"" . KTHtml::getSearchButton() . "\" border=\"0\"/></td>\n";
	$sToRender .= "</tr>\n";
		
	if ($bSearchArchive) {
		$sToRender .= "<input type=\"hidden\" name=\"fToSearch\" value=\"Archived\"/>";
	} else {
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>&nbsp</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr><td>" . _("Search") . ": <select name=\"fToSearch\"><option value=\"Live\">" . _("Live") . "</option><option value=\"Archived\">" . _("Archived") . "</option></select> " . _("Documents") . "</td></tr>";		
	}	

	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>" . getMetaData($aMetaTagIDs) . "</td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>&nbsp</td>\n";
	$sToRender .= "</tr>\n";	
	$sToRender .= "</table>\n";
    $sToRender .= "</div>";


    $sToRender .= "<div id=\"searchMore\" style=\"position:absolute;visibility:hidden;left:5px;\">";

    $sToRender .= "<form name=\"advancedSearch\" method=\"POST\">";

    $sToRender .= "<table width=\"100%\">\n";
    $sToRender .= "<tr><td colspan=\"2\"><h3>Built-in metadata</h3></td></tr>";
    $aBaseCriteria = Criteria::getBaseCriteria();
    foreach ($aBaseCriteria as $k => $v) {
        $sToRender .= $v->searchDisplay($_REQUEST);
    }
    $sToRender .= "<tr><td colspan=\"2\"><h3>Generic metadata</h3></td></tr>";
    $aGenericCriteria = Criteria::getGenericCriteria();
    foreach ($aGenericCriteria as $k => $v) {
        $sToRender .= $v->searchDisplay($_REQUEST);
    }
    $sToRender .= "</table>\n";
	$sToRender .= "<input name=\"advancedSearch\" type=\"image\" src=\"" . KTHtml::getSearchButton() . "\" border=\"0\"/></td>\n";
    $sToRender .= "</form>";

    $sToRender .= "</div>";
    $sToRender .= "</div>";

	
	return $sToRender . getSearchValidationJavaScript();
}

function getSearchResults($sMetaTagIDs, $sSQLSearchString, $iStartIndex, $sSearchString, $sStatus = "Live") {
	global $default;

    $aMetaTagIDs = split(',', $sMetaTagIDs);
    $sQms = DBUtil::paramArray($aMetaTagIDs);
					
	/*ok*/ $sQuery = "SELECT  F.name as folder_name, F.id folder_id, D.id document_id, D.name AS document_name, COUNT(D.id) AS doc_count " .
			"FROM $default->documents_table AS D INNER JOIN $default->folders_table AS F ON D.folder_id = F.id " .
			"INNER JOIN $default->document_fields_link_table AS DFL ON DFL.document_id = D.id " .
			"INNER JOIN $default->document_fields_table AS DF ON DF.id = DFL.document_field_id " . 
			"INNER JOIN $default->search_permissions_table AS SDUL ON SDUL.document_id = D.ID " .
			"INNER JOIN $default->status_table AS SL on D.status_id=SL.id " .						
			"WHERE DF.ID IN ($sQms) " .
			"AND (" . $sSQLSearchString . ") " .
			"AND SDUL.user_id = ? " .
			"AND SL.name = ? " .
			"GROUP BY D.id " .
			"ORDER BY doc_count DESC ";
    $aParams = $aMetaTagIDs;
    $aParams[] = $_SESSION["userID"];
    $aParams[] = $sStatus;

	$default->log->info("getSearchResults $sQuery");

 	$aColumns = array("folder_name", "document_name", "doc_count");
 	$aColumnTypes = array(3,3,1);
	$aColumnHeaders = array("<font color=\"ffffff\"><img src=$default->graphicsUrl/widgets/dfolder.gif>" . _("Folder") . "</font>","<font color=\"ffffff\">" . _("Document") . "</font>", "<font color=\"ffffff\">" . _("Matches") . "</font>");
	$aLinkURLs = array("$default->rootUrl/control.php?action=browse","$default->rootUrl/control.php?action=viewDocument");
	$aDBQueryStringColumns = array("document_id","folder_id");
	$aQueryStringVariableNames = array("fDocumentID", "fFolderID");
	
	$oPatternBrowse = & new PatternBrowseableSearchResults(array($sQuery, $aParams), 10, $aColumns, $aColumnTypes, $aColumnHeaders, $aLinkURLs, $aDBQueryStringColumns, $aQueryStringVariableNames);
	$oPatternBrowse->setStartIndex($iStartIndex);
	$oPatternBrowse->setSearchText($sSearchString);
    
    $sRefreshMessage = "<table><tr><td align=\"center\">" . _("If your browser displays a 'Warning: Page has Expired' message when you attempt to return to these search results, please click your browser's 'Refresh' button") . "</td></tr></table>";
	return renderHeading(_("Advanced Search")) . $oPatternBrowse->render() . $sRefreshMessage . getSearchVariablesHtml($sSearchString, $sStatus, $sMetaTagIDs);
}

function getSearchValidationJavaScript() {
	/*$sToRender = "\n\n<SCRIPT LANGUAGE=\"javascript\">\n ";
	$sToRender .= "<!--\n";		
	$sToRender .= "function validateForm() {\n";	
	$sToRender .= "\tif (!(validRequired(document.MainForm.fSearchString, 'Search text'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "\treturn true;\n}\n";
	$sToRender .= "-->\n";
	$sToRender .= "</SCRIPT>\n\n";*/
	return $sToRender;
}

function wasSelected($iID, $aMetaTagIDs) {
	if (in_array($iID, $aMetaTagIDs)) {
		return "CHECKED";
	}	
	return "";
}

function getSearchVariablesHtml($sSearchText, $sStatus, $sMetaTagIDs) {
	$aMetaTagIDs = explode(",", $sMetaTagIDs);
	$sToRender = "\n\n<input type=\"hidden\" name=\"fForSearch\" value=\"1\" />\n";
	$sToRender .= "\n\n<input type=\"hidden\" name=\"fSearchString\" value=\"$sSearchText\" />\n";
	$sToRender .= "\n\n<input type=\"hidden\" name=\"fToSearch\" value=\"$sStatus\" />\n";
	$sToRender .= "<input type=\"hidden\" name=\"adv_search_start\" value=\"\" />\n";
	for ($i = 0; $i < count($aMetaTagIDs); $i++) {
		$sToRender .= "<input type=\"hidden\" name=\"fMetaTag_$i\" value=\"" . $aMetaTagIDs[$i] . "\" />\n";
		
	}
	$sToRender .= "<input type=\"hidden\" name=\"adv_search_end\" value=\"\" />\n";
	return $sToRender;
}
?>
