<?php

/* Partial Query
 * 
 * Each of the different partial queries handles generating a document and folder
 * list.  Also handles sorting.
 *
 */
 
// FIXME API how to handle indicating which other rows need joining 

require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/search/searchutil.inc.php");
 
// Abstract base class.
class PartialQuery {
    // initialise here (pass whatever this needs)
    function PartialQuery() { ; }

    // no batching.  just use count.
    function getFolderCount() { return 0; }
    function getDocumentCount() { return 0; }
    
    /* Generating the items for the collection requires generating the core of the
     * query, and then adding the columns and tables that are needed to make the 
     * the sorting work.  naturally, this could be somewhat complex, so in order 
     * to make everything clear, a number of "namespaces" are reserved in the simple
     * case.  The SearchQuery needs a number of others, and those are discussed there.
     * 
     *   the sort column should be joined as "sort_col."
     *   the documents column is joined as "D."
     *   the folders column is joined as "F."
     *
     * In order to allow the additional table-joins, etc, the "$sJoinClause, $aJoinParams" 
     * should be passed through.  This should _completely_ handle the join, and should depend only
     * on columns that are known to be there.
     *
     * Browse currently has no way to specify additional constraints.  For that, 
     * use SearchQuery or create a new PartialQuery object.
     *
     * The abstraction is not complete, and some amount of understanding about the specific 
     * query being _created_ is required.  Once this is done, minimal changes in the view 
     * object should be required.
     */        
     
    // with batching.
    function getFolders($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { return array(); }
    function getDocuments($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { return array(); }
}

class BrowseQuery extends PartialQuery{
    // FIXME cache permission lookups, etc.
    var $folder_id = -1;
    var $sPermissionName = "ktcore.permissions.read";

    function BrowseQuery($iFolderId, $oUser = null, $aOptions = null) {
        $this->folder_id = $iFolderId;
        if (is_null($oUser)) {
            $oUser = User::get($_SESSION['userID']);
        }
        $this->oUser =& $oUser;
        $this->aOptions = $aOptions;
        if (KTUtil::arrayGet($aOptions, 'ignorepermissions')) {
            $this->oUser = null;
        }
    }
    
    function _getDocumentQuery($aOptions = null) {
        $res = KTSearchUtil::permissionToSQL($this->oUser, $this->sPermissionName);
        if (PEAR::isError($res)) {
            return $res;
        }
        list($sPermissionString, $aPermissionParams, $sPermissionJoin) = $res;
        $aPotentialWhere = array($sPermissionString, 'D.folder_id = ?', 'D.status_id = 1');
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

        $sSelect = KTUtil::arrayGet($aOptions, 'select', 'D.id');

        $sQuery = sprintf("SELECT %s FROM %s AS D
                LEFT JOIN %s AS DM ON D.metadata_version_id = DM.id
                LEFT JOIN %s AS DC ON DM.content_version_id = DC.id
                %s
                %s %s",
                $sSelect, KTUtil::getTableName("documents"),
                KTUtil::getTableName("document_metadata_version"),
                KTUtil::getTableName("document_content_version"),
                $this->sDocumentJoinClause, $sPermissionJoin, $sWhere);
        $aParams = array();
        $aParams = array_merge($aParams, $this->aDocumentJoinParams);
        $aParams = array_merge($aParams, $aPermissionParams);
        $aParams[] = $this->folder_id;
        return array($sQuery, $aParams);
    }

    function _getFolderQuery($aOptions = null) {
        $res = KTSearchUtil::permissionToSQL($this->oUser, $this->sPermissionName, "F");
        if (PEAR::isError($res)) {
           return $res;
        }
        list($sPermissionString, $aPermissionParams, $sPermissionJoin) = $res;

        $aPotentialWhere = array($sPermissionString, 'F.parent_id = ?');
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

        $sSelect = KTUtil::arrayGet($aOptions, 'select', 'F.id');

        $sQuery = "SELECT $sSelect FROM " . KTUtil::getTableName("folders") . " AS F $sPermissionJoin $sWhere ";
        $aParams = array();
        $aParams = array_merge($aParams,  $aPermissionParams);
        $aParams[] = $this->folder_id;
        return array($sQuery, $aParams);
    }
    
    function getFolderCount() { 
        $aOptions = array(
            'select' => 'count(F.id) AS cnt',
        );
        $aQuery = $this->_getFolderQuery($aOptions);
        if (PEAR::isError($aQuery)) { return 0; }
        $iRet = DBUtil::getOneResultKey($aQuery, 'cnt');
        return $iRet;
    }
    
    function getDocumentCount() { 
        $aOptions = array(
            'select' => 'count(D.id) AS cnt',
        );
        $aQuery = $this->_getDocumentQuery($aOptions);
        if (PEAR::isError($aQuery)) { return 0; }
        $iRet = DBUtil::getOneResultKey($aQuery, 'cnt');
        return $iRet;
    }
    
    function getFolders($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        $res = $this->_getFolderQuery();
        if (PEAR::isError($res)) { return array(); }
        list($sQuery, $aParams) = $res;
        $sQuery .= " ORDER BY " . $sSortColumn . " " . $sSortOrder . " ";

        $sQuery .= " LIMIT ?, ?";
        $aParams[] = $iBatchStart;
        $aParams[] = $iBatchSize;
    
        $q = array($sQuery, $aParams);
        
        $res = DBUtil::getResultArray($q); 
        
        return $res;
    }
    
    function getDocuments($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        $this->sDocumentJoinClause = $sJoinClause;
        $this->aDocumentJoinParams = $aJoinParams;
        $res = $this->_getDocumentQuery();
        if (PEAR::isError($res)) { return array(); } // no permissions 
        list($sQuery, $aParams) = $res;
        $sQuery .= " ORDER BY " . $sSortColumn . " " . $sSortOrder . " ";

        $sQuery .= " LIMIT ?, ?";
        $aParams[] = $iBatchStart;
        $aParams[] = $iBatchSize;
        
        $q = array($sQuery, $aParams);
        
        $res = DBUtil::getResultArray($q); 
         
        
        
        return $res;
    }
}

// testing class - puts docs/folders into testdocs, testfolders.
class TestQuery extends PartialQuery{
    
    var $testdocs;
    var $testfolders;

    function TestQuery() { 
        $this->testdocs = array(array("id" => 2), array("id" => 3),
        );
        $this->testfolders = array(array("id" => 3),);
    }
    
    function getFolderCount() { count($this->testfolders); }
    function getDocumentCount() { count($this->testdocs); }
    
    // with batching.
    function getFolders($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder) { 
        return array_slice($this->testfolders, $iBatchStart, $iBatchSize);
    }
    
    function getDocuments($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder) { 
        return array_slice($this->testdocs, $iBatchStart, $iBatchSize);
    }
}

class SimpleSearchQuery extends PartialQuery {  
    // FIXME cache permission lookups, etc.
    var $searchable_text;

    function SimpleSearchQuery($sSearchableText) { $this->searchable_text = $sSearchableText; }
    
    function getFolderCount() { 
        // never any folders, given the current fulltext environ.
        return 0;
    }

    function getFolders($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        return array();
    }

    function getQuery($aOptions = null) {
        $aSubgroup = array(
            'values' => array(
                array('type' => '-12', 'data' => array('bmd_12' => $this->searchable_text)),
                array('sql' => array('D.status_id = 1')),
            ),
            'join' => 'AND',
        );
        $aCriteriaSet = array(
            'subgroup' => array($aSubgroup),
            'join' => 'AND',
        );
        $oUser = User::get($_SESSION['userID']);
        return KTSearchUtil::criteriaToQuery($aCriteriaSet, $oUser, 'ktcore.permissions.read', $aOptions);
    }
    
    function getDocumentCount() { 
        $aOptions = array(
            'select' => 'count(DISTINCT D.id) AS cnt',
        );
        $aQuery = $this->getQuery($aOptions);
        if (PEAR::isError($aQuery)) { return 0; }
        $iRet = DBUtil::getOneResultKey($aQuery, 'cnt');
        return $iRet;
    }
    
    
    // search needs some special stuff... this should probably get folded into a more complex criteria-driven thing
    // later.
    //
    // we also leak like ---- here, since getting the score is ... fiddly.  and expensive.
    function getDocuments($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        $this->sDocumentJoinClause = $sJoinClause;
        $this->aDocumentJoinParams = $aJoinParams;
        $aOptions = array(
            'select' => 'DISTINCT D.id AS id',
            'join' => array($sJoinClause, $aJoinParams),
        );
        $res = $this->getQuery($aOptions);
        if (PEAR::isError($res)) { return array(); }
        list($sQuery, $aParams) = $res;
        $sQuery .= " ORDER BY " . $sSortColumn . " " . $sSortOrder . " ";
        $sQuery .= " LIMIT ?, ?";

        $aParams[] = $iBatchStart;
        $aParams[] = $iBatchSize;
        
        $q = array($sQuery, $aParams);
        $res = DBUtil::getResultArray($q); 
        
        return $res;
    }
}

class TypeBrowseQuery extends SimpleSearchQuery {
    var $iDocType;
    
    function TypeBrowseQuery($oDocType) {
        $this->iDocType = $oDocType->getId();
    }

    function getQuery($aOptions = null) {
        $aSubgroup = array(
            'values' => array(
                array('type' => '-5', 'data' => array('bmd_5' => $this->iDocType)),
                array('sql' => array('D.status_id = 1')),
            ),
            'join' => 'AND',
        );
        $aCriteriaSet = array(
            'subgroup' => array($aSubgroup),
            'join' => 'AND',
        );
        $oUser = User::get($_SESSION['userID']);
        return KTSearchUtil::criteriaToQuery($aCriteriaSet, $oUser, 'ktcore.permissions.read', $aOptions);
    }
}

class ValueBrowseQuery extends SimpleSearchQuery {
    var $iFieldId;
    var $sValueName;
    
    function ValueBrowseQuery($oField, $oValue) {
        $this->iFieldId = $oField->getId();
        $this->sValueName = $oValue->getName();
    }

    function getQuery($aOptions = null) {
        $aSubgroup = array(
            'values' => array(
                array('type' => $this->iFieldId, 'data' => array('bmd' . $this->iFieldId => $this->sValueName)),
                array('sql' => array('D.status_id = 1')),
            ),
            'join' => 'AND',
        );
        $aCriteriaSet = array(
            'subgroup' => array($aSubgroup),
            'join' => 'AND',
        );
        $oUser = User::get($_SESSION['userID']);
        return KTSearchUtil::criteriaToQuery($aCriteriaSet, $oUser, 'ktcore.permissions.read', $aOptions);
    }
}

class BooleanSearchQuery extends PartialQuery {  
    // FIXME cache permission lookups, etc.
    var $datavars;

    function BooleanSearchQuery($datavars) { $this->datavars = $datavars; }
    
    function getFolderCount() { 
        // never any folders, given the current fulltext environ.
        return 0;
    }

    function getFolders($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        return array();
    }

    function getQuery($aOptions = null) {
        $oUser = User::get($_SESSION['userID']);
        return KTSearchUtil::criteriaToQuery($this->datavars, $oUser, 'ktcore.permissions.read', $aOptions);
    }
    
    function getDocumentCount() { 
        $aOptions = array(
            'select' => 'count(DISTINCT D.id) AS cnt',
        );
        $aQuery = $this->getQuery($aOptions);
        if (PEAR::isError($aQuery)) { return 0; }
        $iRet = DBUtil::getOneResultKey($aQuery, 'cnt');
        return $iRet;
    }
    
    
    // search needs some special stuff... this should probably get folded into a more complex criteria-driven thing
    // later.
    //
    // we also leak like ---- here, since getting the score is ... fiddly.  and expensive.
    function getDocuments($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        $this->sDocumentJoinClause = $sJoinClause;
        $this->aDocumentJoinParams = $aJoinParams;
        $aOptions = array(
            'select' => 'DISTINCT D.id AS id',
            'join' => array($sJoinClause, $aJoinParams),
        );
        $res = $this->getQuery($aOptions);
        if (PEAR::isError($res)) { return array(); }
        list($sQuery, $aParams) = $res;
        $sQuery .= " ORDER BY " . $sSortColumn . " " . $sSortOrder . " ";
        $sQuery .= " LIMIT ?, ?";

        $aParams[] = $iBatchStart;
        $aParams[] = $iBatchSize;
        
        $q = array($sQuery, $aParams);
        $res = DBUtil::getResultArray($q); 
        
        return $res;
    }
}

class FolderBrowseQuery extends BrowseQuery {
    function getDocumentCount() {
        return 0;
    }

    function getDocuments() {
        return array();
    }
}

class ArchivedBrowseQuery extends BrowseQuery {
    function _getDocumentQuery($aOptions = null) {
        $oUser = User::get($_SESSION['userID']);
        $res = KTSearchUtil::permissionToSQL($oUser, $this->sPermissionName);
        if (PEAR::isError($res)) {
            return $res;
        }
        list($sPermissionString, $aPermissionParams, $sPermissionJoin) = $res;
        $aPotentialWhere = array($sPermissionString, 'D.folder_id = ?', 'D.status_id = ' . ARCHIVED);
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

        $sSelect = KTUtil::arrayGet($aOptions, 'select', 'D.id');

        $sQuery = sprintf("SELECT %s FROM %s AS D
                LEFT JOIN %s AS DM ON D.metadata_version_id = DM.id
                LEFT JOIN %s AS DC ON DM.content_version_id = DC.id
                %s %s",
                $sSelect, KTUtil::getTableName("documents"),
                KTUtil::getTableName("document_metadata_version"),
                KTUtil::getTableName("document_content_version"),
                $sPermissionJoin, $sWhere);
        $aParams = array();
        $aParams = array_merge($aParams,  $aPermissionParams);
        $aParams[] = $this->folder_id;
        return array($sQuery, $aParams);
    }
}

?>
