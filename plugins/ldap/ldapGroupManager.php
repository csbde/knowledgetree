<?php

require_once('ldapManager.php');
        
class LdapGroupManager extends LdapManager {

    public function __construct($source)
    {
        parent::__construct($source);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Search groups, using the supplied search string
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
            return new PEAR_Error("There was a problem executing the search [{$e->getMessage()}]");
        }
        
        // NOTE groups (on successful retrieval) is an iterator object and can be used with foreach() or next()
        //      on failed retrieval it will be an empty array
        return $groups;
    }
    
    /**
     * Get a group from the LDAP server
     *
     * @param string $dn
     * @param array $attributes
     * @param boolean $throwOnNotFound Whether to throw an exception if no results are found
     * @return iterator object A collection of results
     */
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
    
    /**
     * Synchronise group members from the LDAP server
     *
     * @param string $group
     * @return unknown
     */
    public function synchroniseGroup($group)
    {
        $group =& KTUtil::getObject('Group', $group);
        $dn = $group->getAuthenticationDetails();
        
        try {
            $ldapResult = $this->getGroup($dn, array('member'));
        }
        catch (Exception $e) {
            // wrap in PEAR error for the rest of the system which expects that format
            return new PEAR_Error($e->getMessage());
        }
         
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

}

?>