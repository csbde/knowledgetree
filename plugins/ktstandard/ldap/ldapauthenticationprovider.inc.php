<?php

require_once(KT_LIB_DIR . '/authentication/authenticationprovider.inc.php');
require_once(KT_LIB_DIR . '/authentication/Authenticator.inc');
require_once(KT_LIB_DIR . '/authentication/class.AuthLdap.php');
require_once("Net/LDAP.php");

class KTLDAPAuthenticationProvider extends KTAuthenticationProvider {
    var $sName = "LDAP authentication provider";
    var $sNamespace = "ktstandard.authentication.ldapprovider";

    function KTLDAPAuthenticationProvider() {
        $this->aConfigMap = array(
            'servername' => _('LDAP Server'),
            'basedn' => _('Base DN'),
            'servertype' => _('LDAP Server Type'),
            'domain' => _('LDAP Server Domain'),
            'searchuser' => _('LDAP Search User'),
            'searchpassword' => _('LDAP Search Password'),
        );
        return parent::KTAuthenticationProvider();
    }

    function showSource($oSource) {
        $aConfig = unserialize($oSource->getConfig());
        if (empty($aConfig)) {
            $aConfig = array();
        }
        $sRet = "<dl>\n";
        foreach ($this->aConfigMap as $sSettingName => $sName) {
            $sRet .= "  <dt>$sName</dt>\n";
            $sValue = KTUtil::arrayGet($aConfig, $sSettingName, _("Unset"));
            $sRet .= "  <dd>" . $sValue . "</dd>\n";
        }
        $sRet .= "</dl>\n";
        return $sRet;
    }

    function showUserSource($oUser, $oSource) {
        $sQuery = sprintf("action=editUserSource&user_id=%d", $oUser->getId());
        $sUrl = KTUtil::addQueryStringSelf($sQuery);
        return '<a href="' . $sUrl . '">' . _('Edit LDAP info') . '</a>';
    }

    function do_editUserSource() {
        $submit = KTUtil::arrayGet($_REQUEST, 'submit');
        if (KTUtil::arrayGet($submit, 'save')) {
            return $this->_do_saveUserSource();
        }

        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser =& $this->oValidator->validateUser($user_id);

        $this->oPage->setBreadcrumbDetails(_("editing LDAP details"));
        $oTemplate = $this->oValidator->validateTemplate('ktstandard/authentication/ldapedituser');

        $oAuthenticationSource = KTAuthenticationSource::getForUser($oUser);

        $dn = $oUser->getAuthenticationDetails();

        $fields = array();
        $fields[] = new KTStringWidget(_('Distinguished name'), _('The location of this user in the LDAP tree'), 'dn', $dn, $this->oPage, true);

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

        $dn = KTUtil::arrayGet($_REQUEST, 'dn', "");
        if (empty($dn)) {
            $this->errorRedirectToMain("Error validating LDAP details");
        }
        $oUser->setAuthenticationDetails($dn);
        $oUser->update();
        $this->successRedirectTo("editUser", _("Details updated"),
            sprintf('user_id=%d', $oUser->getId()));
    }

    function do_editSourceProvider() {
        require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
        $this->oPage->setBreadcrumbDetails(_("editing LDAP settings"));
        $oTemplate = $this->oValidator->validateTemplate('ktstandard/authentication/ldapeditsource');
        $iSourceId = KTUtil::arrayGet($_REQUEST, 'source_id');
        $oSource = KTAuthenticationSource::get($iSourceId);
        $aConfig = unserialize($oSource->getConfig());
        $fields = array();
        $fields[] = new KTStringWidget(_('Server name'), 'The host name or IP address of the LDAP server', 'servername', $aConfig['servername'], $this->oPage, true);
        $fields[] = new KTStringWidget(_('Base DN'), 'FIXME', 'basedn', $aConfig['basedn'], $this->oPage, true);
        $fields[] = new KTStringWidget(_('Server Type'), 'FIXME', 'servertype', $aConfig['servertype'], $this->oPage, true);
        $fields[] = new KTStringWidget(_('Domain'), 'FIXME', 'domain', $aConfig['domain'], $this->oPage, true);
        $fields[] = new KTStringWidget(_('Search User'), 'FIXME', 'searchuser', $aConfig['searchuser'], $this->oPage, true);
        $fields[] = new KTStringWidget(_('Search Password'), 'FIXME', 'searchpassword', $aConfig['searchpassword'], $this->oPage, true);
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
        $this->successRedirectTo('viewsource', _("Configuration updated"), 'source_id=' . $oSource->getId());
    }

    function &getAuthenticator($oSource) {
        return new LDAPAuthenticator($oSource);
    }

    function _do_editUserFromSource() {
        $oTemplate = $this->oValidator->validateTemplate('ktstandard/authentication/ldapadduser');
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $id = KTUtil::arrayGet($_REQUEST, 'id');

        $aConfig = unserialize($oSource->getConfig());
        $aAttributes = array ("dn", "uid", "givenname", "sn", "mail", "mobile");

        $oAuthenticator = $this->getAuthenticator($oSource);
        $aResults = $oAuthenticator->getUser($id, $aAttributes);
        
        $fields = array();
        $fields[] =  new KTStaticTextWidget(_('LDAP DN'), _('The location of the user within the LDAP directory.'), 'dn', $aResults[$aAttributes[0]], $this->oPage);
        $fields[] =  new KTStringWidget(_('Username'), _('The username the user will enter to gain access to KnowledgeTree.  e.g. <strong>jsmith</strong>'), 'ldap_username', $aResults[$aAttributes[1]], $this->oPage, true);
        $fields[] =  new KTStringWidget(_('Name'), _('The full name of the user.  This is shown in reports and listings.  e.g. <strong>John Smith</strong>'), 'name', join(" ", array($aResults[$aAttributes[2]], $aResults[$aAttributes[3]])), $this->oPage, true);
        $fields[] =  new KTStringWidget(_('Email Address'), _('The email address of the user.  Notifications and alerts are mailed to this address if <strong>email notifications</strong> is set below. e.g. <strong>jsmith@acme.com</strong>'), 'email_address', $aResults[$aAttributes[4]], $this->oPage, false);
        $fields[] =  new KTCheckboxWidget(_('Email Notifications'), _('If this is specified then the user will have notifications sent to the email address entered above.  If it is not set, then the user will only see notifications on the <strong>Dashboard</strong>'), 'email_notifications', true, $this->oPage, false);
        $fields[] =  new KTStringWidget(_('Mobile Number'), _('The mobile phone number of the user.  If the system is configured to send notifications to cellphones, then this number will have an SMS delivered to it with notifications.  e.g. <strong>999 9999 999</strong>'), 'mobile_number', $aResults[$aAttributes[5]], $this->oPage, false);
        $fields[] =  new KTStringWidget(_('Maximum Sessions'), _('As a safety precaution, it is useful to limit the number of times a given account can log in, before logging out.  This prevents a single account being used by many different people.'), 'max_sessions', '3', $this->oPage, true);

        $aTemplateData = array(
            'context' => &$this,
            'fields' => $fields,
            'source' => $oSource,
            'search_results' => $aSearchResults,
            'dn' => $aResults[$aAttributes[0]],
        );
        return $oTemplate->render($aTemplateData);
    }

    function _do_createUserFromSource() {
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $dn = KTUtil::arrayGet($_REQUEST, 'dn');
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        if (empty($name)) { $this->errorRedirectToMain(_('You must specify a name for the user.')); }
        $username = KTUtil::arrayGet($_REQUEST, 'ldap_username');
        if (empty($name)) { $this->errorRedirectToMain(_('You must specify a new username.')); }
        // FIXME check for non-clashing usernames.

        $email_address = KTUtil::arrayGet($_REQUEST, 'email_address');
        $email_notifications = KTUtil::arrayGet($_REQUEST, 'email_notifications', false);
        if ($email_notifications !== false) $email_notifications = true;
        $mobile_number = KTUtil::arrayGet($_REQUEST, 'mobile_number');
        $max_sessions = KTUtil::arrayGet($_REQUEST, 'max_sessions', '3');
        // FIXME check for numeric max_sessions... db-error else?

        $oUser =& User::createFromArray(array(
            "Username" => $username,
            "Name" => $name,
            "Email" => $email_address,
            "EmailNotification" => $email_notifications,
            "SmsNotification" => false,   // FIXME do we auto-act if the user has a mobile?
            "MaxSessions" => $max_sessions,
            "authenticationsourceid" => $oSource->getId(),
            "authenticationdetails" => $dn,
            "password" => "",
        ));

        if (PEAR::isError($oUser) || ($oUser == false)) {
            $this->errorRedirectToMain(_("failed to create user."));
            exit(0);
        }

        $this->successRedirectToMain(_('Created new user') . ': ' . $oUser->getUsername());
        exit(0);
    }

    function do_addUserFromSource() {
        $submit = KTUtil::arrayGet($_REQUEST, 'submit');
        if (!is_array($submit)) {
            $submit = array();
        }
        if (KTUtil::arrayGet($submit, 'chosen')) {
            $id = KTUtil::arrayGet($_REQUEST, 'id');
            if (!empty($id)) {
                return $this->_do_editUserFromSource();
            } else {
                $this->oPage->addError(_("No valid LDAP user chosen"));
            }
        }
        if (KTUtil::arrayGet($submit, 'create')) {
            return $this->_do_createUserFromSource();
        }
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $oTemplate = $this->oValidator->validateTemplate('ktstandard/authentication/ldapsearchuser');

        $fields = array();
        $fields[] = new KTStringWidget(_("User's name"), _("The user's name, or part thereof, to find the user that you wish to add"), 'name', '', $this->oPage, true);

        $oAuthenticator = $this->getAuthenticator($oSource);
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        if (!empty($name)) {
            $aSearchResults = $oAuthenticator->searchUsers($name);
        }
        
        $aTemplateData = array(
            'context' => &$this,
            'fields' => $fields,
            'source' => $oSource,
            'search_results' => $aSearchResults,
        );
        return $oTemplate->render($aTemplateData);
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
     * Creates a new instance of the ActiveDirectoryAuthenticator
     *
     *
     */
    function LDAPAuthenticator($oSource) {
        $this->oSource =& $oSource;
        $aConfig = unserialize($oSource->getConfig());
        $this->sLdapServer = $aConfig['servername'];
        $this->sBaseDN = $aConfig['basedn'];
        $this->sLdapDomain = $aConfig['domain'];
        $this->sSearchUser = $aConfig['searchuser'];
        $this->sSearchPassword = $aConfig['searchpassword'];

        require_once('Net/LDAP.php');
        $config = array(
            'dn' => $this->sSearchUser,
            'password' => $this->sSearchPassword,
            'host' => $this->sLdapServer,
            'base' => $this->sBaseDN,
        );

        $this->oLdap =& Net_LDAP::connect($config);
    }
     /**      * Authenticate the user against the LDAP directory
     *
     * @param string the user to authenticate
     * @param string the password to check
     * @return boolean true if the password is correct, else false
     */
    function checkPassword($oUser, $sPassword) {
        $dn = $oUser->getAuthenticationDetails();
        $config = array(
            'host' => $this->sLdapServer,
            'base' => $this->sBaseDN,
        );
        $oLdap =& Net_LDAP::connect($config);
        $res = $oLdap->reBind($dn, $sPassword);
        return $res;
    }


    /**
     * Searched the directory for a specific user
     *
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */
    function getUser($dn) {
        if (PEAR::isError($this->oLdap)) {
            return $this->oLdap;
        }
        $aAttributes = array ("cn", "uid", "givenname", "sn", "mail", "mobile");

        $oEntry = $this->oLdap->getEntry($dn, $aAttributes);
        $aAttr = $oEntry->attributes();
        $aAttr['dn'] = $oEntry->dn();

        foreach ($aAttr as $k => $v) {
            $aRet[strtolower($k)] = $v;
        }
        return $aRet;
    }

    /**
     * Searches the LDAP directory for users matching the supplied search string.
     *
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */
    function searchUsers($sSearch) {
        if (PEAR::isError($this->oLdap)) {
            return $this->oLdap;
        }

        $aParams = array(
            'scope' => 'sub',
            'attributes' => array('cn', 'dn'),
        );

        $rootDn = $this->sBaseDN;
        if (is_array($rootDn)) {
            $rootDn = join(",", $rootDn);
        }
        $sFilter = sprintf('(&(objectClass=posixAccount)(|(uid=*%s*)(samaccountname=*%s*)(cn=*%s*)))', $sSearch, $sSearch, $sSearch);
        $oResult = $this->oLdap->search($rootDn, $sFilter, $aParams);
        if (PEAR::isError($oResult)) {
            return $oResult;
        }
        $aRet = array();
        foreach($oResult->entries() as $oEntry) {
            $aAttr = $oEntry->attributes();
            $aAttr['dn'] = $oEntry->dn();
            $aRet[] = $aAttr;
        }
        return $aRet;
    }

    function searchGroups($sSearch) {
        if (PEAR::isError($this->oLdap)) {
            return $this->oLdap;
        }

        $aParams = array(
            'scope' => 'sub',
            'attributes' => array('cn', 'dn', 'displayName'),
        );
        $rootDn = $oAuthenticator->sBaseDN;
        if (is_array($rootDn)) {
            $rootDn = join(",", $rootDn);
        }
        $sFilter = sprintf('(&(objectClass=group)(cn=*%s*))', $sSearch);
        $oResults = $this->oLdap->search($rootDn, $sFilter, $aParams);
        $aRet = array();
        foreach($oResults->entries() as $oEntry) {
            $aAttr = $oEntry->attributes();
            $aAttr['dn'] = $oEntry->dn();
            $aRet[] = $aAttr;
        }
        return $aRet;
    }

    function getGroup($dn) {
        if (PEAR::isError($this->oLdap)) {
            return $this->oLdap;
        }

        $oEntry = $this->oLdap->getEntry($dn, array('cn'));
        $aAttr = $oEntry->attributes();
        $aAttr['dn'] = $oEntry->dn();
        return $aAttr;
    }
}

