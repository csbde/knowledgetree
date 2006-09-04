<?php

/**
 * $Id: columnregistry.inc.php 5492 2006-06-04 20:50:43Z bshuttle $
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . '/browse/columnentry.inc.php');

class KTColumnRegistry {
    var $columns = array();
    var $views = array();         // should be in here
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTColumnRegistry')) {
            $GLOBALS['_KT_PLUGIN']['oKTColumnRegistry'] =& new KTColumnRegistry;
        }
        return $GLOBALS['_KT_PLUGIN']['oKTColumnRegistry'];
    }
    // }}}

    function registerColumn($sName, $sNamespace, $sClass, $sFile) {
        $this->columns[$sNamespace] = array(
            'name' => $sName,
            'namespace' => $sNamespace,
            'class' => $sClass,
            'file' => $sFile        
        );
    }
    
    function getViewName($sNamespace) { return KTUtil::arrayGet($this->views, $sNamespace); }
    function getViews() { return $this->views; }
    function getColumns() { return $this->columns; }
    function registerView($sName, $sNamespace) { $this->views[$sNamespace] = $sName; }
    
    function getColumnInfo($sNamespace) {
        return  KTUtil::arrayGet($this->columns, $sNamespace, null);
    }        
    
    function getColumn($sNamespace) {
        $aInfo = $this->getColumnInfo($sNamespace);
        if (empty($aInfo)) {
            return PEAR::raiseError(sprintf(_kt("No such column: %s"), $sNamespace));
        } 
        
        require_once($aInfo['file']);

        return new $aInfo['class'];
    }
    
    function getColumnsForView($sViewNamespace) {
        $view_entry = KTUtil::arrayGet($this->views, $sViewNamespace);
        if (is_null($view_entry)) {
            return PEAR::raiseError(sprintf(_kt("No such view: %s"), $sViewNamespace));
        }    
        
        $view_column_entries = KTColumnEntry::getByView($sViewNamespace);
        if (PEAR::isError($view_column_entries)) { 
            return $view_column_entries; 
        }
        
        $view_columns = array();
        foreach ($view_column_entries as $oEntry) {
            $res = $this->getColumn($oEntry->getColumnNamespace());
            if (PEAR::isError($res)) { return $res; }
    
            $aOptions = $oEntry->getConfigArray();
            $aOptions['column_id'] = $oEntry->getId();            
            $aOptions['required_in_view'] = $oEntry->getRequired();             
            $res->setOptions($aOptions);
             
            $view_columns[] = $res;
        }
            
        return $view_columns;        
    }
}

?>
