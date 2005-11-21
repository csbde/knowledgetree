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

    function BrowseQuery($iFolderId) { $this->folder_id = $iFolderId; }
    
    
    function getFolderCount() { 
        // FIXME add permission checks here
        $sQuery = "SELECT count(id) AS c FROM " . KTUtil::getTableName("folders") . " WHERE parent_id = ? ";
        $aParams = array($this->folder_id);

        return  DBUtil::getOneResultKey(array($sQuery, $aParams), 'c');
    }
    
    function getDocumentCount() { 
        // FIXME add permission checks here
        $sQuery = "SELECT count(id) AS c FROM " . KTUtil::getTableName("documents") . " WHERE folder_id = ? AND D.status_id = 1 ";
        $aParams = array($this->folder_id);
        
        return DBUtil::getOneResultKey(array($sQuery, $aParams), 'c'); // FIXME is this right? 
    }
    
    function getFolders($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        // FIXME add permission checks here
        $aParams = array();
        $aJoinParams = array($aJoinParams);
        
        $sQuery = "SELECT id FROM " . KTUtil::getTableName("folders") . " AS F WHERE parent_id = ? ";
        $aParams[] = $this->folder_id;
        
        if ($sJoinClause !== null) {
            $sQuery .= $sJoinClause;
            foreach ($aJoinParams as $param) {
                $aParams[] = $param;
            } // FIXME use merge...
        }        
    
        $sQuery .= " ORDER BY " . $sSortColumn . " " . $sSortOrder . " ";

        $sQuery .= " LIMIT ?, ?";
        $aParams[] = $iBatchStart;
        $aParams[] = $iBatchSize;
    
        $q = array($sQuery, $aParams);
        
        $res = DBUtil::getResultArray($q); 
        
        return $res;
    }
    
    function getDocuments($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        // FIXME add permission checks here
        $aParams = array(); // main parameter array.
        $aJoinParams = array($aJoinParams);
        
        $sQuery = "SELECT id FROM " . KTUtil::getTableName("documents") . " AS D WHERE folder_id = ? AND D.status_id = 1 ";
        $aParams = array($this->folder_id);
        
        if ($sJoinClause !== null) {
            $sQuery .= $sJoinClause;
            foreach ($aJoinParams as $param) {
                $aParams[] = $param;
            } // FIXME use merge...
        }        

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
    
    function getDocumentCount() { 
        // FIXME add permission checks here
        // FIXME do not refer directly.
        // FIXME is this even _vaguely_ portable.
        $sQuery = "SELECT count(document_id) AS c FROM document_text WHERE MATCH (document_text) AGAINST (?)";
        $aParams = array($this->searchable_text);
        
        return DBUtil::getOneResultKey(array($sQuery, $aParams), 'c'); 
    }
    
    function getFolders($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        return array();
    }
    
    
    // search needs some special stuff... this should probably get folded into a more complex criteria-driven thing
    // later.
    //
    // we also leak like ---- here, since getting the score is ... fiddly.  and expensive.
    function getDocuments($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null) { 
        // FIXME add permission checks here
        $aParams = array(); // main parameter array.
        $aJoinParams = array($aJoinParams);
        
        $sQuery = "SELECT D.id, MATCH (DT.document_text) AGAINST (?) as score FROM " . KTUtil::getTableName("documents") . " AS D ";
        $aParams[] = $this->searchable_text;
        
        $sQuery .= " LEFT JOIN document_text AS DT ON (DT.document_id = D.id) ";
        
        if ($sJoinClause !== null) {
            $sQuery .= $sJoinClause;
            foreach ($aJoinParams as $param) { $aParams[] = $param; } // FIXME use merge...
        }        
        $sQuery .= " WHERE MATCH(DT.document_text) AGAINST (?) ";
        $aParams[] = $this->searchable_text;
        
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
