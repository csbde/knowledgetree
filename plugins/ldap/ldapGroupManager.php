<?php

require_once('ldapManager.php');
        
class LdapGroupManager extends LdapManager {

    public function __construct($source)
    {
        parent::__construct($source);
    }

    /**
     * Destroy the ldap connector
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    // TODO proper error returns, I suppose these will have to be PEAR errors as that's
    //      what the rest of the system expects...
    //      these error returns to replace the "return false;" statements
    /**
     * Search groups, using the supplied filter
     *
     * @param string $filter
     * @return iterator object $groups
     */
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
        
        // NOTE groups (on successful retrieval) is an iterator object and can be used with foreach() or next()
        //      on failed retrieval it will be an empty array
        return $groups;
    }

}

?>