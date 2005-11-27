<?php

require_once(KT_LIB_DIR . '/authentication/authenticationprovider.inc.php');
require_once(KT_LIB_DIR . '/authentication/Authenticator.inc');
require_once(KT_LIB_DIR . '/authentication/class.AuthLdap.php');

class KTLDAPAuthenticationProvider extends KTAuthenticationProvider {
    var $sName = "LDAP authentication provider";
    var $sNamespace = "ktstandard.authentication.ldapprovider";

    var $aConfigMap = array(
        'servername' => 'LDAP Server',
        'basedn' => 'Base DN',
        'servertype' => 'LDAP Server Type',
        'domain' => 'LDAP Server Domain',
        'searchuser' => 'LDAP Search User',
        'searchpassword' => 'LDAP Search Password',
    );

    function saveConfig(&$oSource, $aRequest) {
        return true;
    }

    function configFields($oSource) {
        return array();
    }

    function showSource($oSource) {
        $aConfig = unserialize($oSource->getConfig());
        if (empty($aConfig)) {
            $aConfig = array();
        }
        $sRet = "<dl>\n";
        foreach ($this->aConfigMap as $sSettingName => $sName) {
            $sRet .= "  <dt>$sName</dt>\n";
            $sValue = KTUtil::arrayGet($aConfig, $sSettingName, "Unset");
            $sRet .= "  <dd>" . $sValue . "</dd>\n";
        }
        $sRet .= "</dl>\n";
        return $sRet;
    }

    function showUserSource($oUser, $oSource) {
        return '<a href="?action=editUserSource&user_id=' . $oUser->getId() .'">Edit LDAP info</a>';
    }

    function do_editUserSource() {
        $submit = KTUtil::arrayGet($_REQUEST, 'submit');
        if (KTUtil::arrayGet($submit, 'save')) {
            return $this->_do_saveUserSource();
        }

        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser =& $this->oValidator->validateUser($user_id);

        $this->oPage->setBreadcrumbDetails("editing LDAP details");
        $oTemplate = $this->oValidator->validateTemplate('ktstandard/authentication/ldapedituser');

        $oAuthenticationSource = KTAuthenticationSource::getForUser($oUser);

        $aDetails = unserialize($oUser->getAuthenticationDetails());
        $dn = KTUtil::arrayGet($aDetails, 'dn', "");

        $fields = array();
        $fields[] = new KTStringWidget('Distinguished name', 'The location of this user in the LDAP tree', 'dn', $dn, $this->oPage, true);

        $aTemplateData = array(
            'context' => &$this,
            'fields' => $fields,
            'user' => $oUser,
        );
        return $oTemplate->render($aTemplateData);
    }

    function _do_saveUserSource() {
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser =& $this->oValidator->validateUser($user_id);

        $aDetails['dn'] = KTUtil::arrayGet($_REQUEST, 'dn', "");
        $oUser->setAuthenticationDetails(serialize($aDetails));
        $oUser->update();
        $this->successRedirectTo("editUser", "Details updated",
            sprintf('user_id=%d', $oUser->getId()));
    }

    function do_editSourceProvider() {
        require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
        $this->oPage->setBreadcrumbDetails("editing LDAP settings");
        $oTemplate = $this->oValidator->validateTemplate('ktstandard/authentication/ldapeditsource');
        $iSourceId = KTUtil::arrayGet($_REQUEST, 'source_id');
        $oSource = KTAuthenticationSource::get($iSourceId);
        $fields = array();
        $fields[] = new KTStringWidget('Server name', 'The host name or IP address of the LDAP server', 'servername', '', $this->oPage, true);
        $fields[] = new KTStringWidget('Base DN', 'FIXME', 'basedn', '', $this->oPage, true);
        $fields[] = new KTStringWidget('Server Type', 'FIXME', 'servertype', '', $this->oPage, true);
        $fields[] = new KTStringWidget('Domain', 'FIXME', 'domain', '', $this->oPage, true);
        $fields[] = new KTStringWidget('Search User', 'FIXME', 'searchuser', '', $this->oPage, true);
        $fields[] = new KTStringWidget('Search Password', 'FIXME', 'searchpassword', '', $this->oPage, true);
        $aTemplateData = array(
            'context' => &$this,
            'fields' => $fields,
            'source' => $oSource,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_performEditSourceProvider() {
        $iSourceId = KTUtil::arrayGet($_REQUEST, 'source_id');
        $oSource = KTAuthenticationSource::get($iSourceId);
        $aConfig = array();
        foreach ($this->aConfigMap as $k => $v) {
            $sValue = KTUtil::arrayGet($_REQUEST, $k);
            if ($sValue) {
                $aConfig[$k] = $sValue;
            }
        }
        $oSource->setConfig(serialize($aConfig));
        $oSource->update();
        $this->successRedirectTo('viewsource', "Configuration updated", 'source_id=' . $oSource->getId());
    }

    function &getAuthenticatorForSource($oSource) {
        return new LDAPAuthenticator($oSource);
    }

}

class LDAPAuthenticator extends Authenticator {
    /**
     * The LDAP server to connect to
     */
    var $sLdapServer;
    /**
     * The base LDAP DN to perform authentication against
     */
    var $sBaseDN;
    /**
     * The LDAP accessor class
     */
    var $oLdap;

    /**
     * Creates a new instance of the LDAPAuthenticator
     *
     * @param string the LDAP server to connect to for validation (optional)
     * @param string the dn branch to perform the authentication against (optional)
     * @param string the ldap server type (optional)
     */
    function LDAPAuthenticator($oSource) {
        $this->oSource =& $oSource;
        $aConfig = unserialize($oSource->getConfig());
        $this->sLdapServer = $aConfig['servername'];
        $this->sBaseDN = $aConfig['basedn'];
        $this->sServerType = $aConfig['servertype'];
        $this->sLdapDomain = $aConfig['domain'];
        $this->sSearchUser = $aConfig['searchuser'];
        $this->sSearchPassword = $aConfig['searchpassword'];

        // initialise and setup ldap class
        $this->oLdap = new AuthLdap($this->sLdapServer, $this->sBaseDN, $this->sServerType, $this->sLdapDomain, $this->sSearchUser, $this->sSearchPassword);
    }

    /**
     * Checks the user's password against the LDAP directory
     *
     * @param string the name of the user to check
     * @param string the password to check
     * @return boolean true if the password is correct, else false
     */
    function checkPassword($oUser, $sPassword) {
        $aDetails = unserialize($oUser->getAuthenticationDetails());
        if ($this->oLdap->connect()) {
            // lookup dn from username - must exist in db
            $sBindDn = $aDetails['dn'];
            if ($this->oLdap->authBind($sBindDn, $sPassword)) {
                return true;
            } else {
                return PEAR::raiseError("LDAP error: (" . $this->oLdap->ldapErrorCode . ") " . $this->oLdap->ldapErrorText);
            }
        } else {
            return PEAR::raiseError('LDAP server unreachable');
        }
    }


    /**
     * Searched the directory for a specific user
     *
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */
    function getUser($sUserName, $aAttributes) {
        global $default;
        // connect and search
        if ( $this->oLdap->connect() ) {
            // search for the users
            // append and prepend wildcards
            $aUserResults = $this->oLdap->getUsers($sUserName, $aAttributes);
            if ($aUserResults) {
                // return the array
                return $aUserResults;
            } else {
                // the search failed, return empty array
                return array();
            }
        } else {
            $_SESSION["errorMessage"] = "LDAP error: (" . $this->oLdap->ldapErrorCode . ") " . $this->oLdap->ldapErrorText;
            return false;
        }
    }

    /**
     * Searches the LDAP directory for users matching the supplied search string.
     *
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */
    function searchUsers($sUserNameSearch, $aAttributes) {
        global $default;
        // connect and search
        if ( $this->oLdap->connect() ) {
            // search for the users
            // append and prepend wildcards
            $aUserResults = $this->oLdap->getUsers("*" . $sUserNameSearch . "*", $aAttributes);
            if ($aUserResults) {
                // return the array
                return $aUserResults;
            } else {
                // the search failed, return empty array
                return array();
            }
        } else {
            $default->log->error("LDAPAuthentication::searchUsers LDAP error: (" . $this->oLdap->ldapErrorCode . ") " . $this->oLdap->ldapErrorText);
            return false;
        }
    }
}

