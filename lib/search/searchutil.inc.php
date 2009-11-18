<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/search/savedsearch.inc.php');
require_once(KT_LIB_DIR . '/browse/Criteria.inc');

class KTSearchUtil {
    // {{{ _oneCriteriaSetToSQL
    /**
     * Handles leaf criteria set (ie, no subgroups), generating SQL for
     * the values in the criteria.
     *
     * (This would be the place to extend criteria to support contains,
     * starts with, ends with, greater than, and so forth.)
     */
    function _oneCriteriaSetToSQL($aOneCriteriaSet) {
        $aSQL = array();
        $aJoinSQL = array();
        $criteria_set = array();
        
        /*
         * First phase: get criterion object for search or the direct
         * SQL to use.
         *
         * XXX: Why is there $order there? 
         */
        foreach ($aOneCriteriaSet as $order => $dataset) {
            $type = KTUtil::arrayGet($dataset, "type");
            $sql = KTUtil::arrayGet($dataset, "sql");
            if (!empty($type)) {
                $oCriteriaRegistry =& KTCriteriaRegistry::getSingleton();       
                $oCriterion = $oCriteriaRegistry->getCriterion($dataset['type']);
                if (PEAR::isError($oCriterion)) {
                    return PEAR::raiseError(_kt('Invalid criteria specified.'));
                }
                $criteria_set[] = array($oCriterion, $dataset["data"]);
            } else if (!empty($sql)) {
                $criteria_set[] = $sql;
            } else {
                return PEAR::raiseError(_kt('Invalid criteria specified.'));
            }
        }

        /*
         * Second phase: Create an individual SQL query per criteria.
         */
        foreach ($criteria_set as $oCriterionPair) {
            $oCriterion = $oCriterionPair[0];
            $aReq = $oCriterionPair[1];
            
            if (is_object($oCriterion)) {
                if(is_array($aReq[$oCriterion->sNamespace]) && KTPluginUtil::pluginIsActive('inet.multiselect.lookupvalue.plugin'))
                {
                    //$newAReq = $aReq;
                    $aNewSQL = array();
                    foreach($aReq[$oCriterion->sNamespace] as $kkey => $vval)
                    {
                        $newAReq = $aReq;
                        $newAReq[$oCriterion->sNamespace] = $vval;
                        $res = $oCriterion->searchSQL($newAReq);
                        if (!is_null($res)) {
                            $aNewSQL[] = $res;
                        }
                    }

                    $aNewSQL0 = array();
                    $aNewSQL1 = array();
                    foreach($aNewSQL as $ind=>$sQ)
                    {
                        $aNewSQL0[] = $sQ[0];
                        $aNewSQL1 = array_merge($aNewSQL1,$sQ[1]);
                    }
                    
                    $aSQL[] = array(" ( ".join(" ) ".$aReq[$oCriterion->sNamespace."_join"]." ( ", $aNewSQL0)." ) ",$aNewSQL1 );
                    
                    $res = $oCriterion->searchJoinSQL();
                    if (!is_null($res)) {
                        $aJoinSQL[] = $res;
                    }
                }
                else
                {
                    $res = $oCriterion->searchSQL($aReq);
                    if (!is_null($res)) {
                        $aSQL[] = $res;
                    }
                    $res = $oCriterion->searchJoinSQL();
                    if (!is_null($res)) {
                        $aJoinSQL[] = $res;
                    }
                }
            } else {
                $aSQL[] = array($oCriterion, $aReq);
            }
        }

        /*
         * Third phase: build up $aCritQueries and $aCritParams, and put
         * parentheses around them.
         */
        $aCritParams = array();
        $aCritQueries = array();
        foreach ($aSQL as $sSQL) {
            if (is_array($sSQL)) {
                $aCritQueries[] = '('.$sSQL[0].')';
                $aCritParams = kt_array_merge($aCritParams , $sSQL[1]);
            } else {
                $aCritQueries[] = '('.$sSQL.')';
            }
        }

        if (count($aCritQueries) == 0) {
            return PEAR::raiseError(_kt("No search criteria were specified"));
        }

        return array($aCritQueries, $aCritParams, $aJoinSQL);
    }
    // }}}

    /**
     * All for folders only
     * Handles leaf criteria set (ie, no subgroups), generating SQL for
     * the values in the criteria.
     *
     * (This would be the place to extend criteria to support contains,
     * starts with, ends with, greater than, and so forth.)
     */
     function _oneCriteriaFolderSetToSQL($aOneCriteriaSet) {
            $aSQL = array();
            $aJoinSQL = array();
            $criteria_set = array();
            
            /*
             * First phase: get criterion object for search or the direct
             * SQL to use.
             *
             * XXX: Why is there $order there? 
             */
            foreach ($aOneCriteriaSet as $order => $dataset) {
                $type = KTUtil::arrayGet($dataset, "type");
                $sql = KTUtil::arrayGet($dataset, "sql");
                if (!empty($type)) {
                    $oCriteriaRegistry =& KTCriteriaRegistry::getSingleton();       
                    $oCriterion = $oCriteriaRegistry->getCriterion($dataset['type']);
                    
                    if (PEAR::isError($oCriterion)) {
                        return PEAR::raiseError(_kt('Invalid criteria specified.'));
                    }
                    $criteria_set[] = array($oCriterion, $dataset["data"]);
                } else if (!empty($sql)) {
                    $criteria_set[] = $sql;
                } else {
                    return PEAR::raiseError(_kt('Invalid criteria specified.'));
                }
            }
    
            /*
             * Second phase: Create an individual SQL query per criteria.
             */
            foreach ($criteria_set as $oCriterionPair) {
                    $oCriterion->aLookup[table]='folder_field_links';
                    
                $oCriterion = $oCriterionPair[0];
                $aReq = $oCriterionPair[1];
            
              
    
    
                if (is_object($oCriterion)) {
                    // changed by dp start // for multiselect search for folders
                    if(is_array($aReq[$oCriterion->sNamespace]) && KTPluginUtil::pluginIsActive('inet.multiselect.lookupvalue.plugin'))
                    {
                        $aNewSQL = array();
                        foreach($aReq[$oCriterion->sNamespace] as $kkey => $vval)
                        {
                            $newAReq = $aReq;
                            $newAReq[$oCriterion->sNamespace] = $vval;
                            $res = $oCriterion->searchSQL($newAReq);
                            if (!is_null($res)) {
                                $aNewSQL[] = $res;
                            }
                        }
    
                        $aNewSQL0 = array();
                        $aNewSQL1 = array();
                        foreach($aNewSQL as $ind=>$sQ)
                        {
                            $aNewSQL0[] = $sQ[0];
                            $aNewSQL1 = array_merge($aNewSQL1,$sQ[1]);
                        }
                        $aSQL[] = array(" ( ".join(" ) ".$aReq[$oCriterion->sNamespace."_join"]." ( ", $aNewSQL0)." ) ",$aNewSQL1 );
                        $res = $oCriterion->searchJoinSQL();
                        if (!is_null($res)) {
                            if(strstr($res,'D.metadata_version_id')){
                                $res=str_replace('D.metadata_version_id','F.metadata_version_id',$res);
                            }
                            if(strstr($res,'document_fields_link')){
                                $res=str_replace('document_fields_link','folder_fields_link',$res);
                            }
                            $aJoinSQL[] = $res;
                        }
                    }// changed by dp end // for multiselect search for folders
                    else
                    {
                        $res = $oCriterion->searchSQL($aReq);
                        if (!is_null($res)) {
                            $aSQL[] = $res;
                        }
                        $res = $oCriterion->searchJoinSQL();
                        if (!is_null($res)) {
        
                            if(strstr($res,'D.metadata_version_id')){
                                $res=str_replace('D.metadata_version_id','F.metadata_version_id',$res);
                            }
                            if(strstr($res,'document_fields_link')){
                                $res=str_replace('document_fields_link','folder_fields_link',$res);
                            }
                            $aJoinSQL[] = $res;
                        }
                    }   
                    
                } else {
                    $aSQL[] = array($oCriterion, $aReq);
                }
            }
            
            /*
             * Third phase: build up $aCritQueries and $aCritParams, and put
             * parentheses around them.
             */
            $aCritParams = array();
            $aCritQueries = array();
            foreach ($aSQL as $sSQL) {
                if (is_array($sSQL)) {
                    $aCritQueries[] = '('.$sSQL[0].')';
                    $aCritParams = kt_array_merge($aCritParams , $sSQL[1]);
                } else {
                    $aCritQueries[] = '('.$sSQL.')';
                }
            }
            
            
            if (count($aCritQueries) == 0) {
                return PEAR::raiseError(_kt("No search criteria were specified"));
            }
    
            return array($aCritQueries, $aCritParams, $aJoinSQL);
        }
    /**
     * All for folders
     * Converts a criteria set to the SQL joins, where clause, and
     * parameters necessary to ensure that the criteria listed restrict
     * the folders returned to those that match the criteria.
     *
     * Perhaps poorly called recursively to handle criteria that involve
     * subgroups to allow infinitely nested criteria.
     *
     * Returns a list of the following elements:
     *      - String representing the where clause
     *      - Array of parameters that go with the where clause
     *      - String with the SQL necessary to join with the tables in the
     *        where clause
     */ 
     function criteriaFolderSetToSQL($aCriteriaSet, $iRecurseLevel = 0) {
        $aJoinSQL = array();
        $aSearchStrings = array();
        $aParams = array();
        /*
         * XXX: We unnecessarily force the base criteria to have
         * subgroups at the top level, even though we most often only
         * have a single "subgroup".
         */
        
        foreach ($aCriteriaSet["subgroup"] as $k => $aOneCriteriaSet) {
            /*
             * Each subgroup will either have values or it will have
             * subgroups.  They can't be mixed.
             */
            $aValues = KTUtil::arrayGet($aOneCriteriaSet, "values");
            $aSubgroup = KTUtil::arrayGet($aOneCriteriaSet, "subgroup");
            if (!empty($aValues)) {

                $res = KTSearchUtil::_oneCriteriaFolderSetToSQL($aOneCriteriaSet["values"]);
                
                if(PEAR::isError($res)) {
                    return $res;
                }
                list($aThisCritQueries, $aThisParams, $aThisJoinSQL) = $res;
                $aJoinSQL = kt_array_merge($aJoinSQL, $aThisJoinSQL);
                $aParams = kt_array_merge($aParams, $aThisParams);
                $tabs = str_repeat("\t", ($iRecurseLevel + 2));
                $aSearchStrings[] = "\n$tabs(\n$tabs\t" . join("\n " . KTUtil::arrayGet($aOneCriteriaSet, 'join', "AND") . " ", $aThisCritQueries) . "\n$tabs)";
            } else if (!empty($aSubgroup)) {
                
                /*
                 * Recurse if we have a criteria set with subgroups.
                 * Recurselevel makes the tabs increase as we recurse so
                 * that the SQL statement is somewhat understandable.
                 */
                list($sThisSearchString, $aThisParams, $sThisJoinSQL) =
                    KTSearchUtil::criteriaFolderSetToSQL($aOneCriteriaSet, $iRecurseLevel + 1);
                $aJoinSQL[] = $sThisJoinSQL;
                $aParams = kt_array_merge($aParams, $aThisParams);
                $aSearchStrings[] = $sThisSearchString;
            }
        }
        $aJoinSQL = array_unique($aJoinSQL);
        $sJoinSQL = join(" ", $aJoinSQL);
        $tabs = str_repeat("\t", $iRecurseLevel + 1);
        $sSearchString = "\n$tabs(" . join("\n$tabs\t" . $aCriteriaSet['join'] . " ", $aSearchStrings) .  "\n$tabs)";

        return array($sSearchString, $aParams, $sJoinSQL);
    }
    
    /**
     * All for folders
     * Converts a criteria set into a SQL query that (by default)
     * returns the ids of documents that fulfil the criteria.
     *
     * $aOptions is a dictionary that can contain:
     *      - select - a string that contains the list of columns
     *        selected in the query
     *      - join - a string that contains join conditions to satisfy
     *        the select string passed or limit the documents included
     *
     * A list with the following elements is returned:
     *      - String containing the parameterised SQL query
     *      - Array containing the parameters for the SQL query
     */
     function criteriaToFolderQuery($aCriteriaSet, $oUser, $sPermissionName, $aOptions = null) {
        global $default;
        $sSelect = KTUtil::arrayGet($aOptions, 'select', 'F.id AS folder_id');
        $sInitialJoin = KTUtil::arrayGet($aOptions, 'join', '');
        if (is_array($sInitialJoin)) {
            $aInitialJoinParams = $sInitialJoin[1];
            $sInitialJoin = $sInitialJoin[0];
        }

    $res = KTSearchUtil::criteriaFolderSetToSQL($aCriteriaSet);

    if(PEAR::isError($res)) return $res;
        list($sSQLSearchString, $aCritParams, $sCritJoinSQL) = $res;
      
        $sToSearch = KTUtil::arrayGet($aOrigReq, 'fToSearch', 'Live'); // actually never present in this version.

        $res = KTSearchUtil::permissionToSQL($oUser, $sPermissionName);
        
        if (PEAR::isError($res)) {        // only occurs if the group has no permissions.
            return $res;
        } else {
            list ($sPermissionString, $aPermissionParams, $sPermissionJoin) = $res;
        }
        
        /*
         * This is to overcome the problem where $sPermissionString (or
         * even $sSQLSearchString) is empty, leading to leading or
         * trailing ANDs.
         */
        $aPotentialWhere = array($sPermissionString,"($sSQLSearchString)");
        
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

        //$sQuery = DBUtil::compactQuery("
        $sQuery = sprintf("
    SELECT
        %s
    FROM
        %s AS F
        LEFT JOIN %s AS DM ON F.metadata_version_id = DM.id
        %s
        %s
        %s
        %s", $sSelect, KTUtil::getTableName('folders'),
        KTUtil::getTableName('folder_metadata_version'),

        $sInitialJoin,
        $sCritJoinSQL,
        $sPermissionJoin,
        $sWhere
        );
    // GROUP BY D.id

        $aParams = array();
        $aParams = kt_array_merge($aParams, $aInitialJoinParams);
        $aParams = kt_array_merge($aParams, $aPermissionParams);

        if($sToSearch!='Live')
        $aParams[] = $sToSearch;
        $aParams = kt_array_merge($aParams, $aCritParams);
        
        
        if(strstr($sQuery,'document_field_id')){
            $sQuery=str_replace('document_field_id','folder_field_id',$sQuery);
        }
        if(strstr($sQuery,'D.creator_id')){
            $sQuery=str_replace('D.creator_id','F.creator_id',$sQuery);
        }
        return array($sQuery, $aParams);
    }
    // {{{ criteriaSetToSQL
    /**
     * Converts a criteria set to the SQL joins, where clause, and
     * parameters necessary to ensure that the criteria listed restrict
     * the documents returned to those that match the criteria.
     *
     * Perhaps poorly called recursively to handle criteria that involve
     * subgroups to allow infinitely nested criteria.
     *
     * Returns a list of the following elements:
     *      - String representing the where clause
     *      - Array of parameters that go with the where clause
     *      - String with the SQL necessary to join with the tables in the
     *        where clause
     */
    function criteriaSetToSQL($aCriteriaSet, $iRecurseLevel = 0) {
        $aJoinSQL = array();
        $aSearchStrings = array();
        $aParams = array();
        /*
         * XXX: We unnecessarily force the base criteria to have
         * subgroups at the top level, even though we most often only
         * have a single "subgroup".
         */
        
        foreach ($aCriteriaSet["subgroup"] as $k => $aOneCriteriaSet) {
            /*
             * Each subgroup will either have values or it will have
             * subgroups.  They can't be mixed.
             */
            $aValues = KTUtil::arrayGet($aOneCriteriaSet, "values");
            $aSubgroup = KTUtil::arrayGet($aOneCriteriaSet, "subgroup");
            if (!empty($aValues)) {
                $res = KTSearchUtil::_oneCriteriaSetToSQL($aOneCriteriaSet["values"]);
                if(PEAR::isError($res)) {
                    return $res;
                }
                list($aThisCritQueries, $aThisParams, $aThisJoinSQL) = $res;
                $aJoinSQL = kt_array_merge($aJoinSQL, $aThisJoinSQL);
                $aParams = kt_array_merge($aParams, $aThisParams);
                $tabs = str_repeat("\t", ($iRecurseLevel + 2));
                $aSearchStrings[] = "\n$tabs(\n$tabs\t" . join("\n " . KTUtil::arrayGet($aOneCriteriaSet, 'join', "AND") . " ", $aThisCritQueries) . "\n$tabs)";
            } else if (!empty($aSubgroup)) {
                /*
                 * Recurse if we have a criteria set with subgroups.
                 * Recurselevel makes the tabs increase as we recurse so
                 * that the SQL statement is somewhat understandable.
                 */
                list($sThisSearchString, $aThisParams, $sThisJoinSQL) =
                    KTSearchUtil::criteriaSetToSQL($aOneCriteriaSet, $iRecurseLevel + 1);
                $aJoinSQL[] = $sThisJoinSQL;
                $aParams = kt_array_merge($aParams, $aThisParams);
                $aSearchStrings[] = $sThisSearchString;
            }
        }
        $aJoinSQL = array_unique($aJoinSQL);
        $sJoinSQL = join(" ", $aJoinSQL);
        $tabs = str_repeat("\t", $iRecurseLevel + 1);
        $sSearchString = "\n$tabs(" . join("\n$tabs\t" . $aCriteriaSet['join'] . " ", $aSearchStrings) .  "\n$tabs)";
        
        return array($sSearchString, $aParams, $sJoinSQL);
    }
    // }}}

    // {{{ permissionToSQL
    /**
     * Generates the necessary joins and where clause and parameters to
     * ensure that all the documents returns are accessible to the user
     * given for the permission listed.
     *
     * Returns a list of the following elements:
     *      - String representing the where clause
     *      - Array of parameters that go with the where clause
     *      - String with the SQL necessary to join with the tables in the
     *        where clause
     */
    function permissionToSQL($oUser, $sPermissionName, $sItemTableName = "D") {
        if (is_null($oUser)) {
            return array("", array(), "");
        }
        if (is_null($sPermissionName)) {
            $sPermissionName = 'ktcore.permissions.read';
        }
        $oPermission =& KTPermission::getByName($sPermissionName);
        $sPermissionLookupsTable = KTUtil::getTableName('permission_lookups');
        $sPermissionLookupAssignmentsTable = KTUtil::getTableName('permission_lookup_assignments');
        $sPermissionDescriptorsTable = KTUtil::getTableName('permission_descriptors');
        $sJoinSQL = "
            INNER JOIN $sPermissionLookupsTable AS PL ON $sItemTableName.permission_lookup_id = PL.id
            INNER JOIN $sPermissionLookupAssignmentsTable AS PLA ON PL.id = PLA.permission_lookup_id AND PLA.permission_id = ?
            ";
        $aPermissionDescriptors = KTPermissionUtil::getPermissionDescriptorsForUser($oUser);
        if (count($aPermissionDescriptors) === 0) {
            return PEAR::raiseError(_kt('You have no permissions'));
        }
        $sPermissionDescriptors = DBUtil::paramArray($aPermissionDescriptors);
        $sSQLString = "PLA.permission_descriptor_id IN ($sPermissionDescriptors)";
        $aParams = array($oPermission->getId());
        $aParams = kt_array_merge($aParams, $aPermissionDescriptors);
        return array($sSQLString, $aParams, $sJoinSQL);
    }
    // }}}

    // {{{ criteriaToLegacyQuery
    /**
     * Converts a criteria set into a SQL query that returns all the
     * information that the legacy search results page
     * (PatternBrowsableSearchResults) requires for documents that
     * fulfil the criteria.
     *
     * Like criteriaToQuery, a list with the following elements is
     * returned:
     *      - String containing the parameterised SQL query
     *      - Array containing the parameters for the SQL query
     */
    function criteriaToLegacyQuery($aCriteriaSet, $oUser, $sPermissionName) {
        global $default;
        $aOptions = array(
            'select' => "F.name AS folder_name, F.id AS folder_id, D.id AS document_id, D.name AS document_name, D.filename AS file_name, 'View' AS view",
            'join' => "INNER JOIN $default->folders_table AS F ON D.folder_id = F.id",
        );
        return KTSearchUtil::criteriaToQuery($aCriteriaSet, $oUser, $sPermissionName, $aOptions);
    }
    // }}}

    // {{{ criteriaToQuery
    /**
     * Converts a criteria set into a SQL query that (by default)
     * returns the ids of documents that fulfil the criteria.
     *
     * $aOptions is a dictionary that can contain:
     *      - select - a string that contains the list of columns
     *        selected in the query
     *      - join - a string that contains join conditions to satisfy
     *        the select string passed or limit the documents included
     *
     * A list with the following elements is returned:
     *      - String containing the parameterised SQL query
     *      - Array containing the parameters for the SQL query
     */
    function criteriaToQuery($aCriteriaSet, $oUser, $sPermissionName, $aOptions = null) {
        global $default;
        $sSelect = KTUtil::arrayGet($aOptions, 'select', 'D.id AS document_id');
        $sInitialJoin = KTUtil::arrayGet($aOptions, 'join', '');
        if (is_array($sInitialJoin)) {
            $aInitialJoinParams = $sInitialJoin[1];
            $sInitialJoin = $sInitialJoin[0];
        }

    $res = KTSearchUtil::criteriaSetToSQL($aCriteriaSet);

    if(PEAR::isError($res)) return $res;
        list($sSQLSearchString, $aCritParams, $sCritJoinSQL) = $res;
      
        $sToSearch = KTUtil::arrayGet($aOrigReq, 'fToSearch', 'Live'); // actually never present in this version.

        $res = KTSearchUtil::permissionToSQL($oUser, $sPermissionName);
        
        if (PEAR::isError($res)) {        // only occurs if the group has no permissions.
            return $res;
        } else {
            list ($sPermissionString, $aPermissionParams, $sPermissionJoin) = $res;
        }
        
        /*
         * This is to overcome the problem where $sPermissionString (or
         * even $sSQLSearchString) is empty, leading to leading or
         * trailing ANDs.
         */
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

        //$sQuery = DBUtil::compactQuery("
        $sQuery = sprintf("
    SELECT
        %s
    FROM
        %s AS D
        LEFT JOIN %s AS DM ON D.metadata_version_id = DM.id
        LEFT JOIN %s AS DC ON DM.content_version_id = DC.id
        INNER JOIN $default->status_table AS SL on D.status_id=SL.id
        %s
        %s
        %s
        %s", $sSelect, KTUtil::getTableName('documents'),
        KTUtil::getTableName('document_metadata_version'),
        KTUtil::getTableName('document_content_version'),
        $sInitialJoin,
        $sCritJoinSQL,
        $sPermissionJoin,
        $sWhere
        );
    // GROUP BY D.id

        $aParams = array();
        $aParams = kt_array_merge($aParams, $aInitialJoinParams);
        $aParams = kt_array_merge($aParams, $aPermissionParams);
        $aParams[] = $sToSearch;
        $aParams = kt_array_merge($aParams, $aCritParams);
        return array($sQuery, $aParams);
    }
    // }}}

    // {{{ testConditionOnDocument
    /**
     * Checks whether a condition (saved search) is fulfilled by the
     * given document.
     *
     * For example, a condition may require a specific value in a
     * metadata field.
     *
     * Returns either true or false (or a PEAR Error object)
     */
    function testConditionOnDocument($oSearch, $oDocument) {
        $oSearch =& KTUtil::getObject('KTSavedSearch', $oSearch);
        $iDocumentId = KTUtil::getId($oDocument);

        /*
         * Make a new criteria set, an AND of the existing criteria set
         * and the sql statement requiring that D.id be the document id
         * given to us.
         */
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
        if (PEAR::isError($aQuery)) {          // caused by no permissions being set.
            return false; 
        }
        $cnt = DBUtil::getOneResultKey($aQuery, 'cnt');
        if (PEAR::isError($cnt)) {
            return $cnt;
        }
        if (is_null($cnt)) {
            return false;
        }
        if (!is_numeric($cnt)) {
            return PEAR::raiseError(_kt("Non-integer returned when looking for count"));
        }
        return $cnt > 0;
    }
    // }}}
    
    
function testConditionOnFolder($oSearch, $oFolder) {
        $oSearch =& KTUtil::getObject('KTSavedSearch', $oSearch);
        $iFolderId = KTUtil::getId($oFolder);

        /*
         * Make a new criteria set, an AND of the existing criteria set
         * and the sql statement requiring that D.id be the document id
         * given to us.
         */
        $aCriteriaSet = array(
            "join" => "AND",
            "subgroup" => array(
                $oSearch->getSearch(),
                array(
                    "join" => "AND",
                    "values" => array(
                        array(
                            "sql" => array("F.id = ?", array($iFolderId)),
                        ),
                    ),
                ),
            ),
        );
        $aOptions = array('select' => 'COUNT(DISTINCT(F.id)) AS cnt');
        $aQuery = KTSearchUtil::criteriaToFolderQuery($aCriteriaSet, null, null, $aOptions);



        if (PEAR::isError($aQuery)) {          // caused by no permissions being set.
            return false; 
        }
        $cnt = DBUtil::getOneResultKey($aQuery, 'cnt');

        if (PEAR::isError($cnt)) {
            return $cnt;
        }
        if (is_null($cnt)) {
            return false;
        }
        if (!is_numeric($cnt)) {
            return PEAR::raiseError(_kt("Non-integer returned when looking for count"));
        }
        
        return $cnt > 0;
    }
     
}