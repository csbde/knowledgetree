<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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

require_once(KT_LIB_DIR . '/authentication/authenticationprovider.inc.php');
require_once(KT_LIB_DIR . '/authentication/Authenticator.inc');

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

//require_once('ldapGroupManager.php');
//

class LdapAuthProvider extends KTAuthenticationProvider {
    
    public $sName = 'LDAP Authentication Provider';
    public $sNamespace = 'ldap.auth.provider';
    public $sAuthClass = 'LdapAuthenticator';
    public $oLDAPUser = null;
    public $bGroupSource = true;
    private $configMap;

    public function __construct()
    {
        parent::KTAuthenticationProvider();
        $this->configMap = array(
            'server' => _kt('LDAP Server Address'),
            'basedn' => _kt('Base DN'),
            'searchuser' => _kt('Search User'),
            'searchpwd' => _kt('Search Password')
        );
    }
    
    public function do_main()
    {
        $event = strip_tags($_REQUEST[$this->event_var]);
        $proposedMethod = sprintf('%s_%s', $this->action_prefix, $event);
        
        // attempt to map to an event in one of the associated classes if there is
        // no method available in the current class.
        if (method_exists($this, $proposedMethod)) {
            return $this->$proposedMethod;
        }
        
        $ldapDispatcher = null;
        $checkMethod = preg_replace('/^add|create|delete|edit|update/i', '', $event);
        if (preg_match('/^user/i', $checkMethod)) {
            require_once('ldapUserDispatcher.php');
            $ldapDispatcher = new ldapUserDispatcher();
        }
        else if (preg_match('/^group/i', $checkMethod)) {
            require_once('ldapGroupDispatcher.php');
            $ldapDispatcher = new ldapGroupDispatcher();
        }
        
        if (is_object($ldapDispatcher)) {
            return $ldapDispatcher->$proposedMethod();
        }
        
        return null;
    }

    /**
     * Display form for creating / updating the ldap server configuration
     *
     * @access public
     * @return Template
     */
    public function do_editSourceProvider()
    {
        $this->oPage->setBreadcrumbDetails(_kt("Configure LDAP"));

        $iSourceId = $_REQUEST['source_id'];
        $oSource = KTAuthenticationSource::get($iSourceId);
        $config = unserialize($oSource->getConfig());

        $fields = $this->get_form($config);

        $oTemplate = $this->oValidator->validateTemplate('ldap_config');
        $aTemplateData = array(
            'context' => &$this,
            'fields' => $fields,
            'source_id' => $iSourceId,
        );
        return $oTemplate->render($aTemplateData);
    }

    /**
     * Save the entered configuration to the DB
     *
     * @access public
     */
    public function do_performEditSourceProvider()
    {
        $iSourceId = $_REQUEST['source_id'];
        $oSource = KTAuthenticationSource::get($iSourceId);
        $config = unserialize($oSource->getConfig());

        $config['server'] = $_REQUEST['server'];
        $config['basedn'] = $_REQUEST['basedn'];
        $config['searchuser'] = $_REQUEST['searchuser'];
        $config['searchpwd'] = $_REQUEST['searchpwd'];

        if (!empty($config)) {
            $oSource->setConfig(serialize($config));
            $res = $oSource->update();
        }

        // store any data entered into the fields
        // when redirected to the do_editSourceProvider function above the $oSource object will
        // now contain the information entered by the user.
        if ($this->bTransactionStarted) {
            $this->commitTransaction();
        }

        $aErrorOptions = array(
            'redirect_to' => array('editSourceProvider', sprintf('source_id=%d', $oSource->getId())),
        );
        $aErrorOptions['message'] = _kt("A server name or ip address is required");
        $sName = $this->oValidator->validateString($config['server'], $aErrorOptions);

        $aErrorOptions['message'] = _kt("A Base DN is required for importing users");
        $sName = $this->oValidator->validateString($config['basedn'], $aErrorOptions);

        $aErrorOptions['message'] = _kt("A search user is required for importing users");
        $sName = $this->oValidator->validateString($config['searchuser'], $aErrorOptions);

        $aErrorOptions['message'] = _kt("A password is required for the search user");
        $sName = $this->oValidator->validateString($config['searchpwd'], $aErrorOptions);

        $this->successRedirectTo('viewsource', _kt("Configuration updated"), 'source_id=' . $oSource->getId());
    }

    /**
     * Display the configuration
     *
     * @param KTAuthenticationSource $oSource
     * @return string
     */
    public function showSource($oSource)
    {
        $config = unserialize($oSource->getConfig());

        $output = '';
        foreach ($this->configMap as $key => $item) {
            $setting = ($key == 'searchpwd') ? '******' : $config[$key];
            $output .= $item . ': ' . $setting . '<br />';
        }

        return $output;
    }

    public function getAuthenticator($oSource)
    {
        return new $this->sAuthClass($oSource);
    }
    
    /**
     * Returns the fields to be used for the provider info
     *
     * @access private
     * @param array $config
     * @return array
     */
    private function get_form($config)
    {
        $server = (isset($config['server'])) ? $config['server'] : '';
        $basedn = (isset($config['basedn'])) ? $config['basedn'] : '';
        $searchuser = (isset($config['searchuser'])) ? $config['searchuser'] : '';
        $searchpwd = (isset($config['searchpwd'])) ? $config['searchpwd'] : '';

        $fields = array();
        $fields[] = new KTStringWidget(_kt('Server Address'), _kt('The host name or IP address of the LDAP server'), 'server', $server, $this->oPage, true);

        $fields[] = new KTStringWidget(_kt('Base DN'), _kt('The location in the LDAP directory to start searching from (CN=Users,DC=mycorp,DC=com)'), 'basedn', $basedn, $this->oPage, true);

        $fields[] = new KTStringWidget(_kt('Search User'), _kt('The user account in the LDAP directory to perform searches in the LDAP directory as (such as CN=searchUser,CN=Users,DC=mycorp,DC=com or searchUser@mycorp.com)'), 'searchuser', $searchuser, $this->oPage, true);

        $fields[] = new KTPasswordWidget(_kt('Search Password'), _kt('The password for the user account in the LDAP directory that performs searches'), 'searchpwd', $searchpwd, $this->oPage, true);

        return $fields;
    }

}

require_once('LdapGroupManager.php');
        
class LdapAuthenticator extends Authenticator {
    
    private $source;
    private $ldapConnector;
    
    public function __construct($oSource)
    {        
        $this->source =& KTUtil::getObject('KTAuthenticationSource', $oSource);
        $config = unserialize($this->source->getConfig());
        
        // Connect to LDAP
        // TODO error conditions
        $options = array(
            'host'              => $config['server'],
            'username'          => $config['searchuser'],
            'password'          => $config['searchpwd'],
            /** according to the Zend documentation, bindRequiresDn is important 
             * when NOT using Active Directory, but it seems to work fine with AD
             */
            // TODO distinguish between openldap and active directory options, if possible
            //      see http://framework.zend.com/manual/en/zend.ldap.introduction.html
            'bindRequiresDn'    => true,
            'baseDn'            => $config['basedn'],
        );

        try {
            $this->ldapConnector = new Zend_Ldap($options);
        }
        catch (Exception $e) {
            global $default;
            // use info level instead?  Still don't understand the log4php log levels and want to be sure something like this is logged
            $default->log->error("Unable to create an ldap connection: {$e->getMessage()}");
            // TODO return PEAR error? throw exception again?
        }
    }
    
    /**
     * Destroy the ldap connector
     */
    public function __destruct()
    {
        unset($this->ldapConnector);
    }

    /**
     * Authenticate the user against the LDAP directory
     *
     * @param User the user to authenticate
     * @param string the password to check
     * @return boolean true if the password is correct | false
     */
    public function checkPassword($oUser, $sPassword)
    {
        global $default;

        $dn = $oUser->getAuthenticationDetails();

        // Authenticate against ldap
        // TODO logging
        try {
            $result = $this->ldapConnector->bind($dn, $sPassword);
            return true;
        }
        catch (Exception $e) {
            return false;
        }
    }
    
    public function synchroniseGroup($group)
    {
        $manager = new LdapGroupManager($this->source);
        return $manager->synchroniseGroup($group);
    }

    public function getConnector()
    {
    	return $this->ldapConnector;
    }
}

?>