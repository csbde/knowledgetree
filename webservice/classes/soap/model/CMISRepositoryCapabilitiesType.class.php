<?php

/**
 * Contains information about capabilities of the selected repository
 */

class CMISRepositoryCapabilitiesType {
	/** @var boolean */
    public $capabilityMultifiling;
    
    /** @var boolean */
    public $capabilityUnfiling;
    
    /** @var boolean */
    public $capabilityVersionSpecificFiling;
    
    /** @var boolean */
    public $capabilityPWCUpdateable;
    
    /** @var boolean */
    public $capabilityPWCSearchable;
    
    /** @var boolean */
    public $capabilityAllVersionsSearchable;

    /** @var string */
    public $capabilityQuery;

    /** @var string */
    public $capabilityJoin;

    /** @var string */
    public $capabilityFullText;
}

?>