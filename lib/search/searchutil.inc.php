<?php

require_once(KT_LIB_DIR . '/browse/Criteria.inc');

class KTSearchUtil {
    function _oneCriteriaSetToSQL($aOneCriteriaSet) {
        $aSQL = array();
        $aJoinSQL = array();
        $criteria_set = array();
        foreach ($aOneCriteriaSet as $order => $dataset) {
            $type = KTUtil::arrayGet($dataset, "type");
            $sql = KTUtil::arrayGet($dataset, "sql");
            if (!empty($type)) {
                $oCriterion = Criteria::getCriterionByNumber($dataset["type"]);
                if (PEAR::isError($oCriterion)) {
                    return PEAR::raiseError('Invalid criteria specified.');
                }
                $criteria_set[] = array($oCriterion, $dataset["data"]);
            } else if (!empty($sql)) {
                $criteria_set[] = $sql;
            } else {
                return PEAR::raiseError('Invalid criteria specified.');
            }
        }
        foreach ($criteria_set as $oCriterionPair) {
            $oCriterion = $oCriterionPair[0];
            $aReq = $oCriterionPair[1];
            if (is_object($oCriterion)) {
                $res = $oCriterion->searchSQL($aReq);
                if (!is_null($res)) {
                    $aSQL[] = $res;
                }
                $res = $oCriterion->searchJoinSQL();
                if (!is_null($res)) {
                    $aJoinSQL[] = $res;
                }
            } else {
                $aSQL[] = array($oCriterion, $aReq);
            }
        }

        $aCritParams = array();
        $aCritQueries = array();
        foreach ($aSQL as $sSQL) {
            if (is_array($sSQL)) {
                $aCritQueries[] = '('.$sSQL[0].')';
                $aCritParams = array_merge($aCritParams , $sSQL[1]);
            } else {
                $aCritQueries[] = '('.$sSQL.')';
            }
        }

        if (count($aCritQueries) == 0) {
            return PEAR::raiseError("No search criteria were specified");
        }

        return array($aCritQueries, $aCritParams, $aJoinSQL);
    }

    function criteriaSetToSQL($aCriteriaSet, $iRecurseLevel = 0) {
        $aJoinSQL = array();
        $aSearchStrings = array();
        $aParams = array();
        foreach ($aCriteriaSet["subgroup"] as $k => $aOneCriteriaSet) {
            $aValues = KTUtil::arrayGet($aOneCriteriaSet, "values");
            $aSubgroup = KTUtil::arrayGet($aOneCriteriaSet, "subgroup");
            if (!empty($aValues)) {
                list($aThisCritQueries, $aThisParams, $aThisJoinSQL) = KTSearchUtil::_oneCriteriaSetToSQL($aOneCriteriaSet["values"]);
                $aJoinSQL = array_merge($aJoinSQL, $aThisJoinSQL);
                $aParams = array_merge($aParams, $aThisParams);
                $tabs = str_repeat("\t", ($iRecurseLevel + 2));
                $aSearchStrings[] = "\n$tabs(\n$tabs\t" . join("\n " . KTUtil::arrayGet($aOneCriteriaSet, 'join', "AND") . " ", $aThisCritQueries) . "\n$tabs)";
            } else if (!empty($aSubgroup)) {
                list($sThisSearchString, $aThisParams, $sThisJoinSQL) =
                    KTSearchUtil::criteriaSetToSQL($aOneCriteriaSet, $iRecurseLevel + 1);
                $aJoinSQL[] = $sThisJoinSQL;
                $aParams = array_merge($aParams, $aThisParams);
                $aSearchStrings[] = $sThisSearchString;
            }
        }
        $sJoinSQL = join(" ", $aJoinSQL);
        $tabs = str_repeat("\t", $iRecurseLevel + 1);
        $sSearchString = "\n$tabs(" . join("\n$tabs\t" . $aCriteriaSet['join'] . " ", $aSearchStrings) .  "\n$tabs)";
        return array($sSearchString, $aParams, $sJoinSQL);
    }

    function permissionToSQL($oUser, $sPermissionName) {
        if (is_null($oUser)) {
            return array("", array(), "");
        }
        $oPermission =& KTPermission::getByName('ktcore.permissions.read');
        $sPermissionLookupsTable = KTUtil::getTableName('permission_lookups');
        $sPermissionLookupAssignmentsTable = KTUtil::getTableName('permission_lookup_assignments');
        $sPermissionDescriptorsTable = KTUtil::getTableName('permission_descriptors');
        $sJoinSQL = "
            INNER JOIN $sPermissionLookupsTable AS PL ON D.permission_lookup_id = PL.id
            INNER JOIN $sPermissionLookupAssignmentsTable AS PLA ON PL.id = PLA.permission_lookup_id AND PLA.permission_id = ?
            ";
        $aGroups = GroupUtil::listGroupsForUserExpand($oUser);
        $aPermissionDescriptors = KTPermissionDescriptor::getByGroups($aGroups, array('ids' => true));
        $sPermissionDescriptors = DBUtil::paramArray($aPermissionDescriptors);
        $sSQLString = "PLA.permission_descriptor_id IN ($sPermissionDescriptors)";
        $aParams = array($oPermission->getId());
        $aParams = array_merge($aParams, $aPermissionDescriptors);
        return array($sSQLString, $aParams, $sJoinSQL);
    }

    function criteriaToLegacyQuery($aCriteriaSet, $oUser, $sPermissionName) {
        global $default;
        $aOptions = array(
            'select' => "F.name AS folder_name, F.id AS folder_id, D.id AS document_id, D.name AS document_name, D.filename AS file_name, 'View' AS view",
            'join' => "INNER JOIN $default->folders_table AS F ON D.folder_id = F.id",
        );
        return KTSearchUtil::criteriaToQuery($aCriteriaSet, $oUser, $sPermissionName, $aOptions);
    }

    function criteriaToQuery($aCriteriaSet, $oUser, $sPermissionName, $aOptions = null) {
        global $default;
        $sSelect = KTUtil::arrayGet($aOptions, 'select', 'D.id AS document_id');
        $sInitialJoin = KTUtil::arrayGet($aOptions, 'join', '');

        list($sSQLSearchString, $aCritParams, $sCritJoinSQL) = KTSearchUtil::criteriaSetToSQL($aCriteriaSet);

        $sToSearch = KTUtil::arrayGet($aOrigReq, 'fToSearch', 'Live'); // actually never present in this version.

        list ($sPermissionString, $aPermissionParams, $sPermissionJoin) = KTSearchUtil::permissionToSQL($oUser, $sPermissionName);
        //$sQuery = DBUtil::compactQuery("

        $aPotentialWhere = array($sPermissionString, 'SL.name = ?', "($sSQLSearchString)");
        $aWhere = array();
        foreach ($aPotentialWhere as $sWhere) {
            if (empty($sWhere)) {
                continue;
            }
            if ($sWhere == "()") {
                continue;
            }
            $aWhere[] = $sWhere;
        }
        $sWhere = "";
        if ($aWhere) {
            $sWhere = "\tWHERE " . join(" AND ", $aWhere);
        }       

        $sQuery = ("
    SELECT
        $sSelect
    FROM
        $default->documents_table AS D
        INNER JOIN $default->status_table AS SL on D.status_id=SL.id
        $sInitialJoin
        $sCritJoinSQL
        $sPermissionJoin
    $sWhere");
    // GROUP BY D.id

        $aParams = array();
        $aParams = array_merge($aParams, $aPermissionParams);
        $aParams[] = $sToSearch;
        $aParams = array_merge($aParams, $aCritParams);

        return array($sQuery, $aParams);
    }

    function testConditionOnDocument($oSearch, $oDocument) {
        $oSearch =& KTUtil::getObject('KTSavedSearch', $oSearch);
        $iDocumentId = KTUtil::getId($oDocument);

        $aCriteriaSet = array(
            "join" => "AND",
            "subgroup" => array(
                $oSearch->getSearch(),
                array(
                    "join" => "AND",
                    "values" => array(
                        array(
                            "sql" => array("D.id = ?", array($iDocumentId)),
                        ),
                    ),
                ),
            ),
        );
        $aOptions = array('select' => 'COUNT(DISTINCT(D.id)) AS cnt');
        $aQuery = KTSearchUtil::criteriaToQuery($aCriteriaSet, null, null, $aOptions);
        $cnt = DBUtil::getOneResultKey($aQuery, 'cnt');
        if (PEAR::isError($cnt)) {
            return $cnt;
        }
        if (is_null($cnt)) {
            return false;
        }
        if (!is_numeric($cnt)) {
            return PEAR::raiseError("Non-integer returned when looking for count");
        }
        return $cnt > 0;
    }
}

