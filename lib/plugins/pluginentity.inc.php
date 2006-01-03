<?php

require_once(KT_LIB_DIR . '/ktentity.inc');

class KTPluginEntity extends KTEntity {
    var $_bUsePearError = true;
    
    var $sNamespace;
    var $sPath;
    var $iVersion;
    var $bDisabled;
    var $sData;

    // {{{ KTEntity-related
    var $_aFieldToSelect = array(
        'iId' => 'id',
        'sNamespace' => 'namespace',
        'sPath' => 'path',
        'iVersion' => 'version',
        'bDisabled' => 'disabled',
        'sData' => 'data',
    );

    function _table() {
        return KTUtil::getTableName('plugins');
    }
    // }}}

    // {{{ getters/setters
    function getNamespace() { return $this->sNamespace; }
    function getPath() { return $this->sPath; }
    function getVersion() { return $this->iVersion; }
    function getDisabled() { return $this->bDisabled; }
    function getData() { return $this->sData; }
    function setNamespace($sValue) { $this->sNamespace = $sValue; }
    function setPath($sValue) { $this->sPath = $sValue; }
    function setVersion($iValue) { $this->iVersion = $iValue; }
    function setDisabled($bValue) { $this->bDisabled = $bValue; }
    function setData($sValue) { $this->sData = $sValue; }
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
        global $default;
        return KTEntityUtil::getList2('KTPluginEntity', $sWhereClause);
    }

    // STATIC
    function &getByNamespace($sName) {
        return KTEntityUtil::getBy('KTPluginEntity', 'namespace', $sName);
    }

    function &getEnabledPlugins() {
        $aOptions = array(
            'ids' => true,
            'multi' => true,
        );
        return KTEntityUtil::getBy('KTPluginEntity', 'disabled', false,
                $aOptions);
    }
}
