<?php

require_once(KT_DIR . '/thirdparty/ZendFramework/library/Zend/Ldap.php');
        
class LdapGroupManager {
    
    private $ldapConnector;
    
    public function __construct($oSource)
    {        
        $config = unserialize($oSource->getConfig());
        
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
    
    // TODO proper error returns, I suppose these will have to be PEAR errors as that's
    //      what the rest of the system expects...
    //      these error returns to replace the "return false;" statements
    public function searchGroups($filter)
    {
        $groups = array();
        
        if (!empty($filter)) {
            $filter = "(cn=*$filter*)";
        }
        
        $attributes = array('cn', 'dn', 'displayName');
        // NOTE we don't need to get the base dn here:
        //      If null, it will be automatically used as set in the construction of the ldap connector.
        
        try {
            $groups = $this->ldapConnector->search("(&(objectClass=group)$filter)", null, Zend_Ldap::SEARCH_SCOPE_SUB, $attributes);
        }
        catch (Exception $e) {
            // TODO logging and remove the echo statement
            echo $e->getMessage() . " [$dn]";
        }
        
        return $groups;
    }

}

?>