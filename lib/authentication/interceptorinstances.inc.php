<?php

/**
 * $Id: authenticationsource.inc.php 5758 2006-07-27 10:17:43Z bshuttle $
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

require_once(KT_LIB_DIR . "/users/User.inc");
require_once(KT_LIB_DIR . "/ktentity.inc");

class KTInterceptorInstance extends KTEntity {
    var $sName;
    var $sInterceptorNamespace;
    var $sAuthenticationProvider;
    var $sConfig = "";

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sInterceptorNamespace" => "interceptor_namespace",
        "sConfig" => "config",
    );

    var $_bUsePearError = true;

    function getName() { return $this->sName; }
    function getInterceptorNamespace() { return $this->sInterceptorNamespace; }
    function getConfig() { return $this->sConfig; }
    function setName($sName) { $this->sName = $sName; }
    function setInterceptorNamespace($mValue) { $this->sInterceptorNamespace = $mValue; }
    function setConfig($sConfig) { $this->sConfig = $sConfig; }

    function _table () {
        return KTUtil::getTableName('interceptor_instances');
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTInterceptorInstance', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTInterceptorInstance', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        return KTEntityUtil::getList2('KTInterceptorInstance', $sWhereClause);
    }

    // STATIC
    function &getByInterceptorNamespace($sNamespace) {
        return KTEntityUtil::getBy('KTInterceptorInstance', 'namespace', $sNamespace);
    }

    function &getInterceptorInstances() {
        return KTEntityUtil::getList2('KTInterceptorInstance', $sWhereClause);
    }
}

?>
