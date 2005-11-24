<?php

class KTAuthenticationProvider {
    var $sName;
    var $sNamespace;
    var $bHasSource = false;

    function KTAuthenticationProvider() {
    }

    function configure($aInfo) {
        $this->aInfo = $aInfo;
    }

    function &getAuthenticator() {
        return $this;
    }

    function &getSource() {
        if (empty($bHasSource)) {
            return null;
        }
        return $this;
    }
}
