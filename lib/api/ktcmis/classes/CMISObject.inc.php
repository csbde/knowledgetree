<?php
/**
 * CMIS Repository Base Object API class for KnowledgeTree.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008,2009 KnowledgeTree Inc.
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
 *
 * @copyright 2008-2009, KnowledgeTree Inc.
 * @license GNU General Public License version 3
 * @author KnowledgeTree Team
 * @package KTCMIS
 * @version Version 0.1
 */

abstract class CMISObject {

    protected $typeId;
    protected $queryName;
    protected $displayName;
    protected $baseType;
    protected $baseTypeQueryName;
    protected $parentId;
    protected $description;
    protected $creatable;
    protected $fileable;
    protected $queryable;
    protected $includedInSupertypeQuery;
    protected $controllable; // NOTE deprecated?  part of policy objects specification, policy objects are indicated as TODO remove
    protected $contentStreamAllowed = 'notAllowed';

    protected $properties; // list of property objects which define the additional properties for this object

    // TODO all we have here so far is getAttributes & getProperties
    //      add all the other methods as we go along

    public function __construct()
    {
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
        $attributes['typeId'] = $this->typeId;
        $attributes['queryName'] = $this->queryName;
        $attributes['displayName'] = $this->displayName;
        $attributes['baseType'] = $this->baseType;
        $attributes['baseTypeQueryName'] = $this->baseTypeQueryName;
        $attributes['parentId'] = $this->parentId;
        $attributes['description'] = $this->description;
        $attributes['creatable'] = $this->creatable;
        $attributes['fileable'] = $this->fileable;
        $attributes['queryable'] = $this->queryable;
        $attributes['includedInSupertypeQuery'] = $this->includedInSupertypeQuery;
        $attributes['controllable'] = $this->includedInSupertypeQuery;

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

    public function reload($documentId)
    {
        $this->_get($documentId);
    }

    private function _get($documentId)
    {
        // override in child classes
    }

}

?>
