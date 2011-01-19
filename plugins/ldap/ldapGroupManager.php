<?php

require_once('ldapManager.php');
require_once('ldapUserManager.php');

class LdapGroupManager extends LdapManager {

    private $memberAttributes;
    private $searchAttributes;

    public function __construct($source)
    {
        parent::__construct($source);

        $defaultMemberAttributes = array('member', 'memberUid');
        $config = unserialize($this->source->getConfig());

        $memberAttributes = KTUtil::arrayGet($config, 'memberattributes');
        if (!empty($memberAttributes)) {
            if (!is_array($memberAttributes)) {
                $memberAttributes = array($memberAttributes);
            }
            $this->memberAttributes = array_unique(array_merge($memberAttributes, $defaultMemberAttributes));
        }

        $this->searchAttributes = array_merge(array('cn', 'dn', 'displayName'), $this->memberAttributes);
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Search groups, using the supplied search string
     *
     * NOTE the return value is an iterator on success, not an array.
     *
     * @param string $search
     * @return iterator object $groups
     */
    public function searchGroups($search)
    {
        $attributes = $this->searchAttributes;
        // NOTE we don't need to get the base dn here:
        //      If null, it will be automatically used as set in the construction of the ldap connector.

        try {
            // TODO consider adding the group object classes to the config?
            $groups = $this->ldapConnector->search("(&(|(objectClass=group)(objectClass=posixGroup))(cn=*$search*))", null, Zend_Ldap::SEARCH_SCOPE_SUB, $attributes);
        }
        catch (Exception $e) {
            throw new RuntimeException("There was a problem executing the search [{$e->getMessage()}]");
        }

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
            $ldapResult = $this->getGroup($dn, $this->memberAttributes);
        }
        catch (Exception $e) {
            // wrap in PEAR error for the rest of the system which expects that format
            return new PEAR_Error($e->getMessage());
        }

        foreach ($this->memberAttributes as $attribute) {
        	$members = KTUtil::arrayGet($ldapResult, strtolower($attribute), array());
            if (!empty($members)) {
                break;
            }
        }

        if (!is_array($members)) {
            $members = array($members);
        }

        $userIds = array();
        foreach ($members as $member) {
            // we need the user cn to check the authentication source, this may not always be directly returned
            if (!preg_match('/^cn=/', $member)) {
                $userManager = new LdapUserManager($this->source);
                $searchResults = $userManager->searchUsers($member);
                // There is likely a problem with the ldap server setup if there is more than one user with a matching identifier.
                // Even if not, how are we to know which
                if ($searchResults->count() == 1) {
                    $searchResults->rewind();
                    foreach ($searchResults as $key => $result) {
                        $member = $result['dn'];
                        $userId = User::getByAuthenticationSourceAndDetails($this->source, $member, array('ids' => true));
                        if (!PEAR::isError($userId)) {
                            break;
                        }
                    }
                }
            }
            else {
                $userId = User::getByAuthenticationSourceAndDetails($this->source, $member, array('ids' => true));
            }

            if (empty($userId) || PEAR::isError($userId)) {
                continue;
            }

            $userIds[] = $userId;
        }

        $group->setMembers($userIds);

        return null;
    }

}

?>