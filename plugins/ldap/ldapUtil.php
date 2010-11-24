<?php

class LdapUtil {
    
    public static function getConnectionOptions($source)
    {
        $config = unserialize($source->getConfig());
        $options = array(
            'host'              => $config['server'],
//            'port'              => !empty($config['port']) ? $config['port'] : ($config['tls'] ? 686 : 389),
            'port'              => 389,
            'username'          => $config['searchuser'],
            'password'          => $config['searchpwd'],
            /** according to the Zend documentation, bindRequiresDn is important 
             * when NOT using Active Directory, but it seems to work fine with AD
             */
            // TODO distinguish between openldap and active directory options, if possible
            //      see http://framework.zend.com/manual/en/zend.ldap.introduction.html
            'bindRequiresDn'    => true,
            'baseDn'            => $config['basedn'],
//            'useStartTls'       => (bool)$config['tls']
        );
        
        return $options;
    }
    
}

?>