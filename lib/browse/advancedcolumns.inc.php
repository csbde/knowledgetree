<?php

require_once(KT_LIB_DIR . "/util/sanitize.inc");

// more advanced, intelligent columns.

class AdvancedColumn {
    // the internal tracking name 
    var $namespace = 'ktcore.columns.base';

    var $label = '';
    var $sort_on = false;
    var $sort_direction = 'asc';
    var $sortable = false;
    var $return_url = null;
    var $aOptions;
    
    // no params - important
    function AdvancedColumn() {
        $this->label = _kt('Base Column');
    }    
    
    // meld the internal vars with those from the options.
    function setOptions($aOptions = null) { 
        $this->aOptions = kt_array_merge($this->aOptions, $aOptions);
        $this->sortable = KTUtil::arrayGet($this->aOptions, 'sortable', $this->sortable);       
        $this->return_url = KTUtil::arrayGet($this->aOptions, 'return_url', $this->return_url);       
        $this->sort_on = KTUtil::arrayGet($this->aOptions, 'sort_on', $this->sort_on);       
        $this->sort_direction = KTUtil::arrayGet($this->aOptions, 'sort_on', $this->sort_direction);              
    }

    function getOptions() {
        return $this->aOptions;
    }

    /*
       return the html for the header.  
       
        "return url" : URL to return to (or null to use addQueryStringSelf)       
    */
    function renderHeader() { 
        // short-circuit
        if (empty($this->label)) { return ''; }    
        // for safety
        $label = htmlentities($this->label, ENT_NOQUOTES, 'UTF-8');

        // without sorthing to sort on, don't bother. 
        if (empty($this->namespace)) {
            $this->sortable = false;        // if we haven't set which column we're sorted by, do nothing.    
        }        
        
        // no sorting, no link
        if (!$this->sortable) {
            return $label;
        }
        
        // merge the sorting options into the header.        
        $sort_order = $this->sort_direction == 'asc' ? 'desc' : 'asc';
        $qs = sprintf('sort_on=%s&sort_order=%s', $this->namespace, $sort_order);  
        if (is_null($this->return_url)) {
            $url = KTUtil::addQueryStringSelf($qs);
        } else {
            $url = KTUtil::addQueryString($this->return_url, $qs);
        }

        return sprintf('<a href="%s">%s</a>', $url, $label);        
    }
    
    function renderData($aDataRow) { 
       if ($aDataRow['type'] == 'folder') {
           return $this->name . ': '. $aDataRow['folder']->getName();            
        } else {
           return $this->name . ': '. $aDataRow['document']->getName(); 
        }
    }
    
    function setSortedOn($bIsSortedOn) { $this->sort_on = $bIsSortedOn; }
    function getSortedOn() { return $this->sort_on; }
    function setSortDirection($sSortDirection) { $this->sort_direction = $sSortDirection; }
    function getSortDirection() { return $this->sort_direction; }
    
    function addToFolderQuery() { return array(null, null, null); }
    function addToDocumentQuery() { return array(null, null, null); }
    
    function getName() {
        return sanitizeForSQLtoHTML($this->label);
    }
    
    function getEntryId() {
        return KTUtil::arrayGet($this->aOptions, 'column_id', null);
    }
    
    function getRequiredInView() {
        return KTUtil::arrayGet($this->aOptions, 'required_in_view', null);
    }    
    
}

?>