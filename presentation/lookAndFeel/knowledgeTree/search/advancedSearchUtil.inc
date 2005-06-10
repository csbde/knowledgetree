<?php
/**
 * $Id$
 *
 * Business logic used to perform advanced search.  Advanced search allows
 * users to search by meta data types.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 * @package search
 */

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
    $aJoinSQL = array();
    foreach ($aIDs as $iID) {
        $oCriterion =& Criteria::getCriterionByNumber($iID);
        $res = $oCriterion->searchSQL($aReq);
        if (!is_null($res)) {
            $aSQL[] = $res;
        }
        $res = $oCriterion->searchJoinSQL();
        if (!is_null($res)) {
            $aJoinSQL[] = $res;
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
    $sJoinSQL = join(" ", $aJoinSQL);

    $sToSearch = KTUtil::arrayGet($aOrigReq, 'fToSearch', 'Live');

    $sQuery = DBUtil::compactQuery("
SELECT
    F.name AS folder_name, F.id AS folder_id, D.id AS document_id,
    D.name AS document_name, D.filename AS file_name, COUNT(D.id) AS doc_count, 'View' AS view
FROM
    $default->documents_table AS D
    INNER JOIN $default->folders_table AS F ON D.folder_id = F.id
    $sJoinSQL
    INNER JOIN $default->search_permissions_table AS SDUL ON SDUL.document_id = D.id
    INNER JOIN $default->status_table AS SL on D.status_id=SL.id
WHERE
	(F.is_public OR
    SDUL.user_id = ?)
    AND SL.name = ?
    AND ($sSQLSearchString)
GROUP BY D.id
ORDER BY doc_count DESC");

    $aParams = array();
    $aParams[] = $_SESSION["userID"];
    $aParams[] = $sToSearch;
    $aParams = array_merge($aParams, $aCritParams);

    //var_dump(DBUtil::getResultArray(array($sQuery, $aParams)));
    //exit(0);

    $aColumns = array("folder_name", "file_name", "document_name", "doc_count", "view");
    $aColumnTypes = array(3,3,3,1,3);
    $aColumnHeaders = array("<font color=\"ffffff\"><img src=$default->graphicsUrl/widgets/dfolder.gif>" . _("Folder") . "</font>", "<font color=\"ffffff\">" . _("Name") . "</font>", "<font color=\"ffffff\">" . _("Title") . "</font>", "<font color=\"ffffff\">" . _("Matches") . "</font>", "<font color=\"ffffff\">" . _("View") . "</font>");
    $aLinkURLs = array("$default->rootUrl/control.php?action=browse","$default->rootUrl/control.php?action=viewDocument", "$default->rootUrl/control.php?action=viewDocument", null, "$default->rootUrl/control.php?action=downloadDocument");
    $aDBQueryStringColumns = array("document_id","folder_id");
    $aQueryStringVariableNames = array("fDocumentID", "fFolderID");

    $oPatternBrowse = & new PatternBrowseableSearchResults(array($sQuery, $aParams), 10, $aColumns, $aColumnTypes, $aColumnHeaders, $aLinkURLs, $aDBQueryStringColumns, $aQueryStringVariableNames);
    $oPatternBrowse->setStartIndex($iStartIndex);
    $oPatternBrowse->setSearchText("");
    $oPatternBrowse->setRememberValues($aReq);
    $sForSearch = "<input type=\"hidden\" name=\"fForSearch\" value=\"1\" />";

    return renderHeading(_("Advanced Search")) . $oPatternBrowse->render() . $sForSearch . $sRefreshMessage;
}

function dealWithAdvancedSearch($aReq, $iStartIndex) {
    global $main;
    $oPatternCustom = & new PatternCustom();
    $oPatternCustom->setHtml(getAdvancedSearchResults($aReq, $iStartIndex));
    $main->setCentralPayload($oPatternCustom);				                                
    $main->render();
}

?>
