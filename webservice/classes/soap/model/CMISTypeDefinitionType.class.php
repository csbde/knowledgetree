<?php

/**
 * Contains type definitions for a repository
 */

class CMISTypeDefinitionType {
    /** @var string */
	public $typeId;

    /** @var string */
	public $queryName;

    /** @var string */
	public $displayName;

    /** @var string */
	public $baseType;

    /** @var string */
	public $baseTypeQueryName;

    /** @var string */
	public $parentId;

    /** @var string */
	public $description;

    /** @var boolean */
	public $creatable;

    /** @var boolean */
	public $fileable;

    /** @var boolean */
	public $queryable;

    /** @var boolean */
	public $controllable;

    /** @var boolean */
	public $includedInSupertypeQuery;

    // worry about property definitions later...
//    /** @var cmisPropertyDefinitionType */
//	public $propertyDefinition;
    
}

?>