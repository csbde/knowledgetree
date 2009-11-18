<?php
/**
 * $Id$
 *
 * Base class for database-backed objects
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
                if (KTLOG_CACHE) $default->log->debug("Clearing cached group: $group");
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
        $oObject =new $sClass;
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
        if ($oCache->get($group, $this->iId) != array(false, false)) {
        	$oCache->remove($group, $this->iId);
        }

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
    function delete($dereference = null) {
        $group = sprintf("%s/%s", get_class($this), 'id');
        $oCache =& KTCache::getSingleton();
        $oCache->remove($group, $this->iId);
        $this->clearCachedGroups();
        if ($this->iId >= 0) {
            if($dereference){
            	$res = DBUtil::deReference($this->_table(), $this->iId);
            }else{
            	$res = DBUtil::autoDelete($this->_table(), $this->iId);
            }
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
            if (KTLOG_CACHE) $default->log->debug(sprintf(_kt("Found and testing object cache for class %s, id %d"), $sClassName, $iId));

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
            if (KTLOG_CACHE) $default->log->debug(sprintf("Using object cache for class %s, id %d", $sClassName, $iId));
            $res = $mCached;
            /* */
        } else {
            if (KTLOG_CACHE) $default->log->debug(sprintf("No object cache for class %s, id %d", $sClassName, $iId));
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
        $array2 = array_flip($this->_aFieldToSelect);

        if (!$aCache) {
            $case = array();
            foreach($array as $k) {
                $case[strtolower($k)] = $k;
            }
            foreach($case as $k => $v) {
               $case[substr($k, 1)] = $v;
            }
            foreach($array2 as $k => $v) {
                $case[strtolower($k)] = $v;
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

    /**
     * Core function for KTEntityUtil, gets a list of all the objects according to the given where clause
     *
     * @param string $sTable
     * @param string $sClassName
     * @param array|string $aWhereClause
     * @param array $aOptions
     * @return array
     */
    function &getList($sTable, $sClassName, $aWhereClause = null, $aOptions = null) {
        global $default;

        // Force the where clause to be an array
        if (!is_array($aWhereClause)) {
            $aWhereClause = array($aWhereClause, array());
        }

        // Force the options to be an array
        if (is_null($aOptions)) {
            $aOptions = array();
        }

        // Merge in the standard options for the class
        $aBaseOpts = call_user_func(array($sClassName, "_ktentityOptions"));
        if(!is_array($aBaseOpts)){
            $aBaseOpts = array();
        }
        $aOptions = array_merge($aBaseOpts, $aOptions);

        /* ***  Check for all option values  *** */

        // Check if only the id's have been requested
        $bIDs = isset($aOptions['ids']) ? $aOptions['ids'] : false;

        // Check if the id field differs from the standard
        $sIDField = isset($aOptions['idfield']) ? $aOptions['idfield'] : 'id';

        // Check for an order by clause
        $sOrderBy = isset($aOptions['orderby']) ? $aOptions['orderby'] : false;

        // Check for a limit and offset
        $iLimit = isset($aOptions['limit']) ? $aOptions['limit'] : false;
        $iOffset = isset($aOptions['offset']) ? $aOptions['offset'] : false;

        // Check for the cache value
        $cache = isset($aOptions['cache']) ? $aOptions['cache'] : 'getlist';

        /* *** Construct the query *** */

        $sQuery = '';
        if($bIDs !== false){
            $sQuery = "SELECT $sIDField FROM $sTable";
        }else{
            $oObject = new $sClassName;
            $select = $oObject->_getSqlSelection();
            $sQuery = "SELECT {$select} FROM $sTable";
        }

        // Append the where clause
        $sWhere = '';
        $aParams = array();
        if(isset($aWhereClause[0]) && !empty($aWhereClause[0])){
            // check whether the WHERE or ORDER has already been included in the query string
            $check = substr($aWhereClause[0], 0, 5);

            if($check != 'WHERE' && $check != 'ORDER'){
                $sWhere = 'WHERE ';
            }
            $sWhere .= $aWhereClause[0];
            $aParams = isset($aWhereClause[1]) && !empty($aWhereClause[1]) ? $aWhereClause[1] : array();

            $sQuery .= " $sWhere";
        }

        // Append the order by
        if($sOrderBy != false){
            $sQuery .= " ORDER BY $sOrderBy";
        }

        // Append a limit and offset
        if($iLimit != false){
            $sLimit = '';
            if($iOffset != false){
                $sLimit .= "$iOffset, ";
            }
            $sLimit .= $iLimit;

            $sQuery .= " LIMIT $sLimit";
        }

        /* *** Check the cache *** */

        $oCache = KTCache::getSingleton();
        $vals = serialize(array($sQuery, $aParams));
        $group = $sClassName.'/'.$cache;

        list($bCached, $mCached) = $oCache->get($group, $vals);


        /* *** Execute the query *** */

        // If only id's are requested, return them
        if($bIDs){
            $aIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), $sIDField);
            return $aIDs;
        }

        // Get the object data and load into objects
        $results = DBUtil::getResultArray(array($sQuery, $aParams));
        $aObjects = KTEntityUtil::loadFromArrayMulti($sClassName, $results);
        return $aObjects;
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
        $oObject =new $ClassName;
        $ret = $oObject->loadFromArray($aArray);
        if (PEAR::isError($ret)) {
            return $ret;
        }
        return $oObject;
    }

    function &loadFromArrayMulti($sClassName, $aMultiArray, $aOptions = null) {
        $aRet = array();
        $config =& KTConfig::getSingleton();
        //$useProxy = $config->get('cache/proxyCacheEnabled');
        foreach ($aMultiArray as $aArray) {

            //if($useProxy){
                $iId = (int)$aArray['id'];
                if(empty($iId)){
                    $iId = null;
                }
                $sProxyClass = KTEntityUtil::_getProxyClass($sClassName);
                $oObject =new $sProxyClass($iId, $aArray);
                $res = $oObject->getId();
                if (PEAR::isError($res)) {
                    return $res;
                }
                $aRet[] = $oObject;
                continue;
            //}

            $oObject =new $sClassName;
            $ret = $oObject->loadFromArray($aArray);
            if (PEAR::isError($ret)) {
                return $ret;
            }
            $aRet[] = $oObject;
        }
        return $aRet;
    }

    static function &get($sClassName, $iId) {
        if (!is_numeric($iId)) {
            return PEAR::raiseError(_kt('Non-numeric identifier'));
        }
        $iId = (int)$iId;
        // Use a proxy class if enabled
        $config =& KTConfig::getSingleton();
        //if($config->get('cache/proxyCacheEnabled')){
            $sProxyClass = KTEntityUtil::_getProxyClass($sClassName);
            $oObject =new $sProxyClass($iId);
            $res = $oObject->getId();
            if (PEAR::isError($res)) {
                return $res;
            }
            return $oObject;
        //}
        // XXX Object cache currently causes hard-to-trace inconsistencies in data.
        // $oObject =& KTUtil::arrayGet($GLOBALS['_OBJECTCACHE'][$sClassName], $iId);
        // if ($oObject) { return $oObject; }
        $oObject =new $sClassName;
        $res = $oObject->load($iId);
        if (PEAR::isError($res)) {
            return $res;
        }
        // XXX Object cache currently causes hard-to-trace inconsistencies in data.
        //$GLOBALS['_OBJECTCACHE'][$sClassName][$iId] =& $oObject;
        return $oObject;
        /* */
    }

    static function _getProxyClass($sClassName) {
        $sProxyClassName = sprintf("%sProxy", $sClassName);
        if (!class_exists($sProxyClassName)) {
            KTEntityUtil::_proxyCreate($sClassName, $sProxyClassName);
        }
        return $sProxyClassName;
    }

    static function _proxyCreate($sClassName, $sProxyClassName) {
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
        $allcode[] = sprintf('var $cacheGlobal = null;%s', "\n");
        $allcode[] = sprintf('var $aFieldArr = null;%s', "\n");

        foreach ($methods as $sMethod) {
        	if(substr($sMethod, 0, 2) == '__') {
				/* We don't want magic functions in our proxy classes */
				continue;
			}
            if ($sMethod == 'getid') {
                $code = sprintf('function getId() { return $this->iId; }');
    		} else if ($sMethod == '_table' || $sMethod == 'update' ) {
                $code = sprintf('function %s() { return parent::%s(); }', $sMethod, $sMethod);
    		} else if ($sMethod == 'get') {
    			$code = sprintf('function get($oId) { return $this->get(%s, $oid); }', $sClassName);
    		} else {
                $code = sprintf('function %s() { $aArgs = func_get_args(); return $this->_callOnObject("%s", $aArgs); }%s', $sMethod, $sMethod, "\n");
            }
		  $allcode[] = $code;
        }

        $allcode[] = sprintf('function &_fetch() {
            if (!empty($this->cacheGlobal[$this->iId])) {
                $oObject =& $this->cacheGlobal[$this->iId];
                return $oObject;
            }
            $oObject =new %s;
            if(!empty($this->aFieldArr)){
                $res = $oObject->loadFromArray($this->aFieldArr);
            }else{
                $res = $oObject->load($this->iId);
            }
            if (PEAR::isError($res)) {
                return $res;
            }
            $this->cacheGlobal[$this->iId] =& $oObject;
            return $oObject;
        }', $sClassName, $sClassName, $sClassName, $sClassName, $sClassName);

        $allcode[] = sprintf('function _save(&$oObject) {
            $this->cacheGlobal[$this->iId] =& $oObject;
        }', $sClassName);

        $allcode[] = 'function &_callOnObject($sName, $aArgs) {
            $oObject =& $this->_fetch();
            if (PEAR::isError($oObject)) {
                return $oObject;
            }
            /* */
            $res = call_user_func_array(array(&$oObject, $sName), $aArgs);
            $this->cacheGlobal[$this->iId] =& $oObject;
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
            $this->cacheGlobal[$this->iId] =& $oObject;
            return $res;
            /* */
        }';

        $allcode[] = sprintf('function %s ($iId, $aInfo = null) { $this->iId = $iId; $this->aFieldArr = $aInfo; $this->cacheGlobal =& $GLOBALS["_OBJECTCACHE"]["%s"]; }' . "\n", $sProxyClassName, $sClassName);

        $gen = sprintf("class %s extends %s {\n", $sProxyClassName, $sClassName);
        $gen .= "    " . join("\n    ", $allcode) . "\n";
        $gen .= "}";

        return $gen;
    }

    static function _getBy_equals($sField, $aValue) {
        $aParam = $aValue['value'];
        if (!is_array($aParam)) {
            return array("$sField = ?", array($aParam));
        }
        $sParam = DBUtil::paramArray($aParam);
        return array("$sField IN ($sParam)", array($aParam));
    }

    static function _getBy_nequals($sField, $aValue) {
        $aParam = $aValue['value'];
        if (!is_array($aParam)) {
            return array("$sField \!= ?", array($aParam));
        }
        $sParam = DBUtil::paramArray($aParam);
        return array("$sField NOT IN ($sParam)", array($aParam));
    }

    static function _getBy_after($sField, $aValue) {
        $aParam = $aValue['value'];
        return array("$sField > ?", array($aParam));
    }

    static function _getBy_before($sField, $aValue) {
        $aParam = $aValue['value'];
        return array("$sField < ?", array($aParam));
    }

    static function _getBy_between($sField, $aValue) {
        $aParam = $aValue['value'];
        return array("$sField BETWEEN ? AND ?", $aParam);
    }

    static function &getBy($sClassName, $aField, $mValue, $aOptions = null) {
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
