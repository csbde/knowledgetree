<?php

/**
 * Contains basic data for a repository object
 */

// NOTE this implementation will probably change substantially once more of the CMIS
//      functionality is in place

class CMISObjectType {
    /** @var cmisPropertiesType */
    public $properties;

    /** @var cmisObjectType[] */
    public $child;
}

?>