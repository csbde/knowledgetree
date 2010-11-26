<?php

/**
 * Base LDAP manager class
 */

require_once('LdapUtil.php');
require_once(KT_DIR . '/thirdparty/ZendFramework/library/Zend/Ldap.php');

class LdapManager {
    
    protected $source;
    protected $ldapConnector;
    
    /**
     * Create the connector based on the supplied authentication source
     *
     * @param object $source
     */
    public function __construct($source)
    {
    	$this->source = KTUtil::getObject('KTAuthenticationSource', $source);
        
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
     * Destroy the LDAP connector
     */
    public function __destruct()
    {
        unset($this->ldapConnector);
    }
    
}

?>