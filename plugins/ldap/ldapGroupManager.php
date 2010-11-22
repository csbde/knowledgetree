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
        try {
            $attributes = $this->getGroup($dn, array('member'));
        }
        catch (Exception $e) {
            // wrap in PEAR error for the rest of the system which expects that format
            return new PEAR_Error($e->getMessage());
        }
        
        $members = KTUtil::arrayGet($attributes, 'member', array());
        if (!is_array($members)) {
            $members = array($members);
        }
        
        $userIds = array();
        foreach ($members as $member) {
            $userId = User::getByAuthenticationSourceAndDetails($this->oSource, $member, array('ids' => true));
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