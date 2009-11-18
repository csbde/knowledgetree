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

require_once(KT_LIB_DIR . '/ktentity.inc');

class KTPluginEntity extends KTEntity {
    var $_bUsePearError = true;

    var $sNamespace;
    var $sPath;
    var $iVersion;
    var $bDisabled;
    var $sData;
    var $bUnavailable;
    var $iOrderBy=0;

    // {{{ KTEntity-related
    var $_aFieldToSelect = array(
        'iId' => 'id',
        'sNamespace' => 'namespace',
        'sPath' => 'path',
        'iVersion' => 'version',
        'bDisabled' => 'disabled',
        'sData' => 'data',
        'bUnavailable' => 'unavailable',
        'sFriendlyName' => 'friendly_name',
        'iOrderBy' => 'orderby',
        'iList_Admin' => 'list_admin'
    );

    function _table() {
        return KTUtil::getTableName('plugins');
    }

    function _cachedGroups() {
        return array('getlist', 'getList', 'getByNamespace');
    }
    // }}}

    // {{{ getters/setters
    function getNamespace() { return $this->sNamespace; }
    function getPath() { return $this->sPath; }
    function getVersion() { return $this->iVersion; }
    function getDisabled() { return $this->bDisabled; }
    function getData() { return $this->sData; }
    function getUnavailable() { return $this->bUnavailable; }
    function getFriendlyName($sValue) { return $this->sFriendlyName; }
    function getOrderBy() { return $this->iOrderBy; }
    function setNamespace($sValue) { $this->sNamespace = $sValue; }
    function setPath($sValue) { $this->sPath = $sValue; }
    function setVersion($iValue) { $this->iVersion = $iValue; }
    function setDisabled($bValue) { $this->bDisabled = $bValue; }
    function setData($sValue) { $this->sData = $sValue; }
    function setUnavailable($mValue) { $this->bUnavailable = $mValue; }
    function setFriendlyName($sValue) { $this->sFriendlyName = $sValue; }
    function setOrderBy($iValue) { $this->iOrderBy = $iValue; }
    // }}}

    function get($iId) {
        return KTEntityUtil::get('KTPluginEntity', $iId);
    }
    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTPluginEntity', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        return KTEntityUtil::getList2('KTPluginEntity', $sWhereClause, null);
    }

    // STATIC
    function &getByNamespace($sName) {
        $aOptions = array('fullselect' => false, 'cache' => 'getByNamespace');
        return KTEntityUtil::getBy('KTPluginEntity', 'namespace', $sName, $aOptions);
    }

    // STATIC
    function &getAvailable() {
        $aOptions = array('multi' => true, 'orderby' => 'friendly_name');
        // return KTEntityUtil::getBy('KTPluginEntity', 'unavailable', false, $aOptions);
        // Ignore those plugins that are unavailable or set as invisible
        $aKeys = array('unavailable', 'list_admin');
        $aValues = array(false, true);
        return KTEntityUtil::getBy('KTPluginEntity', $aKeys, $aValues, $aOptions);
    }

    function &getEnabledPlugins() {
        $aOptions = array(
            'ids' => true,
            'multi' => true,
        );
        return KTEntityUtil::getBy('KTPluginEntity', 'disabled', false,
                $aOptions);
    }

    function setEnabled($aIds) {
        $sTable = KTPluginEntity::_table();
        $sIds = DBUtil::paramArray($aIds);
        $sQuery = sprintf('UPDATE %s SET disabled = 1 WHERE id NOT IN (%s)', $sTable, $sIds);
        DBUtil::runQuery(array($sQuery, $aIds));
        $sQuery = sprintf('UPDATE %s SET disabled = 0 WHERE id IN (%s)', $sTable, $sIds);
        DBUtil::runQuery(array($sQuery, $aIds));
        KTPluginEntity::clearAllCaches();
    }

    function clearAllCaches() {
        return KTEntityUtil::clearAllCaches('KTPluginEntity');
    }

        // either return the friendly name, or the namespace (for failback).
    function getUserFriendlyName() {
        $n = trim($this->sFriendlyName);
        if (!empty($n)) { return $this->sFriendlyName; }
        return $this->sNamespace;
    }
}
