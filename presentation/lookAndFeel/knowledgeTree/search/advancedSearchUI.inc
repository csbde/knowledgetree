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

function getSearchPage($sSearchString = "", $aMetaTagIDs = array(), $sHeading = null, $bSearchArchive = false) {
	global $default;
	if (is_null($sHeading)) {
		$sHeading = _("Advanced Search");
	}

    $sToRender  = renderHeading($sHeading);	

    $sToRender .= "<form name=\"advancedSearch\" method=\"POST\">";

    $sToRender .= "<table width=\"100%\">\n";
    $sToRender .= "<tr><td colspan=\"2\"><h3>" . _("Document Data") . "</h3></td></tr>";
    $aBaseCriteria = Criteria::getBaseCriteria();
    foreach ($aBaseCriteria as $k => $v) {
        $sToRender .= $v->searchDisplay($_REQUEST);
    }
    $sToRender .= "<tr><td colspan=\"2\"><h3>" . _("Document Fields") . "</h3></td></tr>";
    $aGenericCriteria = Criteria::getGenericCriteria();
    foreach ($aGenericCriteria as $k => $v) {
        $sToRender .= $v->searchDisplay($_REQUEST);
    }

	if ($bSearchArchive) {
		$sToRender .= "<input type=\"hidden\" name=\"fToSearch\" value=\"Archived\"/>";
	} else {
		$sToRender .= "<tr>\n";
		$sToRender .= "<td>&nbsp</td>\n";
		$sToRender .= "</tr>\n";
		$sToRender .= "<tr><td>" . _("Search") . ": <select name=\"fToSearch\"><option value=\"Live\">" . _("Live") . "</option><option value=\"Archived\">" . _("Archived") . "</option></select> " . _("Documents") . "</td></tr>";		
	}	

    $sToRender .= "</table>\n";
	$sToRender .= "<input name=\"advancedSearch\" type=\"image\" src=\"" . KTHtml::getSearchButton() . "\" border=\"0\"/>\n";
    $sToRender .= "</form>";

	return $sToRender . getSearchValidationJavaScript();
}

function getSearchValidationJavaScript() {
	/*$sToRender = "\n\n<SCRIPT LANGUAGE=\"javascript\">\n ";
	$sToRender .= "<!--\n";		
	$sToRender .= "function validateForm() {\n";	
	$sToRender .= "\tif (!(validRequired(document.MainForm.fSearchString, 'Search text'))) {\n";
	$sToRender .= "\t\treturn false;\n\t}\n";
	$sToRender .= "\treturn true;\n}\n";
	$sToRender .= "-->\n";
	$sToRender .= "</SCRIPT>\n\n";
	return $sToRender;*/
    return "";
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
