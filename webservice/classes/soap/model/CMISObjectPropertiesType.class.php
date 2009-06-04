<?php

/**
 * Contains basic data for a repository object
 */

// NOTE this implementation will probably change substantially once more of the CMIS
//      functionality is in place

class CMISObjectPropertiesType {
    /** @var cmisPropertyCollectionType */
    public $properties;

    /** @var cmisObjectType[] */
    public $child;
}

?>