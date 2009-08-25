<?php

// TODO Property Type Definitions (only done Attributes up to now)

interface CMISObject {

    /*
     * ----- Object Services -----
     */

    /**
     * Moves this filed object from one folder to another.
     * <p>
     * The target folder is that into which the object has to be moved. When the
     * object is multi-filed, a source folder to be moved out of must be
     * specified.
     *
     * @param targetFolder the target folder
     * @param sourceFolder the source folder, or {@code null}
     */
//    function move($targetFolder, $sourceFolder = null);

    /**
     * Deletes this object.
     * <p>
     * When a filed object is deleted, it is removed from all folders it is
     * filed in.
     * <p>
     * This deletes a specific version of a document object. To delete all
     * versions, use {@link #deleteAllVersions}.
     * <p>
     * Deletion of a private working copy (checked out version) is the same as
     * to cancel checkout.
     */
//    function delete();

    /**
     * Unfiles this non-folder object.
     * <p>
     * This removes this object from all folders it is filed in, but never
     * deletes the object, which means that if unfiling is not supported, an
     * exception will be thrown.
     * <p>
     * If this object is a folder then an exception will be thrown.
     *
     * @see #delete
     * @see Folder#remove
     */
//    function unfile();

    /*
     * ----- Navigation Services -----
     */

    /**
     * Gets the parent folder, or the single folder in which the object is
     * filed.
     * <p>
     * For a folder, returns the parent folder, or {@code null} if there is no
     * parent (for the root folder).
     * <p>
     * For a non-folder, if the object is single-filed then the folder in which
     * it is filed is returned, otherwise if the folder is unfiled then {@code
     * null} is returned. An exception is raised if the object is multi-filed,
     * so in doubt use {@link #getParents}.
     *
     * @return the parent folder, or {@code null}.
     *
     * @see #getParents
     * @see Folder#getAncestors
     */
//    function getParent();

//    /**
//     * Gets the direct parents of this object.
//     * <p>
//     * The object must be a non-folder, fileable object.
//     *
//     * @return the collection of parent folders
//     *
//     * @see #getParent
//     * @see Folder#getAncestors
//     */
//    function getParents();
//
//    /*
//     * ----- Relationship Services -----
//     */
//
//    /**
//     * Gets the relationships having as source or target this object.
//     * <p>
//     * Returns a list of relationships associated with this object, optionally
//     * of a specified relationship type, and optionally in a specified
//     * direction.
//     * <p>
//     * If typeId is {@code null}, returns relationships of any type.
//     * <p>
//     * Ordering is repository specific but consistent across requests.
//     *
//     * @param direction the direction of relationships to include
//     * @param typeId the type ID, or {@code null}
//     * @param includeSubRelationshipTypes {@code true} if relationships of any
//     *            sub-type of typeId are to be returned as well
//     * @return the list of relationships
//     */
//    function getRelationships($direction, $typeId, $includeSubRelationshipTypes);
//
//    /*
//     * ----- Policy Services -----
//     */
//
//    /**
//     * Applies a policy to this object.
//     * <p>
//     * The object must be controllable.
//     *
//     * @param policy the policy
//     */
//    function applyPolicy(Policy policy);
//
//    /**
//     * Removes a policy from this object.
//     * <p>
//     * Removes a previously applied policy from the object. The policy is not
//     * deleted, and may still be applied to other objects.
//     * <p>
//     * The object must be controllable.
//     *
//     * @param policy the policy
//     */
//    function removePolicy(Policy policy);
//
//    /**
//     * Gets the policies applied to this object.
//     * <p>
//     * Returns the list of policy objects currently applied to the object. Only
//     * policies that are directly (explicitly) applied to the object are
//     * returned.
//     * <p>
//     * The object must be controllable.
//     */
//    function getPolicies();
//
    /*
     * ----- data access -----
     */

    /**
     * The object's type definition.
     */
//    function getType();

    /**
     * Gets a property.
     *
     * @param name the property name
     * @return the property
     */
//    function getProperty($name);

    /**
     * Gets all the properties.
     *
     * @return a map of the properties
     */
//    function getProperties();

    /**
     * Gets a property value.
     *
     * @param name the property name
     * @return the property value
     */
//    function getValue($name);

//    /**
//     * Sets a property value.
//     * <p>
//     * Setting a {@code null} value removes the property.
//     * <p>
//     * Whether the value is saved immediately or not is repository-specific, see
//     * {@link #save()}.
//     *
//     * @param name the property name
//     * @param value the property value, or {@code null}
//     */
//    function setValue($name, $value);
//
//    /**
//     * Sets several property values.
//     * <p>
//     * Setting a {@code null} value removes a property.
//     * <p>
//     * Whether the values are saved immediately or not is repository-specific,
//     * see {@link #save()}.
//     *
//     * @param values the property values
//     */
//    function setValues($values);
//
//    /**
//     * Saves the modifications done to the object through {@link #setValue},
//     * {@link #setValues} and {@link Document#setContentStream}.
//     * <p>
//     * Note that a repository is not required to wait until a {@link #save} is
//     * called to actually save the modifications, it may do so as soon as
//     * {@link #setValue} is called.
//     * <p>
//     * Calling {#link #save} is needed for objects newly created through
//     * {@link Connection#newDocument} and similar methods.
//     */
//    function save();
//
//    /*
//     * ----- convenience methods -----
//     */
//
//    function getString($name);
//
//    function getStrings($name);
//
//    function getDecimal($name);
//
//    function getDecimals($name);
//
//    function getInteger($name);
//
//    function getIntegers($name);
//
//    function getBoolean($name);
//
//    function getBooleans($name);
//
//    function getDateTime($name);
//
//    function getDateTimes($name);
//
//    function getURI($name);
//
//    function getURIs($name);
//
//    function getId($name);
//
//    function getIds($name);
//
//    function getXML($name);
//
//    function getXMLs($name);
//
//    function getHTML($name);
//
//    function getHTMLs($name);
//
//    /*
//     * ----- convenience methods for specific properties -----
//     */
//
//    function getId();
//
//    function getURI();
//
//    function getTypeId();
//
//    function getCreatedBy();
//
//    function getCreationDate();
//
//    function getLastModifiedBy();
//
//    function getLastModificationDate();
//
//    function getChangeToken();
//
//    function getName();
//
//    function isImmutable();
//
//    function isLatestVersion();
//
//    function isMajorVersion();
//
//    function isLatestMajorVersion();
//
//    function getVersionLabel();
//
//    function getVersionSeriesId();
//
//    function isVersionSeriesCheckedOut();
//
//    function getVersionSeriesCheckedOutBy();
//
//    function getVersionSeriesCheckedOutId();
//
//    function getCheckinComment();
//
//    /*
//     * ----- convenience methods for specific properties (setter) -----
//     */
//
//    function setName($name);

}

?>
