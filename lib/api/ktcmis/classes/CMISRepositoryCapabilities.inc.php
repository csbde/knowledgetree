<?php

/**
 * CMIS Repository Capabilities API class for KnowledgeTree.
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

class CMISRepositoryCapabilities {
    
    // TODO we need an enum equivalent class which can be used to define acceptable values for all which are not boolean
    
    // navigation capabilities
    protected $capabilityGetDescendants; // true/false
    protected $capabilityGetFolderTree; // true/false
    
    // object capabilities
    protected $capabilityContentStreamUpdatability; // none/anytime/pwconly
    protected $capabilityChanges; // none/objectidsonly/properties/all
    protected $capabilityRenditions; // none/read
    
    // filing capabilities
    protected $capabilityMultifiling; // true/false
    protected $capabilityUnfiling; // true/false
    protected $capabilityVersionSpecificFiling; // true/false
    
    // versioning capabilities
    protected $capabilityPWCUpdateable; // true/false
    protected $capabilityPWCSearchable; // true/false
    protected $capabilityAllVersionsSearchable; // true/false
    
    // query capabilities
    protected $capabilityQuery; // none/metadataonly/fulltextonly/bothseparate/bothcombined
    protected $capabilityJoin; // none/inneronly/innerandouter
    
    // acl capabilities
    protected $capabilityACL; // none/discover/manage

    /**
     * Set a single field value
     *
     * @param string $field
     * @param string/int $value
     * @return a collection of repository entries
     * 
     * TODO when we have the enum class in place we will need to check whether the value is of type enum and call its set function
     *      to ensure that the rules are followed
     */
    function setFieldValue($field, $value)
    {
        $this->{$field} = ($value == 'true' ? true : ($value == 'false' ? false : $value));
    }
    
    /**
     * Gets the value of the capabilityMultifiling property.
     *
     */
    public function hasCapabilityGetDescendants() {
        return $this->capabilityGetDescendants;
    }
    
    /**
     * Gets the value of the capabilityMultifiling property.
     *
     */
    public function hasCapabilityGetFolderTree() {
        return $this->capabilityGetFolderTree;
    }
    
    /**
     * Gets the value of the capabilityContentStreamUpdatability property.
     *
     * @return
     *     possible object is
     *     {@link EnumCapabilityContentStreamUpdatability }
     *
     */
    public function getCapabilityContentStreamUpdatability() {
        return $this->capabilityContentStreamUpdatability;
    }
    
    /**
     * Gets the value of the capabilityChanges property.
     *
     * @return
     *     possible object is
     *     {@link EnumCapabilityChanges }
     *
     */
    public function getCapabilityChanges() {
        return $this->capabilityChanges;
    }
    
    /**
     * Gets the value of the capabilityRenditions property.
     *
     * @return
     *     possible object is
     *     {@link EnumCapabilityRenditions }
     *
     */
    public function getCapabilityRenditions() {
        return $this->capabilityRenditions;
    }
    
    /**
     * Gets the value of the capabilityMultifiling property.
     *
     */
    public function hasCapabilityMultifiling() {
        return $this->capabilityMultifiling;
    }

    /**
     * Gets the value of the capabilityUnfiling property.
     *
     */
    public function hasCapabilityUnfiling() {
        return $this->capabilityUnfiling;
    }
    
    /**
     * Gets the value of the capabilityVersionSpecificFiling property.
     *
     */
    public function hasCapabilityVersionSpecificFiling() {
        return $this->capabilityVersionSpecificFiling;
    }

    /**
     * Gets the value of the capabilityPWCUpdateable property.
     *
     */
    public function hasCapabilityPWCUpdateable() {
        return $this->capabilityPWCUpdateable;
    }

    /**
     * Gets the value of the capabilityPWCSearchable property.
     *
     */
    public function hasCapabilityPWCSearchable() {
        return $this->capabilityPWCSearchable;
    }

    /**
     * Gets the value of the capabilityAllVersionsSearchable property.
     *
     */
    public function hasCapabilityAllVersionsSearchable() {
        return $this->capabilityAllVersionsSearchable;
    }
    
    /**
     * Gets the value of the capabilityQuery property.
     *
     * @return
     *     possible object is
     *     {@link EnumCapabilityQuery }
     *
     */
    public function getCapabilityQuery() {
        return $this->capabilityQuery;
    }

    /**
     * Gets the value of the capabilityJoin property.
     *
     * @return
     *     possible object is
     *     {@link EnumCapabilityJoin }
     *
     */
    public function getCapabilityJoin() {
        return $this->capabilityJoin;
    }

    /**
     * Gets the value of the capabilityACL property.
     *
     * @return
     *     possible object is
     *     {@link EnumCapabilityACL }
     *
     */
    public function getCapabilityACL() {
        return $this->capabilityACL;
    }

//    /**
//     * Gets the value of the any property.
//     *
//     * <p>
//     * This accessor method returns a reference to the live list,
//     * not a snapshot. Therefore any modification you make to the
//     * returned list will be present inside the JAXB object.
//     * This is why there is not a <CODE>set</CODE> method for the any property.
//     *
//     * <p>
//     * For example, to add a new item, do as follows:
//     * <pre>
//     *    getAny().add(newItem);
//     * </pre>
//     *
//     *
//     * <p>
//     * Objects of the following type(s) are allowed in the list
//     * {@link Element }
//     *
//     *
//     */
//    public List<Element> getAny() {
//        if (any == null) {
//            any = new ArrayList<Element>();
//        }
//        return $this->any;
//    }

//    /**
//     * Gets a map that contains attributes that aren't bound to any typed property on this class.
//     *
//     * <p>
//     * the map is keyed by the name of the attribute and
//     * the value is the string value of the attribute.
//     *
//     * the map returned by this method is live, and you can add new attribute
//     * by updating the map directly. Because of this design, there's no setter.
//     *
//     *
//     * @return
//     *     always non-null
//     */
//    public Map<QName, String> getOtherAttributes() {
//        return $this->otherAttributes;
//    }

}

?>
