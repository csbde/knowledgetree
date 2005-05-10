<?php
/**
 * $Id$
 *
 * Business logic used to perform advanced search.  Advanced search allows
 * users to search by meta data types
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

require_once("../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fForSearch', 'fSearchString', 'fShowSection', 'fStartIndex', 'fToSearch');

function searchCriteria ($var) {
    return preg_match('/^bmd(_?\d+)/', $var);
}

function criteriaNumber ($var) {
    $res = preg_replace('/^bmd(_?\d+)(\D.*)?/', '\\1', $var);
    if ($res !== false) {
        $res = strtr($res, '_', '-');
    }
    return $res;
}

function getAdvancedSearchResults($aOrigReq, $iStartIndex) {
    global $default;

    $sRefreshMessage = "<table><tr><td align=\"center\">" . _("If your browser displays a 'Warning: Page has Expired' message when you attempt to return to these search results, please click your browser's 'Refresh' button") . "</td></tr></table>";

    $aReq = array();
    foreach ($aOrigReq as $k => $v) {
        if (searchCriteria($k) === 1) {
            $v = trim($v);
            if ($v === "") {
                continue;
            }
            if ($v === "-1") {
                continue;
            }
            $aReq[$k] = $v;
        }
    }

    $aIDs = array_unique(array_map("criteriaNumber", array_keys($aReq)));
    $aSQL = array();
    foreach ($aIDs as $iID) {
        $oCriterion =& Criteria::getCriterionByNumber($iID);
        $res = $oCriterion->searchSQL($aReq);
        if (!is_null($res)) {
            $aSQL[] = $res;
        }
    }
    $aCritParams = array();
    $aCritQueries = array();
    foreach ($aSQL as $sSQL) {
        if (is_array($sSQL)) {
            $aCritQueries[] = $sSQL[0];
            $aCritParams = array_merge($aCritParams , $sSQL[1]);
        } else {
            $aCritQueries[] = $sSQL;
        }
    }

    if (count($aCritQueries) == 0) {
        return "No search criteria were specified";
    }

    $sSQLSearchString = join(" AND ", $aCritQueries);

    $sQuery = DBUtil::compactQuery("
SELECT
    F.name AS folder_name, F.id AS folder_id, D.id AS document_id,
    D.name AS document_name, COUNT(D.id) AS doc_count
FROM
    $default->documents_table AS D
    INNER JOIN $default->folders_table AS F ON D.folder_id = F.id
    LEFT JOIN $default->document_fields_link_table AS DFL ON DFL.document_id = D.id
    LEFT JOIN $default->document_fields_table AS DF ON DF.id = DFL.document_field_id
    INNER JOIN $default->search_permissions_table AS SDUL ON SDUL.document_id = D.id
    INNER JOIN $default->status_table AS SL on D.status_id=SL.id
WHERE
    SDUL.user_id = ?
    AND SL.name = ?
    AND ($sSQLSearchString)
GROUP BY D.id
ORDER BY doc_count DESC");

    $aParams = array();
    $aParams[] = $_SESSION["userID"];
    $aParams[] = "Live";
    $aParams = array_merge($aParams, $aCritParams);

    //var_dump(DBUtil::getResultArray(array($sQuery, $aParams)));
    //exit(0);

    $aColumns = array("folder_name", "document_name", "doc_count");
    $aColumnTypes = array(3,3,1);
    $aColumnHeaders = array("<font color=\"ffffff\"><img src=$default->graphicsUrl/widgets/dfolder.gif>" . _("Folder") . "</font>","<font color=\"ffffff\">" . _("Document") . "</font>", "<font color=\"ffffff\">" . _("Matches") . "</font>");
    $aLinkURLs = array("$default->rootUrl/control.php?action=browse","$default->rootUrl/control.php?action=viewDocument");
    $aDBQueryStringColumns = array("document_id","folder_id");
    $aQueryStringVariableNames = array("fDocumentID", "fFolderID");

    $oPatternBrowse = & new PatternBrowseableSearchResults(array($sQuery, $aParams), 10, $aColumns, $aColumnTypes, $aColumnHeaders, $aLinkURLs, $aDBQueryStringColumns, $aQueryStringVariableNames);
    $oPatternBrowse->setStartIndex($iStartIndex);
    $oPatternBrowse->setSearchText("");
    $oPatternBrowse->setRememberValues($aReq);

    return renderHeading(_("Advanced Search")) . $oPatternBrowse->render() . $sRefreshMessage;
}

function dealWithAdvancedSearch($aReq, $iStartIndex) {
    global $main;
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml(getAdvancedSearchResults($aReq, $iStartIndex));
    $main->setCentralPayload($oPatternCustom);				                                
    $main->render();
}

if (checkSession()) {	
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableTableSqlQuery.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("advancedSearchUI.inc");
	require_once("advancedSearchUtil.inc");	
	
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

    if (!isset($fStartIndex)) {
        $fStartIndex = 1;
    }

	if (strlen($fForSearch)) {		
        dealWithAdvancedSearch($_REQUEST, $fStartIndex);
	} else {	
		//display search criteria
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml(getSearchPage());
		$main->setHasRequiredFields(true);
		$main->setCentralPayload($oPatternCustom);                                
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fForSearch=1");                                
		$main->render();
	}	
}
?>
