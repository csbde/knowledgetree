<?php

require_once(KT_DIR . '/thirdparty/ZendFramework/library/Zend/Ldap.php');

class LdapManager {
    
    protected $ldapConnector;
    
    public function __construct($source)
    {
    	$source = KTUtil::getObject('KTAuthenticationSource', $source);
        $config = unserialize($source->getConfig());
        
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
    
}
?>