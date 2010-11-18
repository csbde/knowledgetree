<?php
require_once('ldapManager.php');

class LdapUserManager extends LdapManager {
	
	public function LdapUserManager($oSource)
	{
		parent::LdapManager($oSource);
	}
	
    // TODO proper error returns, I suppose these will have to be PEAR errors as that's
    //      what the rest of the system expects...
    //      these error returns to replace the "return false;" statements
    public function search($filter)
    {
        $users = array();
        
        if (!empty($filter)) {
            $filter = "(cn=*$filter*)";
        }
        
        $attributes = array('cn', 'dn', 'displayName');
        // NOTE we don't need to get the base dn here:
        //      If null, it will be automatically used as set in the construction of the ldap connector.
        
        try {
            $users = $this->ldapConnector->search("(&(objectClass=user)$filter)", null, Zend_Ldap::SEARCH_SCOPE_SUB, $attributes);
        }
        catch (Exception $e) {
            // TODO logging and remove the echo statement
            echo $e->getMessage() . " [$dn]";
        }
        
        /*
        $sObjectClasses = "|";
        foreach ($this->aObjectClasses as $sObjectClass) {
            $sObjectClasses .= sprintf('(objectClass=%s)', trim($sObjectClass));
        }
        $sSearchAttributes = "|";
        foreach ($this->aSearchAttributes as $sSearchAttribute) {
            $sSearchAttributes .= sprintf('(%s=*%s*)', trim($sSearchAttribute), $sSearch);
        }
        $sFilter = !empty($sSearch) ? sprintf('(&(%s)(%s))', $sObjectClasses, $sSearchAttributes) : null;
        $default->log->debug("Search filter is: " . $sFilter);

        $oResult = $this->oLdap->search($rootDn, $sFilter, $aParams);
        if (PEAR::isError($oResult)) {
            return $oResult;
        }
        $aRet = array();
        foreach($oResult->entries() as $oEntry) {
            $aAttr = $oEntry->attributes();
            $aAttr['dn'] = $oEntry->dn();
            $aRet[] = $aAttr;
        }
		*/
        
        return $users;
    }

}

?>