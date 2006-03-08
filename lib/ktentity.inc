<?php

/**
 * $Id$
 *
 * Base class for database-backed objects
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib
 */

require_once(KT_LIB_DIR . '/cache/cache.inc.php');

class KTEntity {
    var $_bUsePearError = false;
    /** object primary key */
    var $iId = -1;

    function getId() {
        return $this->iId;
    }

    function _cachedGroups() {
        return array('getlist');
    }

    function clearCachedGroups() {
        $groups = $this->_cachedGroups();
        $oCache =& KTCache::getSingleton();
        foreach ($groups as $group) {
            global $default;
            $group = sprintf("%s/%s", get_class($this), $group);
            $default->log->debug("Clearing cached group: $group");
            $oCache->clear($group);
        }
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
        if (is_null($iId)) {
            if (is_null($this->iId)) {
                return PEAR::raiseError("No ID given");
            }
            $iId = $this->iId;
        }
        $table = $this->_table();
        $select = $this->_getSqlSelection();
        $sQuery = "SELECT $select FROM $table WHERE id = ?";
        $aParams = array($iId);

        $res = DBUtil::getResultArray(array($sQuery, $aParams));

        if (PEAR::isError($res)) {
            return $res;
        }
        if (count($res) === 0) {
            return PEAR::raiseError("No such ID: $iId");
        }
        if (count($res) > 1) {
            return PEAR::raiseError("Multiple matches for ID: $iId");
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
            return PEAR::raiseError("Expected an array!");
        }

        foreach ($aOptions as $sField => $sValue) {
            $sElement = $this->_getElementFromMethod($sField);
            if ($sElement === false) {
                return PEAR::raiseError('Setting a non-existent field: ' . $sField);
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

        $array = array_keys($this->_aFieldToSelect);

        foreach($array as $k) {
            $case[strtolower($k)] = $k;
        }

        $sElement = strtolower($sElement);

        if (array_key_exists($sElement, $case)) {
            return $case[strtolower($sElement)];
        }

        foreach($array as $k) {
            $case[substr(strtolower($k), 1)] = $k;
        }

        if (array_key_exists($sElement, $case)) {
            return $case[strtolower($sElement)];
        }
        return PEAR::raiseError("No such element");
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
        return true;
    }
}

class KTEntityUtil {
    function &getList2($sClassName, $sWhereClause = null, $aOptions = null) {
        $sTable = call_user_func(array($sClassName, "_table"));
        return KTEntityUtil::getList($sTable, $sClassName, $sWhereClause, $aOptions);
    }

    function &getList($sTable, $sClassName, $sWhereClause = null, $aOptions = null) {
        if (is_null($aOptions)) {
            $aOptions = array();
        }
        $bIDs = false;
        $bIDs = KTUtil::arrayGet($aOptions, "ids", false);
        $sIDField = 'id';
        $cache = KTUtil::arrayGet($aOptions, 'cache', false);
        $fullselect = KTUtil::arrayGet($aOptions, 'fullselect', false);
        if ($cache) {
            global $default;
            if (is_array($sWhereClause)) {
                $vals = serialize($sWhereClause[1]);
            } else {
                $vals = serialize($sWhereClause);
            }
            $default->log->debug(sprintf("Trying object cache for class %s, %s: %s", $sClassName, $cache, $vals));

            $oCache =& KTCache::getSingleton();
            $group = sprintf("%s/%s", $sClassName, $cache);
            list($bCached, $mCached) = $oCache->get($group, $vals);
            if ($bCached) {
                $default->log->debug(sprintf("Using object cache for class %s, %s: %s", $sClassName, $cache, $vals));
                return $mCached;
                /* */
            }
        }
        if (!empty($fullselect)) {
            $oObject = new $sClassName;
            $select = $oObject->_getSqlSelection();
            $sQuery = "SELECT $select FROM " . $sTable;/*ok*/
        } else {
            $sIDField = KTUtil::arrayGet($aOptions, "idfield", 'id');
            $sQuery = "SELECT $sIDField FROM " . $sTable;/*ok*/
        }
        $aParams = array();
        if (!is_null($sWhereClause)) {
            if (is_string($sWhereClause)) {
                if (substr($sWhereClause, 0, 5) != 'WHERE') {
                    if (substr($sWhereClause, 0, 5) != 'ORDER') {
                        $sQuery .= ' WHERE';
                    }
                }
                $sQuery .= ' ' . $sWhereClause;
            } else if (is_array($sWhereClause)) {
                if (substr($sWhereClause[0], 0, 5) != 'WHERE') {
                    if (substr($sWhereClause[0], 0, 5) != 'ORDER') {
                        $sQuery .= ' WHERE';
                    }
                }
                $sQuery .= ' ' . $sWhereClause[0];
                $aParams = $sWhereClause[1];
            } else {
                return new PEAR_Error('Weird WhereClause passed');
            }
        }
        $sOrderBy = KTUtil::arrayGet($aOptions, 'orderby');
        if (!empty($sOrderBy)) {
            $sQuery .= " ORDER BY " . $sOrderBy;
        }

        if (!empty($fullselect)) {
            $aIDs = DBUtil::getResultArray(array($sQuery, $aParams));
        } else {
            $aIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), $sIDField);
        }

        if (PEAR::isError($aIDs)) {
            return $aIDs;
        }

        if ($bIDs === true) {
            return $aIDs;
        }

        if (!empty($fullselect)) {
            $aRet =& KTEntityUtil::loadFromArrayMulti($sClassName, $aIDs);
            if ($cache) {
                $oCache->set($group, $vals, $aRet);
            }
            return $aRet;
        }
        $aRet = array();
        foreach ($aIDs as $iId) {
            $aRet[] =& call_user_func(array($sClassName, 'get'), $iId);
        }
        if ($cache) {
            $default->log->debug(sprintf("Setting object cache for class %s, %s, %s", $sClassName, $cache, $vals));
            $oCache->set($group, $vals, $aRet);
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
        return $oObject;
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
        return true;
    }

    function &loadFromArray ($sClassName, $aArray, $aOptions = null) {
        $oObject =& new $ClassName;
        $ret = $oObject->loadFromArray($aArray);
        if (PEAR::isError($ret)) {
            return $ret;
        }
        $oCache =& KTCache::getSingleton();
        $group = sprintf("%s/%s", $sClassName, 'id');
        $oCache->set($group, $oObject->getId(), $oObject);
        return $oObject;
    }

    function &loadFromArrayMulti($sClassName, $aMultiArray, $aOptions = null) {
        $aRet = array();
        $oCache =& KTCache::getSingleton();
        foreach ($aMultiArray as $aArray) {
            $oObject =& new $sClassName;
            $ret = $oObject->loadFromArray($aArray);
            if (PEAR::isError($ret)) {
                return $ret;
            }
            $aRet[] = $oObject;
            $group = sprintf("%s/%s", $sClassName, 'id');
            $oCache->set($group, $oObject->getId(), $oObject);
        }
        return $aRet;
    }

    function &get($sClassName, $iId) {
        $oCache =& KTCache::getSingleton();
        $group = sprintf("%s/%s", $sClassName, 'id');
        list($bCached, $mCached) = $oCache->get($group, $iId);
        if ($bCached) {
            global $default;
            $default->log->debug(sprintf("Using object cache for class %s, id %d", $sClassName, $iId));
            return $mCached;
            /* */
        }
        $oObject =& new $sClassName;
        $res = $oObject->load($iId);
        if (PEAR::isError($res)) {
            return $res;
        }
        $oCache->set($group, $iId, $oObject);
        return $oObject;
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

            $sType = KTUtil::arrayGet($mThisValue, 'type');
            if (empty($sType)) {
                $mThisValue = array('type' => 'equals', 'value' => $mThisValue);
                $sType = 'equals';
            }

            $sFunc = array('KTEntityUtil', sprintf('_getby_%s', $sType));
            if (!is_callable($sFunc)) {
                return PEAR::raiseError('Unknown comparison type given: ' . $sType);
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
                return PEAR::raiseError("Multiple objects returned");
            }
            return $aObjects[0];
        } else {
            return $aObjects;
        }
    }

    function &getByDict($sClassName, $aDict, $aOptions = null) {
        return KTEntityUtil::getBy($sClassName, array_keys($aDict), array_values($aDict), $aOptions);
    }
}

class KTEntityNoObjects extends PEAR_Error {
}

?>
