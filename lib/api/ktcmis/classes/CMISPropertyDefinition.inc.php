<?php

/**
 * Defines the rules for an object property definition
 */

class CMISPropertyDefinition {
    
    /* ID */
    protected $id;
    /* String [optional] */
    protected $localName;
    /* string [optional] */
    protected $localNamespace;
    /* String */
    protected $queryName;
    /* string [optional] */
    protected $displayName;
    /* string [optional] */
    protected $description;
    /* Enum; the type of the property definition, which must be one of the allowed property types */
    protected $propertyType;
    /* Enum: single/multi; whether the property definition holds a single value or a collection of 
       (preferably, but not required) ordered values */
    protected $cardinality;
    /* Enum: readonly, readwrite, whencheckedout, oncreate; indicates under what circumstances this property may be updated */
    protected $updatability;
    /* Boolean; whether the property definition is inherited by child object-types */
    protected $inherited;
    /* Boolean;  applicable only to non-system properties, i.e. values provided by the application;
       If true, the value MUST be set when an object of this type is created;
       If a value is not provided, then the default value defined for the property MUST be set. 
       If no default value is provided and no default value is defined, the repository MUST throw an exception.
       
       This attribute is not applicable when the "updatability" attribute is “readonly”. In that case, "required" SHOULD be set to FALSE.
       
       Note: For CMIS-defined object types, the value of a system property (such as cmis:objectId, cmis:createdBy) MUST be set by 
       the repository. However, the property’s "required" attribute 474 SHOULD be FALSE because it is read-only to applications. */
    protected $required;
    /* Boolean; whether the property may be included in the WHERE part of a query */
    protected $queryable;
    /* Boolean; whether the property may appear in the ORDER BY part of a query */
    protected $orderable;
    
    
    public function __construct()
    {
        // will be implemented in the extending classes
    }
    
    /**
     * Sets the value for the property definition according to the defined rules
     *
     * @params $value the value to be set
     * @throws InvalidArgumentException if the value to be set does not satisfy all the rules
     */
    public function set($value)
    {
        // will be implemented in the extending classes
    }
    
}

?>