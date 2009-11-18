<?php

/**
 * CMIS Repository Capabilities API class for KnowledgeTree.
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

class CMISRepositoryCapabilities {

    // boolean values
    protected $capabilityMultifiling;
    protected $capabilityUnfiling;
    protected $capabilityVersionSpecificFiling;
    protected $capabilityPWCUpdateable;
    protected $capabilityPWCSearchable;
    protected $capabilityAllVersionsSearchable;

    // non-boolean values
    // TODO these should be defined as classes/enums which will only accept the defined values when set
    protected $capabilityQuery;
    protected $capabilityFullText;
    protected $capabilityJoin;

    /**
     * Set a single field value
     *
     * @param string $field
     * @param string/int $value
     * @return a collection of repository entries
     */
    function setFieldValue($field, $value)
    {
        $this->{$field} = ($value == 'true' ? true : ($value == 'false' ? false : $value));
    }

    /**
     * Gets the value of the capabilityMultifiling property.
     *
     */
    public function hasCapabilityMultifiling() {
        return $this->capabilityMultifiling;
    }

    /**
     * Sets the value of the capabilityMultifiling property.
     *
     */
    public function setCapabilityMultifiling($value) {
        $this->capabilityMultifiling = $value;
    }

    /**
     * Gets the value of the capabilityUnfiling property.
     *
     */
    public function hasCapabilityUnfiling() {
        return $this->capabilityUnfiling;
    }

    /**
     * Sets the value of the capabilityUnfiling property.
     *
     */
    public function setCapabilityUnfiling($value) {
        $this->capabilityUnfiling = $value;
    }

    /**
     * Gets the value of the capabilityVersionSpecificFiling property.
     *
     */
    public function hasCapabilityVersionSpecificFiling() {
        return $this->capabilityVersionSpecificFiling;
    }

    /**
     * Sets the value of the capabilityVersionSpecificFiling property.
     *
     */
    public function setCapabilityVersionSpecificFiling($value) {
        $this->capabilityVersionSpecificFiling = $value;
    }

    /**
     * Gets the value of the capabilityPWCUpdateable property.
     *
     */
    public function hasCapabilityPWCUpdateable() {
        return $this->capabilityPWCUpdateable;
    }

    /**
     * Sets the value of the capabilityPWCUpdateable property.
     *
     */
    public function setCapabilityPWCUpdateable($value) {
        $this->capabilityPWCUpdateable = $value;
    }

    /**
     * Gets the value of the capabilityPWCSearchable property.
     *
     */
    public function hasCapabilityPWCSearchable() {
        return $this->capabilityPWCSearchable;
    }

    /**
     * Sets the value of the capabilityPWCSearchable property.
     *
     */
    public function setCapabilityPWCSearchable($value) {
        $this->capabilityPWCSearchable = $value;
    }

    /**
     * Gets the value of the capabilityAllVersionsSearchable property.
     *
     */
    public function hasCapabilityAllVersionsSearchable() {
        return $this->capabilityAllVersionsSearchable;
    }

    /**
     * Sets the value of the capabilityAllVersionsSearchable property.
     *
     */
    public function setCapabilityAllVersionsSearchable($value) {
        $this->capabilityAllVersionsSearchable = $value;
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
     * Sets the value of the capabilityQuery property.
     *
     * @param value
     *     allowed object is
     *     {@link EnumCapabilityQuery }
     *
     */
    public function setCapabilityQuery($value) {
        $this->capabilityQuery = $value;
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
     * Sets the value of the capabilityJoin property.
     *
     * @param value
     *     allowed object is
     *     {@link EnumCapabilityJoin }
     *
     */
    public function setCapabilityJoin($value) {
        $this->capabilityJoin = $value;
    }

    /**
     * Gets the value of the capabilityFullText property.
     *
     * @return
     *     possible object is
     *     {@link EnumCapabilityFullText }
     *
     */
    public function getCapabilityFullText() {
        return $this->capabilityFullText;
    }

    /**
     * Sets the value of the capabilityFullText property.
     *
     * @param value
     *     allowed object is
     *     {@link EnumCapabilityFullText }
     *
     */
    public function setCapabilityFullText($value) {
        $this->capabilityFullText = $value;
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
