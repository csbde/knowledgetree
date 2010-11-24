<?php
require_once('ldapManager.php');

class LdapUserManager extends LdapManager {
    
    private $attributes = array('cn', 'samaccountname', 'givenname', 'sn', 'mail', 'mobile', 'userprincipalname', 'uid');
    private $objectClasses;
    private $searchAttributes;
	
	public function __construct($source, $attributes = null)
	{	    
		parent::__construct($source);
		
	    if (!empty($attributes) && is_array($attributes)) {
	        $this->attributes = $attributes;
	    }
	    
	    $config = $this->source->getConfig();
		$this->objectClasses = KTUtil::arrayGet($config, 'objectclasses');
        if (empty($this->objectClasses)) {
            $this->objectClasses = array('user', 'inetOrgPerson', 'posixAccount');
        }
        
        $this->searchAttributes = KTUtil::arrayGet($config, 'searchattributes');
        if (empty($this->searchAttributes)) {
            $this->searchAttributes = array('cn', 'samaccountname');
        }
	}
	
	public function __destruct()
	{
	    parent::__destruct();
	}
	
    // TODO proper error returns, I suppose these will have to be PEAR errors as that's
    //      what the rest of the system expects...
    //      these error returns to replace the "return false;" statements
    public function searchUsers($search)
    {
        global $default;
        
        $users = array();
        
        $attributes = array('cn', 'dn', 'displayName');
        // NOTE we don't need to get the base dn here:
        //      If null, it will be automatically used as set in the construction of the ldap connector.
        
        $objectClasses = "|";
        foreach ($this->objectClasses as $sObjectClass) {
            $objectClasses .= sprintf('(objectClass=%s)', trim($sObjectClass));
        }
        
        $searchAttributes = "|";
        foreach ($this->searchAttributes as $searchAttribute) {
            $searchAttributes .= sprintf('(%s=*%s*)', trim($searchAttribute), $search);
        }
        
        $filter = !empty($search) ? sprintf('(&(%s)(%s))', $objectClasses, $searchAttributes) : null;
        $default->log->debug("Search filter is: " . $filter);
        
        try {
            $users = $this->ldapConnector->search($filter, null, Zend_Ldap::SEARCH_SCOPE_SUB, $attributes);
        }
        catch (Exception $e) {
            // TODO logging and remove the echo statement
            echo $e->getMessage() . " [$filter]";
        }
        
        return $users;
    }
    
    public function getUser($dn, $attributes = null, $throwOnNotFound = true)
    {
        if (empty($attributes)) {
            $attributes = $this->attributes;
        }

        try {
            // Third argument specifies to throw exception on error if true, else would return null
            $attributes = $this->ldapConnector->getEntry($dn, $attributes, $throwOnNotFound);
        }
        catch (Exception $e) {
            // wrap in PEAR error for the rest of the system which expects that format
            return new PEAR_Error($e->getMessage());
        }
        
        global $default;
        foreach ($attributes as $k => $v) {
            $default->log->info("LDAP: For DN $dn, attribute $k value is " . print_r($v, true));
            if (is_array($v)) {
                $v = array_shift($v);
            }
            $attributes[strtolower($k)] = $v;
        }

        return $attributes;
    }

}

?>