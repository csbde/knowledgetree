<?php

/**
 * $Id: columnregistry.inc.php 5492 2006-06-04 20:50:43Z bshuttle $
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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
        $aInfo = KTUtil::arrayGet($this->columns, $sNamespace, null);
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
