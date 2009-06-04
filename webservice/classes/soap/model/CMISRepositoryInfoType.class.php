<?php

/**
 * Contains information about the selected repository
 */

class CMISRepositoryInfoType {
	/** @var string */
	public $repositoryId;
	
	/** @var string */
	public $repositoryName;

    /** @var string */
    public $repositoryRelationship;

    /** @var string */
    public $repositoryDescription;

    /** @var string */
    public $vendorName;

    /** @var string */
    public $productName;

    /** @var string */
    public $productVersion;

    /** @var string */
    public $rootFolderId;

    /** @var cmisRepositoryCapabilitiesType */
    public $capabilities;

    /** @var string */
    public $cmisVersionsSupported;

    /** @var string */
    public $repositorySpecificInformation;
}

?>