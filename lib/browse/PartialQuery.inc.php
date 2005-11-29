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

    function BrowseQuery($iFolderId) { $this->folder_id = $iFolderId; }
    
    function _getDocumentQuery($aOptions = null) {
        $oUser = User::get($_SESSION['userID']);
        list($sPermissionString, $aPermissionParams, $sPermissionJoin) = KTSearchUtil::permissionToSQL($oUser, $this->sPermissionName);

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

        $sQuery = "SELECT $sSelect FROM " . KTUtil::getTableName("documents") . " AS D $sPermissionJoin $sWhere ";
        $aParams = array();
        $aParams = array_merge($aParams,  $aPermissionParams);
        $aParams[] = $this->folder_id;
        return array($sQuery, $aParams);
    }

    function _getFolderQuery($aOptions = null) {
        $oUser = User::get($_SESSION['userID']);
        list($sPermissionString, $aPermissionParams, $sPermissionJoin) = KTSearchUtil::permissionToSQL($oUser, $this->sPermissionName, "F");

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
        $iRet = DBUtil::getOneResultKey($aQuery, 'cnt');
        return $iRet;
    }
    
    function getDocumentCount() { 
        $aOptions = array(
            'select' => 'count(D.id) AS cnt',
        );
        $aQuery = $this->_getDocumentQuery($aOptions);
        $iRet = DBUtil::getOneResultKey($aQuery, 'cnt');
        return $iRet;
    }
    
    function getFolders($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        list($sQuery, $aParams) = $this->_getFolderQuery();
        $sQuery .= " ORDER BY " . $sSortColumn . " " . $sSortOrder . " ";

        $sQuery .= " LIMIT ?, ?";
        $aParams[] = $iBatchStart;
        $aParams[] = $iBatchSize;
    
        $q = array($sQuery, $aParams);
        
        $res = DBUtil::getResultArray($q); 
        
        return $res;
    }
    
    function getDocuments($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        list($sQuery, $aParams) = $this->_getDocumentQuery();
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
            'select' => 'count(D.id) AS cnt',
        );
        $aQuery = $this->getQuery($aOptions);
        $iRet = DBUtil::getOneResultKey($aQuery, 'cnt');
        return $iRet;
    }
    
    
    // search needs some special stuff... this should probably get folded into a more complex criteria-driven thing
    // later.
    //
    // we also leak like ---- here, since getting the score is ... fiddly.  and expensive.
    function getDocuments($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        $aOptions = array(
            'select' => 'D.id AS id',
        );
        list($sQuery, $aParams) = $this->getQuery($aOptions);

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

?>
