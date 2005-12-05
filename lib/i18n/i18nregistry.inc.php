<?php

require_once(KT_LIB_DIR . '/i18n/i18n.inc.php');

class KTi18nRegistry {
    var $_ai18nDetails = array();
    var $_ai18ns = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTi18nRegistry')) {
            $GLOBALS['oKTi18nRegistry'] = new KTi18nRegistry;
        }
        return $GLOBALS['oKTi18nRegistry'];
    }

    function registeri18n($sDomain, $sDirectory = "") {
        if (empty($sDirectory)) {
            $sDirectory = KT_DIR . '/i18n';
        }
        $this->_ai18nDetails[$sDomain] = array($sDomain, $sDirectory);
        bindtextdomain($sDomain, $sDirectory);
        bind_textdomain_codeset($sDomain, 'UTF-8');
    }

    function &geti18n($sDomain) {
        $oi18n =& KTUtil::arrayGet($this->_ai18ns, $sDomain);
        if (!empty($oi18n)) {
            return $oi18n;
        }
        $aDetails = KTUtil::arrayGet($this->_ai18nDetails, $sDomain);
        if (empty($aDetails)) {
            return new KTi18nGeneric;
        }
        $oi18n =& new KTi18n($sDomain, $sDirectory);
        $this->ai18ns[$sDomain] =& $oi18n;
        return $oi18n;
    }
}

