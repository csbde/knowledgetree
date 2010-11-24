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
     * @param string $search
     * @return iterator object $groups
     */
    public function searchGroups($search)
    {
        $groups = array();
              
        $attributes = array('cn', 'dn', 'displayName', 'member');
        // NOTE we don't need to get the base dn here:
        //      If null, it will be automatically used as set in the construction of the ldap connector.
        
        try {
            // TODO consider adding the group object classes to the config?
            $groups = $this->ldapConnector->search("(&(|(objectClass=group)(objectClass=posixGroup))(cn=*$search*))", null, Zend_Ldap::SEARCH_SCOPE_SUB, $attributes);
        }
        catch (Exception $e) {
            // TODO logging and remove the echo statement
            // TODO return which reliably indicates to calling code whether there was an error (empty response is not enough)
            echo $e->getMessage() . " [$dn]";
        }
        
        // NOTE groups (on successful retrieval) is an iterator object and can be used with foreach() or next()
        //      on failed retrieval it will be an empty array
        return $groups;
    }
    
    public function getGroup($dn, $attributes = null, $throwOnNotFound = true)
    {
        if (empty($attributes)) {
            $attributes = array('cn');
        }

        try {
            // Third argument specifies to throw exception on error if true, else would return null
            $attributes = $this->ldapConnector->getEntry($dn, $attributes, $throwOnNotFound);
        }
        catch (Exception $e) {
            // wrap in PEAR error for the rest of the system which expects that format
            return new PEAR_Error($e->getMessage());
        }

        return $attributes;
    }
    
    public function synchroniseGroup($group)
    {
        $group =& KTUtil::getObject('Group', $group);
        $dn = $group->getAuthenticationDetails();
//        $dn = str_replace('CN=Domain Users,', '', $dn);
//        $dn = 'dom';
        $dn = 'CN=Jean-Paul Bauer,CN=Users,DC=kt-cpt,DC=internal';
        try {
            $ldapResult = $this->getGroup($dn, array('memberOf'));
        }
        catch (Exception $e) {
            // wrap in PEAR error for the rest of the system which expects that format
            return new PEAR_Error($e->getMessage());
        }
        
        $ldap = ldap_connect("ad.kt-cpt.internal");
    if ($ldap && $bind = ldap_bind($ldap, 'paul', 'pl4st1kfr0g17!')) {
        $query = ldap_search($ldap, $dn, "cn=*");
// Read all results from search
$data = ldap_get_entries($ldap, $query);

// Loop over 
for ($i=0; $i < $data['count']; $i++) {
    print_r($data[$i]['member']);
    echo "\n\n";    
}
        // ldap_search and ldap_get_entries here i guess, but how?
    }
exit;
        
        echo "LOOKING IN $dn<BR><BR>";
        $this->iterate($ldapResult);
        exit;
        
        $members = KTUtil::arrayGet($ldapResult, 'member', array());
        if (!is_array($members)) {
            $members = array($members);
        }
        
        $userIds = array();
        foreach ($members as $member) {
            $userId = User::getByAuthenticationSourceAndDetails($this->source, $member, array('ids' => true));
            if (PEAR::isError($userId)) {
                continue;
            }
            $userIds[] = $userId;
        }
        
        $group->setMembers($userIds);
        
        return null;
    }
    
    private function iterate($result)
    {
        foreach ($result as $key => $item) {
            if (is_array($item)) {
                echo "<b>$key</b><BR>";
                $this->iterate($item);
                continue;
            }
            echo "$key => $item<BR>";
        }
    }

}

?>