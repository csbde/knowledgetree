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

class LdapAuthProvider extends KTAuthenticationProvider {
    
    public $sName = 'LDAP Authentication Provider';
    public $sNamespace = 'ldap.auth.provider';
    public $sAuthClass = 'LdapAuthenticator';
    public $oLDAPUser = null;
    public $bGroupSource = true;
    private $configMap;
    private $defaultSearchAttributes = array('cn', 'mail', 'sAMAccountName');
    private $defaultObjectClasses = array('user', 'inetOrgPerson', 'posixAccount');
    
    public function __construct()
    {
        parent::KTAuthenticationProvider();
        $this->configMap = array(
            'server' => _kt('LDAP Server Address'),
            'basedn' => _kt('Base DN'),
            'searchuser' => _kt('Search User'),
            'searchpwd' => _kt('Search Password'),
            'searchattributes' => _kt('Search Attributes'),
            'objectclasses' => _kt('Object Classes')
        );
    }
    
    /**
     * Runs the appropriate function from either this class or the designated dispatcher
     *
     * @return unknown Output depends on the function being called
     */
    public function do_main()
    {
        $event = strip_tags($_REQUEST[$this->event_var]);
        $proposedMethod = sprintf('%s_%s', $this->action_prefix, $event);
        
        // attempt to map to an event in one of the associated classes if there is
        // no method available in the current class.
        if (method_exists($this, $proposedMethod)) {
            return $this->$proposedMethod;
        }
        
        // attempt to determine whether the function is to be found in the user or group dispatcher
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
        
        // if we have manager to find a dispatcher for the requested method
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

        $sourceId = $_REQUEST['source_id'];
        $source = KTAuthenticationSource::get($sourceId);
        $config = unserialize($source->getConfig());
        $fields = $this->get_form($config);

        $oTemplate = $this->oValidator->validateTemplate('ldap_config');
        $aTemplateData = array(
            'context' => &$this,
            'fields' => $fields,
            'source_id' => $sourceId,
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
        $sourceId = $_REQUEST['source_id'];
        $source = KTAuthenticationSource::get($sourceId);
        $config = unserialize($source->getConfig());

        $config['server'] = $_REQUEST['server'];
        $config['basedn'] = $_REQUEST['basedn'];
        $config['searchuser'] = $_REQUEST['searchuser'];
        $config['searchpwd'] = $_REQUEST['searchpwd'];
        $config['searchattributes'] = KTUtil::arrayGet($config, 'searchattributes', $this->defaultSearchAttributes);
        $config['objectclasses'] = KTUtil::arrayGet($config, 'objectclasses', $this->defaultObjectClasses);
        // TLS is forced on, not user configurable
        $config['tls'] = true;
        $config['port'] = !empty($config['port']) ? $config['port'] : 389; // in case port is empty
        
        foreach ($this->configMap as $k => $v) {
            $value = KTUtil::arrayGet($_REQUEST, $k . '_nls');
            if ($value) {
                $nls_array = split("\n", $value);
                
                $final_array = array();
                foreach ($nls_array as $nls_item) {
                    $nls_item = trim($nls_item);
                    if (empty($nls_item)) {
                        continue;
                    }
                    $final_array[] = $nls_item;
                }
                
                $config[$k] = $final_array;
                continue;
            }
            
            if (array_key_exists($k . '_bool', $_REQUEST)) {
                if ($_REQUEST[$k . '_bool']) {
                    $config[$k] = true;
                }
                else {
                    $config[$k] = false;
                }
                continue;
            }
            
            $value = KTUtil::arrayGet($_REQUEST, $k);
            if ($value) {
                $config[$k] = $value;
            }
        }

        if (!empty($config)) {
            $source->setConfig(serialize($config));
            $res = $source->update();
        }

        // store any data entered into the fields
        // when redirected to the do_editSourceProvider function above the $source object will
        // now contain the information entered by the user.
        if ($this->bTransactionStarted) {
            $this->commitTransaction();
        }

        $errorOptions = array(
            'redirect_to' => array('editSourceProvider', sprintf('source_id=%d', $source->getId())),
        );
        $errorOptions['message'] = _kt("A server name or ip address is required");
        $name = $this->oValidator->validateString($config['server'], $errorOptions);

        $errorOptions['message'] = _kt("A Base DN is required for importing users");
        $name = $this->oValidator->validateString($config['basedn'], $errorOptions);

        $errorOptions['message'] = _kt("A search user is required for importing users");
        $name = $this->oValidator->validateString($config['searchuser'], $errorOptions);

        $errorOptions['message'] = _kt("A password is required for the search user");
        $name = $this->oValidator->validateString($config['searchpwd'], $errorOptions);

        $errorOptions['message'] = _kt("At least one search attribute is required for searching");
        $name = $this->oValidator->validateString($config['searchattributes'], $errorOptions);

        $errorOptions['message'] = _kt("At least one object class is required for searching");
        $name = $this->oValidator->validateString($config['objectclasses'], $errorOptions);

        $this->successRedirectTo('viewsource', _kt("Configuration updated"), 'source_id=' . $source->getId());
    }

    /**
     * Display the configuration
     *
     * @param KTAuthenticationSource $source
     * @return string
     */
    public function showSource($source)
    {        
        $config = unserialize($source->getConfig());

        $output = '';
        foreach ($this->configMap as $key => $item) {            
            $setting = ($key == 'searchpwd') ? '******' : (is_array($config[$key]) ? join(', ', $config[$key]) : $config[$key]);
            $output .= $item . ': ' . $setting . '<br />';
        }

        return $output;
    }

    public function getAuthenticator($source)
    {
        return new $this->sAuthClass($source);
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
        $searchAttributes = (isset($config['searchattributes'])) ? $config['searchattributes'] : $this->defaultSearchAttributes;
        $objectClasses = (isset($config['objectclasses'])) ? $config['objectclasses'] : $this->defaultObjectClasses;

        $fields = array();
        $fields[] = new KTStringWidget(_kt('Server Address'), _kt('The host name or IP address of the LDAP server'), 'server', $server, $this->oPage, true);

        $fields[] = new KTStringWidget(_kt('Base DN'), _kt('The location in the LDAP directory to start searching from (CN=Users,DC=mycorp,DC=com)'), 'basedn', $basedn, $this->oPage, true);

        $fields[] = new KTStringWidget(_kt('Search User'), _kt('The user account in the LDAP directory to perform searches in the LDAP directory as (such as CN=searchUser,CN=Users,DC=mycorp,DC=com or searchUser@mycorp.com)'), 'searchuser', $searchuser, $this->oPage, true);

        $fields[] = new KTPasswordWidget(_kt('Search Password'), _kt('The password for the user account in the LDAP directory that performs searches'), 'searchpwd', $searchpwd, $this->oPage, true);
        
        $fields[] = new KTTextWidget(_kt('Search Attributes'), _kt('The LDAP attributes to use to search for users when given their name (one per line, examples: <strong>cn</strong>, <strong>mail</strong>)'), 'searchattributes_nls', join("\n", $searchAttributes), $this->oPage, true, null, null, $aOptions);
        
        $fields[] = new KTTextWidget(_kt('Object Classes'), _kt('The LDAP object classes to search for users (one per line, example: <strong>user</strong>, <strong>inetOrgPerson</strong>, <strong>posixAccount</strong>)'), 'objectclasses_nls', join("\n", $objectClasses), $this->oPage, true, null, null, $aOptions);

        return $fields;
    }

}

require_once('LdapUtil.php');
require_once('LdapGroupManager.php');
        
class LdapAuthenticator extends Authenticator {
    
    private $source;
    private $ldapConnector;
    
    public function __construct($source)
    {        
        $this->source =& KTUtil::getObject('KTAuthenticationSource', $source);
        
        // Connect to LDAP
        $options = LdapUtil::getConnectionOptions($this->source);
        try {
            $this->ldapConnector = new Zend_Ldap($options);
        }
        catch (Exception $e) {
            global $default;
            $default->log->error("Unable to create an ldap connection: {$e->getMessage()}");
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
        if (empty($dn)) {
            return new PEAR_Error(_kt('The authentication parameters are corrupt. (authentication_detail_s1 is null)'));
        }

        // Authenticate against ldap
        try {
            $result = $this->ldapConnector->bind($dn, $sPassword);
            return true;
        }
        catch (Exception $e) {
            $default->log->error('LDAP Authentication: Failed to authenticate user: ' . $e->getMessage());
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