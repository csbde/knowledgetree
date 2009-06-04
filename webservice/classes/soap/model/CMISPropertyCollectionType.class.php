<?php

/**
 * Contains property data for a repository object
 *
 * NOTE this is a temporary measure until we get properties implement properly from the webservices point of view;
 *      no time right now to take the riskj of breaking substantial amounts of functionality...
 */

class CMISPropertyCollectionType {
    /** @var string */
    public $objectId;

    /** @var string */
    public $URI;

    /** @var string */
    public $typeId;

    /** @var string */
    public $createdBy;

    /** @var string */
    public $creationDate;

    /** @var string */
    public $lastModifiedBy;

    /** @var string */
    public $lastModificationDate;

    /** @var string */
    public $changeToken;
}

?>