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

    function &getAuthenticator($oSource) {
        // Not implemented
        return null;
    }

    function &getSource() {
        if (empty($bHasSource)) {
            return null;
        }
        return $this;
    }

    /**
     * Gives the provider a chance to show something about how the
     * authentication source is set up.  For example, describing the
     * server settings for an LDAP authentication source.
     */
    function showSource($oSource) {
        return null;
    }

    /**
     * Gives the provider a chance to show something about how the
     * user's authentication works.  For example, providing a link to a
     * page to allow the admin to change a user's password.
     */
    function showUserSource($oUser, $oSource) {
        return null;
    }

    function getName() {
        return $this->sName;
    }
    function getNamespace() {
        return $this->sNamespace;
    }

    function do_editSourceProvider() {
        return $this->errorRedirectTo('viewsource', _("Provider does not support editing"), 'source_id=' .  $_REQUEST['source_id']);
    }

    function do_performEditSourceProvider() {
        return $this->errorRedirectTo('viewsource', _("Provider does not support editing"), 'source_id=' .  $_REQUEST['source_id']);
    }
}
