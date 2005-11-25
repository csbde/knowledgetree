<?php

class KTAuthenticationProvider extends KTStandardDispatcher {
    var $sName;
    var $sNamespace;
    var $bHasSource = false;

    function KTAuthenticationProvider() {
        return parent::KTStandardDispatcher();
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

    function showSource() {
        return null;
    }

    function getName() {
        return $this->sName;
    }
    function getNamespace() {
        return $this->sNamespace;
    }

    function do_editSourceProvider() {
        return $this->errorRedirectTo('viewsource', "Provider does not support editing", 'source_id=' .  $_REQUEST['source_id']);
    }

    function do_performEditSourceProvider() {
        return $this->errorRedirectTo('viewsource', "Provider does not support editing", 'source_id=' .  $_REQUEST['source_id']);
    }
}
