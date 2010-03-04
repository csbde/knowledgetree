<?php
/**
 * CMIS Repository Base Object API class for KnowledgeTree.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 */

/**
 *
 * @copyright 2008-2010, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KTCMIS
 * @version Version 0.1
 */

// NOTE designation "opaque" means the value may not be changed

// TODO consider attributes as a class similar to property definitions, in order to more easily extract without having 
//      to have attribute specific code

abstract class CMISObject {

    protected $id; // ID (opaque); identifies the object-type in the repository
    protected $localName; // String (opaque, optional); local name for object-type - need not be set
    protected $localNamespace; // String (opaque, optional); optional local namespace for object-type - need not be set
    // NOTE queryName should not contain characters that negatively interact with BNF grammar
    protected $queryName; // String (opaque); used for query and filter operations on object-types
    protected $displayName; // String (optional); used for presentation by application
    protected $baseId; // Enum; indicates base type
    protected $parentId; // ID; id of immediate parent type; must be "not set" for a base type (Document, Folder, Relationship, Policy)
    protected $description; // String (optional); used for presentation by application
    protected $creatable; // Boolean; indicates whether new objects of this type may be created
    protected $fileable; // Boolean; indicates whether objects of this type are fileable
    protected $queryable; // Boolean; indicates whether this object-type can appear inthe FROM clause of a query statement
    protected $controllablePolicy; // Boolean; indicates whether objects of this type are controllable via policies
    protected $controllableACL; // Boolean; indicates whether objects of this type are controllable by ACLs
    protected $fulltextIndexed; // Boolean; indicates whether objects of this type are indexed for full-text search 
                                //          for querying via the CONTAINS() query predicate
    protected $includedInSupertypeQuery; // Boolean; indicates whether this type and sub-types appear in a query of this type's ancestor types
                                         // For example: if Invoice is a sub-type of cmis:document, if this is TRUE on Invoice then for a query 
                                         // 391 on cmis:document, instances of Invoice will be returned if they match.
    
    protected $properties; // list of property objects which define the additional properties for this object

    // TODO all we have here so far is getAttributes & getProperties
    //      add all the other methods as we go along

    public function __construct()
    {
        // set properties shared by all objects of this type
        $this->_setSharedProperties();
        
//        $propertyDef = new PropertyDefinition();
//        $this->properties[] = $propertyDef;
    }

    /**
     * Returns a listing of all attributes in an array
     *
     * @return array $attributes
     */
    public function getAttributes()
    {
        $attributes = array();

        // TODO look at how chemistry does this and implement something similar
        //      for now this is fine as we are just trying to get things up and running :)
        $attributes['id'] = $this->id;
        $attributes['localName'] = $this->localName;
        $attributes['localNamespace'] = $this->localNamespace;
        $attributes['queryName'] = $this->queryName;
        $attributes['displayName'] = $this->displayName;
        $attributes['baseId'] = $this->baseId;
        $attributes['parentId'] = $this->parentId;
        $attributes['description'] = $this->description;
        $attributes['creatable'] = $this->creatable;
        $attributes['fileable'] = $this->fileable;
        $attributes['queryable'] = $this->queryable;
        $attributes['controllablePolicy'] = $this->controllablePolicy;
        $attributes['controllableACL'] = $this->controllableACL;
        $attributes['fulltextIndexed'] = $this->fulltextIndexed;
        $attributes['includedInSupertypeQuery'] = $this->includedInSupertypeQuery;

        return $attributes;
    }

    public function getAttribute($field)
    {
        return $this->{$field};
    }

    /**
     * Sets properties for this type
     * Obeys the rules as specified in the property definitions (once implemented)
     */
    public function setProperty($field, $value)
    {
        $this->properties->setValue($field, $value);
    }

    /**
     * Sets properties for this type - internal only function which allows
     * setting of properties by object on initialisation or re-query
     *
     * This will bypass the property definition checks for updateability (once implemented)
     */
    protected function _setPropertyInternal($field, $value)
    {
        $this->properties->setValue($field, $value);
    }

    /**
     * Fetches properties for this object type
     */
    public function getProperties()
    {
        return $this->properties;
    }

    public function getProperty($property)
    {
        return $this->properties->getValue($property);
    }

    public function reload($objectId)
    {
        $this->_get($objectId);
    }

    protected function _get($objectId)
    {
        // override in child classes
    }
    
    /**
     * Sets properties which are shared between all objects of this type
     */
    protected function _setSharedProperties()
    {
        $this->_setPropertyInternal('objectTypeId', strtolower($this->getAttribute('id')));
        // Needed to distinguish type
        $this->_setPropertyInternal('baseTypeId', strtolower($this->getAttribute('id')));
    }

}

?>
