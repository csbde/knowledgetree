<?php

/**
 * $Id$
 *
 * Base class for database-backed objects
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
 *
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib
 */

$_LCASECACHE = array();
$_OBJECTCACHE = array();
$_RANDOMCALL = 0;
$_STOPCACHING = array();

require_once(KT_LIB_DIR . '/cache/cache.inc.php');

DEFINE("EVIL_CACHE_GRIND", false);

class KTEntity {
    var $_bUsePearError = false;
    /** object primary key */
    var $iId = -1;

    function getId() {
        return $this->iId;
    }

    function setId($mValue) {
        $this->iId = $mValue;
    }

    function _cachedGroups() {
        return array('getlist');
    }

    function _innerClearCachedGroups($sClassName) {
        if ($GLOBALS['_STOPCACHING'][$sClassName]) {
            if ($GLOBALS['_STOPCACHING'][$sClassName] > 5) {
                return;
            }
        } else {
            $GLOBALS['_STOPCACHING'][$sClassName] = 0;
        }
        $group_func = array($sClassName, '_cachedGroups');
        if (is_callable($group_func)) {
            $groups = call_user_func($group_func);
        } else {
            $groups = array('getlist');
        }
        $groups[] = 'auto';
        $oCache =& KTCache::getSingleton();
        $aSuffixes = array(''); // , '_count', '_fullselect');
        foreach ($groups as $group_base) {
            global $default;
            foreach ($aSuffixes as $sSuffix) {
                $group = sprintf("%s/%s%s", $sClassName, $group_base, $sSuffix);
                $default->log->debug("Clearing cached group: $group");
                $oCache->clear($group);
            }
        }
        $GLOBALS['_STOPCACHING'][$sClassName]++;
        
    }

    function clearCachedGroups() {
        $sClass = get_class($this);
        $this->_innerClearCachedGroups($sClass);
    }

    /**
     * Create the current object in the database
     *
     * @return boolean on successful store, false otherwise and set $_SESSION["errorMessage"]
     *
     */
    function create() {
        if ($this->iId <= 0) {
            $id = DBUtil::autoInsert($this->_table(), $this->_fieldValues());
            if (PEAR::isError($id)) {
                if ($this->_bUsePearError === false) {
                    $_SESSION["errorMessage"] = $id->toString();
                    return false;
                } else {
                    return $id;
                }
            }
            $this->clearCachedGroups();
            $this->iId = $id;
            return true;
        }
        $_SESSION["errorMessage"] = "Can't create an object that already exists id = " . $this->iId . ' table = ' . $this->_table();
        return false;
    }

    function newCopy() {
        $sClass = get_class($this);
        $oObject =& new $sClass;
        foreach (array_keys($this->_aFieldToSelect) as $k) {
            $oObject->$k = $this->$k;
        }
        $oObject->iId = -1;
        $oObject->create();

        $iId = $oObject->iId;
        return KTEntityUtil::get($sClass, $iId);
    }

    /**
     * Update the values in the database table with the object's current values
     *
     * @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
     *
     */
    function update() {
        $group = sprintf("%s/%s", get_class($this), 'id');
        $oCache =& KTCache::getSingleton();
        $oCache->remove($group, $this->iId);
        $this->clearCachedGroups();

        if ($this->iId > 0) {
            $res = DBUtil::autoUpdate($this->_table(), $this->_fieldValues(), $this->iId);
            if (PEAR::isError($res)) {
                if ($this->_bUsePearError === false) {
                    $_SESSION['errorMessage'] = $res->toString();
                    return false;
                }
                return $res;
            }
            return true;
        }
        $_SESSION["errorMessage"] = "Can't update an object that isn't in the database";
        return false;
    }

    /**
    * Delete the current object from the database
    *
    * @return boolean true on successful deletion, false otherwise and set $_SESSION["errorMessage"]
    *
    */
    function delete() {
        $group = sprintf("%s/%s", get_class($this), 'id');
        $oCache =& KTCache::getSingleton();
        $oCache->remove($group, $this->iId);
        $this->clearCachedGroups();
        if ($this->iId >= 0) {
            $res = DBUtil::autoDelete($this->_table(), $this->iId);
            if (PEAR::isError($res)) {
                if ($this->_bUsePearError === false) {
                    $_SESSION['errorMessage'] = $res->toString();
                    return false;
                } else {
                    return $res;
                }
            }
            return true;
        }
        $_SESSION["errorMessage"] = "Can't delete an object that isn't in the database";;
        return false;
    }

    function _getSqlSelection() {
        $aRet = array();
        foreach ($this->_aFieldToSelect as $k => $v) {
            $aRet[] = $v;
        }
        return join(", ", $aRet);
    }

    function load($iId = null) {
        global $default;
        if (is_null($iId)) {
            if (is_null($this->iId)) {
                return PEAR::raiseError(_kt("No ID given"));
            }
            $iId = $this->iId;
        }
        $sClassName = get_class($this);
        // cache at lowest possible level.
        $oCache =& KTCache::getSingleton();
        $group = sprintf("%s/%s", $sClassName , 'id');
        list($bCached, $mCached) = $oCache->get($group, $iId);
        if ($bCached && EVIL_CACHE_GRIND) {
            $default->log->debug(sprintf(_kt("Found and testing object cache for class %s, id %d"), $sClassName, $iId));
            
            $table = $this->_table();
            $select = $this->_getSqlSelection();
            $sQuery = "SELECT $select FROM $table WHERE id = ?";
            $aParams = array($iId);
            
            $res = DBUtil::getResultArray(array($sQuery, $aParams));
            // we _have_ a cache:  error is a disaster
            if (PEAR::isError($res) || (count($res) === 0) || (count($res) > 1)) {
                $oCache->alertFailure($sClassName, array('<strong>SERIOUS FAILURE:</strong> real object is an error, cache claims "valid".'));
                return $res;
            }
            
            // now compare the sub-values.
            $aFailures = array();
            $sEntClass = get_class($this);
            
            foreach ($res as $sKey => $aVal) {
                if ($mCached[$sKey] != $res[$sKey]) {
                    $id = $aVal['id'];
                    foreach ($aVal as $sField => $sStored) {
                        if ($mCached[$sKey][$sField] != $sStored) {
                            $aFailures[] = sprintf("For %d field %s, stored value is %s, but cached value is %s", $id, $sField, $sStored, $mCached[$sKey][$sField]);
                        }
                    }
                    // $aFailures[] = $sKey;
                }
            }
            
            if (!empty($aFailures)) {
                $oCache->alertFailure($sClassName, $aFailures);
            }
            
            $res = $mCached;
        } else if ($bCached) {
            global $default;
            $default->log->debug(sprintf("Using object cache for class %s, id %d", $sClassName, $iId));
            $res = $mCached;
            /* */
        } else {        
            $default->log->debug(sprintf("No object cache for class %s, id %d", $sClassName, $iId));
            $table = $this->_table();
            $select = $this->_getSqlSelection();
            $sQuery = "SELECT $select FROM $table WHERE id = ?";
            $aParams = array($iId);
            
            $res = DBUtil::getResultArray(array($sQuery, $aParams));
            
            if (PEAR::isError($res)) {
               return $res;
            }
            if (count($res) === 0) {
                return PEAR::raiseError(_kt("No such ID: ") . $iId);
            }
            
            if (count($res) > 1) {
                return PEAR::raiseError(_kt("Multiple matches for ID: ") . $iId);
            }
            $oCache->set($group, $iId, $res);  // finally, cache if its "good".
        }
        $vk = array_flip($this->_aFieldToSelect);
        $aLoadInfo = array();
        foreach ($res[0] as $k => $v) {
            $aLoadInfo[$vk[$k]] = $v;
        }
        $res = $this->loadFromArray($aLoadInfo);
        if (PEAR::isError($res)) {
            return $res;
        }
    }

    function loadFromArray ($aOptions) {
        if (!is_array($aOptions)) {
            return PEAR::raiseError(_kt("Expected an array!"));
        }

        foreach ($aOptions as $sField => $sValue) {
            $sElement = $this->_getElementFromMethod($sField);
            if ($sElement === false) {
                return PEAR::raiseError(_kt('Setting a non-existent field: ') . $sField);
            }
            if (PEAR::isError($sElement)) {
                return $sElement;
            }
            $ret = $this->_set($sElement, $sValue);
            if (PEAR::isError($ret)) {
                return $ret;
            }
        }
        return true;
    }

    function _set (&$element, &$params) {
        $this->$element = $params;
        return array(true, true);
    }

    function &_getElementFromMethod ($sElement) {
        // The element is probably lower-case, for various reasons.  Get
        // the correct case from the aFieldToSelect dictionary's keys.
        //
        // If the element isn't in the case array, the method doesn't
        // exist.
        $sClassName = get_class($this);
        
        $aCache = KTUtil::arrayGet($GLOBALS['_LCASECACHE'], $sClassName);
        
        $sLowerElement = strtolower($sElement);
        if ($aCache) {
            $r = KTUtil::arrayGet($aCache['fieldnames'], $sLowerElement);
            if ($r) { return $r; }
        }
        
        $array = array_keys($this->_aFieldToSelect);

        if (!$aCache) {
            $case = array();
            foreach($array as $k) {
                $case[strtolower($k)] = $k;
            }
            foreach($case as $k => $v) {
               $case[substr($k, 1)] = $v;
            }
        } else {
            $case = $aCache['fieldnames'];
        }

        //var_dump($case);
        
        if (!$aCache) {
            $aCache = array();
            $aCache['fieldnames'] = $case;
        }
        
        $GLOBALS['_LCASECACHE'][$sClassName] =& $aCache;
        
        if (array_key_exists($sLowerElement, $case)) {
            return $case[$sLowerElement];
        }

        return PEAR::raiseError(_kt("No such element"));
    }

    function _fieldValues () {
        $aRet = array();

        foreach ($this->_aFieldToSelect as $k => $v) {
            if ($k === 'iId') {
                continue;
            }
            $aRet[$v] = $this->$k;
        }
        return $aRet;
    }

    function updateFromArray ($aOptions) {
        $ret = $this->load();
        if (PEAR::isError($ret)) {
            return $ret;
        }
        $ret = $this->loadFromArray($aOptions);
        if (PEAR::isError($ret)) {
            return $ret;
        }
        $ret = $this->update();
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if ($ret === false) {
            return PEAR::raiseError(sprintf(_kt("update for class %s failed"), $sClassName));
        }
        return true;
    }
    
    function _ktentityOptions() {
        return array(
            'orderby' => 'id',
        );
    }
}

class KTEntityUtil {
    function &getList2($sClassName, $aWhereClause = null, $aOptions = null) {
        $sTable = call_user_func(array($sClassName, "_table"));
        return KTEntityUtil::getList($sTable, $sClassName, $aWhereClause, $aOptions);
    }

    function &getList($sTable, $sClassName, $aWhereClause = null, $aOptions = null) {
        global $default;
        if (is_null($aOptions)) {
            $aOptions = array();
        }
        
        $aBaseOpts = call_user_func(array($sClassName, "_ktentityOptions"));
        $aOptions = KTUtil::meldOptions($aBaseOpts, $aOptions);
        
        $bIDs = false;
        $bIDs = KTUtil::arrayGet($aOptions, "ids", false);
        $sIDField = 'id';
        $cache = KTUtil::arrayGet($aOptions, 'cache', false);

        if (!is_array($aWhereClause)) {
            $aWhereClause = array($aWhereClause, array());
        }

        
        if (!$cache) { // && EVIL_CACHE_GRIND) {
            $cache = 'auto';
        }
        
        if (isset($GLOBALS['_STOPCACHING'][$sClassName])) {
            if ($GLOBALS['_STOPCACHING'][$sClassName] > 5) {
                $cache = false;
            }
        }

        if (isset($aOptions['orderby'])) {
            $sOrderBy = $aOptions['orderby'];
            if (!empty($aWhereClause[0])) {
                $aWhereClause[0] .= " ORDER BY " . $sOrderBy;
            } else {
                $aWhereClause[0] = "ORDER BY " . $sOrderBy;
            }
        }

        $sIDField = KTUtil::arrayGet($aOptions, "idfield", 'id');
        $aWhereClause[2] = $sIDField;

        if (isset($aOptions['limit'])) {
            if (isset($aOptions['offset'])) {
                $iOffset = $aOptions['offset'];
            } else {
                $iOffset = 0;
            }
            $iLimit = $aOptions['limit'];

            if ($iOffset) {
                $aWhereClause[0] .= " LIMIT ?, ?";
                $aWhereClause[1][] = $iOffset; 
                $aWhereClause[1][] = $iLimit; 
            } else {
                $aWhereClause[0] .= " LIMIT ?";
                $aWhereClause[1][] = $iLimit; 
            }
        }
        
        $fullselect = KTUtil::arrayGet($aOptions, 'fullselect', false);
        
        if ($cache) {
            if ($cache === 'auto') {
                $vals = serialize($aWhereClause);
            } else if (is_array($aWhereClause)) {
                $vals = serialize($aWhereClause[1]);
            } else {
                $vals = serialize($aWhereClause);
            }
            $default->log->debug(sprintf("Trying list cache for class %s, %s: %s", $sClassName, $cache, $vals));

            $oCache =& KTCache::getSingleton();
            $suffix = '';
            if ($fullselect) {
                $suffix = '_fullselect';
            }
            $group = sprintf("%s/%s%s", $sClassName, $cache, $suffix);
            list($bCached, $mCached) = $oCache->get($group, $vals);
        } else {
            $bCached = false;
        }
        
        if ($cache && !$bCached) {
            global $default;
            $default->log->debug(sprintf("No list cache for class %s, %s: %s", $sClassName, $cache, $vals));
        }
        if ($bCached && EVIL_CACHE_GRIND) {
            if (!empty($fullselect)) {
                $oObject = new $sClassName;
                $select = $oObject->_getSqlSelection();
                $sQuery = "SELECT $select FROM " . $sTable;/*ok*/
            } else {
                $sIDField = KTUtil::arrayGet($aOptions, "idfield", 'id');
                $sQuery = "SELECT $sIDField FROM " . $sTable;/*ok*/
            }
            $aParams = array();
            if (!empty($aWhereClause[0])) {
                if (is_string($aWhereClause)) {
                    if (substr($aWhereClause, 0, 5) != 'WHERE') {
                        if (substr($aWhereClause, 0, 5) != 'ORDER') {
                            $sQuery .= ' WHERE';
                        }
                    }
                    $sQuery .= ' ' . $aWhereClause;
                } else if (is_array($aWhereClause)) {
                    if (substr($aWhereClause[0], 0, 5) != 'WHERE') {
                        if (substr($aWhereClause[0], 0, 5) != 'ORDER') {
                            $sQuery .= ' WHERE';
                        }
                    }
                    $sQuery .= ' ' . $aWhereClause[0];
                    $aParams = $aWhereClause[1];
                } else {
                    return new PEAR_Error('Weird WhereClause passed');
                }
            }

            if (!empty($fullselect)) {
                $aIDs = DBUtil::getResultArray(array($sQuery, $aParams));
            } else {
                $aIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), $sIDField);
            }
            // compare mcached == $aIds
            
            if ($aIDs != $mCached) {
                /*
                print "==================\n\n\n\n\n";
                var_dump($aIDs);
                print "------\n";
                var_dump($mCached);
                */
                $oCache->alertFailure($group, array('Stored IDS != Cache IDS'));
            }
        } else if ($bCached) {
            $default->log->debug(sprintf("Using object cache for class %s, %s: %s", $sClassName, $cache, $vals));
            $aIDs = $mCached;
        /* */
        } else {
            if (!empty($fullselect)) {
                $oObject = new $sClassName;
                $select = $oObject->_getSqlSelection();
                $sQuery = "SELECT $select FROM " . $sTable;/*ok*/
            } else {
                $sIDField = KTUtil::arrayGet($aOptions, "idfield", 'id');
                $sQuery = "SELECT $sIDField FROM " . $sTable;/*ok*/
            }
            $aParams = array();
            if (!is_null($aWhereClause[0])) {
                if (is_string($aWhereClause)) {
                    if (substr($aWhereClause, 0, 5) != 'WHERE') {
                        if (substr($aWhereClause, 0, 5) != 'ORDER') {
                            $sQuery .= ' WHERE';
                        }
                    }
                    $sQuery .= ' ' . $aWhereClause;
                } else if (is_array($aWhereClause)) {
                    if (substr($aWhereClause[0], 0, 5) != 'WHERE') {
                        if (substr($aWhereClause[0], 0, 5) != 'ORDER') {
                            $sQuery .= ' WHERE';
                        }
                    }
                    $sQuery .= ' ' . $aWhereClause[0];
                    $aParams = $aWhereClause[1];
                } else {
                    return new PEAR_Error('Weird WhereClause passed');
                }
            }

            if (!empty($fullselect)) {
                $aIDs = DBUtil::getResultArray(array($sQuery, $aParams));
            } else {
                $aIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), $sIDField);
            }
        }

        if (PEAR::isError($aIDs)) {
            return $aIDs;
        }

        if ($cache && !$bCached) {
            $default->log->debug(sprintf("Setting object cache for class %s, %s, %s", $sClassName, $cache, $vals));
            $oCache->set($group, $vals, $aIDs);
        }

        if ($bIDs === true) {
            return $aIDs;
        }

        if (!empty($fullselect)) {
            $aRet =& KTEntityUtil::loadFromArrayMulti($sClassName, $aIDs);
            return $aRet;
        }
        $aRet = array();
        foreach ($aIDs as $iId) {
            $aRet[] = call_user_func(array($sClassName, 'get'), $iId);
        }
        return $aRet;
    }

    function &createFromArray ($sClassName, $aOptions) {
        $oObject = new $sClassName;
        $ret = $oObject->loadFromArray($aOptions);
        if (PEAR::isError($ret)) {
            return $ret;
        }
        $ret = $oObject->create();
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if ($ret === false) {
            return PEAR::raiseError(sprintf(_kt("create for class %s failed"), $sClassName));
        }
        $meth = array($sClassName, 'get');
        return call_user_func($meth, $oObject->getId());
    }

    function updateFromArray ($sClassName, $iId, $aOptions) {
        $oObject = new $ClassName;
        $ret = $oObject->load($iId);
        if (PEAR::isError($ret)) {
            return $ret;
        }
        $ret = $this->loadFromArray($aOptions);
        if (PEAR::isError($ret)) {
            return $ret;
        }
        $ret = $this->update();
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if ($ret === false) {
            return PEAR::raiseError(sprintf(_kt("update for class %s failed"), $sClassName));
        }
        return true;
    }

    function &loadFromArray ($sClassName, $aArray, $aOptions = null) {
        $oObject =& new $ClassName;
        $ret = $oObject->loadFromArray($aArray);
        if (PEAR::isError($ret)) {
            return $ret;
        }
        return $oObject;
    }

    function &loadFromArrayMulti($sClassName, $aMultiArray, $aOptions = null) {
        $aRet = array();
        foreach ($aMultiArray as $aArray) {
            $oObject =& new $sClassName;
            $ret = $oObject->loadFromArray($aArray);
            if (PEAR::isError($ret)) {
                return $ret;
            }
            $aRet[] = $oObject;
        }
        return $aRet;
    }

    function &get($sClassName, $iId) {
        if (!is_numeric($iId)) {
            return PEAR::raiseError(_kt('Non-numeric identifier'));
        }
        $iId = (int)$iId;
        /* */
        $sProxyClass = KTEntityUtil::_getProxyClass($sClassName);
        $oObject =& new $sProxyClass($iId);
        $res = $oObject->getId();
        if (PEAR::isError($res)) {
            return $res;
        }
        return $oObject;
        /* */
        // XXX Object cache currently causes hard-to-trace inconsistencies in data. 
        // $oObject =& KTUtil::arrayGet($GLOBALS['_OBJECTCACHE'][$sClassName], $iId);
        // if ($oObject) { return $oObject; }
        $oObject =& new $sClassName;
        $res = $oObject->load($iId);
        if (PEAR::isError($res)) {
            return $res;
        }
        // XXX Object cache currently causes hard-to-trace inconsistencies in data. 
        //$GLOBALS['_OBJECTCACHE'][$sClassName][$iId] =& $oObject;
        return $oObject;
        /* */
    }

    function _getProxyClass($sClassName) {
        $sProxyClassName = sprintf("%sProxy", $sClassName);
        if (!class_exists($sProxyClassName)) {
            KTEntityUtil::_proxyCreate($sClassName, $sProxyClassName);
        }
        return $sProxyClassName;
    }

    function _proxyCreate($sClassName, $sProxyClassName) {
        /*
        $code = KTEntityUtil::_proxyBuild($sClassName, $sProxyClassName);
        return eval($code);
        /* */

        $oKTConfig =& KTConfig::getSingleton();
        $sDirectory = $oKTConfig->get('cache/proxyCacheDirectory');
        if (!file_exists($sDirectory)) {
            $res = @mkdir($sDirectory);
        }
        $sRunningUser = KTUtil::running_user();
        if ($sRunningUser) {
            $sDirectory = sprintf("%s/%s", $sDirectory, $sRunningUser);
        }
        if (!file_exists($sDirectory)) {
            $res = @mkdir($sDirectory);
        }
        $sFilename = sprintf("%s/%s.inc.php", $sDirectory, $sProxyClassName);
        if (file_exists($sFilename)) {
            require_once($sFilename);
            return;
        }

        $oCache =& KTCache::getSingleton();
        list($bCached, $mCached) = $oCache->get('ktentity/proxycreate', $sClassName);
        if ($bCached) {
            $code = $mCached;
        } else {
            $code = KTEntityUtil::_proxyBuild($sClassName, $sProxyClassName);
            $res = @file_put_contents($sFilename, "<?php\n$code");
            if ($res) {
                require_once($sFilename);
                return;
            }
            $oCache->set('ktentity/proxycreate', $sClassName, $code);
        }
        eval($code);
    }
    function _proxyBuild($sClassName, $sProxyClassName) {
        // var_dump("Building proxy for $sClassName");
        $methods = get_class_methods($sClassName);
        $allcode = array();
        foreach ($methods as $sMethod) {
            $code = sprintf('function %s() { $aArgs = func_get_args(); return $this->_callOnObject("%s", $aArgs); }%s', $sMethod, $sMethod, "\n");
            $allcode[] = $code;
        }

        $allcode[] = sprintf('function &_fetch() {
        if (!empty($GLOBALS["_OBJECTCACHE"]["%s"][$this->iId])) {
            $oObject =& $GLOBALS["_OBJECTCACHE"]["%s"][$this->iId];
            return $oObject;
        }
        $oObject =& new %s;
        $res = $oObject->load($this->iId);
        if (PEAR::isError($res)) {
            return $res;
        }
        $GLOBALS["_OBJECTCACHE"]["%s"][$this->iId] =& $oObject;
        return $oObject;
    }
        ', $sClassName, $sClassName, $sClassName, $sClassName);

        $allcode[] = sprintf('function _save(&$oObject) {
        $GLOBALS["_OBJECTCACHE"]["%s"][$this->iId] =& $oObject;
    }
        ', $sClassName);

        $allcode[] = 'function &_callOnObject($sName, $aArgs) {
        $oObject =& $this->_fetch();
        if (PEAR::isError($oObject)) {
            return $oObject;
        }
        /* */
        $res = call_user_func_array(array(&$oObject, $sName), $aArgs);
        $this->_save($oObject);
        return $res;
        /* */

        /* */
        $aExecArgs = array();
        $exec = \'$res =& $oObject->$sName(\';
        foreach (array_keys($aArgs) as $iKey) {
            $aExecArgs[] = \'$aArgs[\' . $iKey . \']\';
        }
        $exec .= join(", ", $aExecArgs);
        $exec .= \');\';
        eval($exec);
        $this->_save($oObject);
        return $res;
        /* */
    }
        ';

        $allcode[] = sprintf('function %s ($iId) { $this->iId = $iId; }' . "\n", $sProxyClassName);

        $gen = sprintf("class %s extends %s {\n", $sProxyClassName, $sClassName);
        $gen .= "    " . join("\n    ", $allcode) . "\n";
        $gen .= "}";

        return $gen;
    }

    function _getBy_equals($sField, $aValue) {
        $aParam = $aValue['value'];
        if (!is_array($aParam)) {
            return array("$sField = ?", array($aParam));
        }
        $sParam = DBUtil::paramArray($aParam);
        return array("$sField IN ($sParam)", array($aParam));
    }

    function _getBy_nequals($sField, $aValue) {
        $aParam = $aValue['value'];
        if (!is_array($aParam)) {
            return array("$sField \!= ?", array($aParam));
        }
        $sParam = DBUtil::paramArray($aParam);
        return array("$sField NOT IN ($sParam)", array($aParam));
    }

    function _getBy_after($sField, $aValue) {
        $aParam = $aValue['value'];
        return array("$sField > ?", array($aParam));
    }

    function _getBy_before($sField, $aValue) {
        $aParam = $aValue['value'];
        return array("$sField < ?", array($aParam));
    }

    function _getBy_between($sField, $aValue) {
        $aParam = $aValue['value'];
        return array("$sField BETWEEN ? AND ?", $aParam);
    }

    function &getBy($sClassName, $aField, $mValue, $aOptions = null) {
        $bMulti = KTUtil::arrayGet($aOptions, 'multi', false);
        if ($bMulti) {
            $bNoneOk = true;
        } else {
            $bNoneOk = false;
        }
        $bNoneOk = KTUtil::arrayGet($aOptions, 'noneok', $bNoneOk);
        if (is_string($aField)) {
            $aField = array($aField);
            $mValue = array($mValue);
        }

        $aWhere = array();
        foreach ($aField as $k => $sField) {
            $mThisValue = $mValue[$k];
            if (!is_array($mThisValue)) {
                $mThisValue = array('type' => 'equals', 'value' => $mThisValue);
            }
            $sField = KTUtil::arrayGet($mThisValue, 'field', $sField);

            $sType = KTUtil::arrayGet($mThisValue, 'type');
            if (empty($sType)) {
                $mThisValue = array('type' => 'equals', 'value' => $mThisValue);
                $sType = 'equals';
            }

            $sFunc = array('KTEntityUtil', sprintf('_getby_%s', $sType));
            if (!is_callable($sFunc)) {
                return PEAR::raiseError(_kt('Unknown comparison type given: ') . $sType);
            }
            $aWhere[] = call_user_func($sFunc, $sField, $mThisValue);
        }
        $sWhereClause = KTUtil::whereToString($aWhere);

        $aObjects =& KTEntityUtil::getList2($sClassName, $sWhereClause, $aOptions);
        if (PEAR::isError($aObjects)) {
            return $aObjects;
        }
        if ($bMulti === false) {
            if (count($aObjects) === 0) {
                if ($bNoneOk) {
                    return null;
                }
                return new KTEntityNoObjects();
            }
            if (count($aObjects) > 1) {
                return PEAR::raiseError(_kt("Multiple objects returned"));
            }
            return $aObjects[0];
        } else {
            return $aObjects;
        }
    }

    function &getByDict($sClassName, $aDict, $aOptions = null) {
        return KTEntityUtil::getBy($sClassName, array_keys($aDict), array_values($aDict), $aOptions);
    }

    function clearAllCaches($sClassName) {
        KTEntity::_innerClearCachedGroups($sClassName);
        $oCache =& KTCache::getSingleton();
        $oCache->clear(sprintf("%s/id", $sClassName));
        unset($GLOBALS['_OBJECTCACHE'][$sClassName]);
    }
    
}

class KTEntityNoObjects extends PEAR_Error {
}

?>
