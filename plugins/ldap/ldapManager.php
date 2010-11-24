<?php

require_once('LdapUtil.php');
require_once(KT_DIR . '/thirdparty/ZendFramework/library/Zend/Ldap.php');

class LdapManager {
    
    protected $source;
    protected $ldapConnector;
    
    public function __construct($source)
    {
    	$this->source = KTUtil::getObject('KTAuthenticationSource', $source);
        
        // Connect to LDAP
        // TODO error conditions
        $options = LdapUtil::getConnectionOptions($this->source);
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
    
}
?>