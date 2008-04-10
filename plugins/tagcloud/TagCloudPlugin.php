<?php

/*
 * $Id$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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
 *
 */

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once('TagCloudRedirectPage.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

 /**
  * Tag Cloud Plugin class
  *
  */
 class TagCloudPlugin extends KTPlugin{

 	var $sNamespace = 'ktcore.tagcloud.plugin';
 	var $iVersion = 1;

 	/**
 	 * Constructor method for plugin
 	 *
 	 * @param string $sFilename
 	 * @return TagCloudPlugin
 	 */
 	function TagCloudPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Tag Cloud Plugin');
        return $res;
    }

    /**
     * Setup function for plugin
     *
     */
    function setup() {
    	// Register plugin components
		$this->registerCriterion('TagCloudCriterion', 'ktcore.criteria.tagcloud', KT_LIB_DIR . '/browse/Criteria.inc');
		$this->registerDashlet('TagCloudDashlet', 'ktcore.tagcloud.feed.dashlet', 'TagCloudDashlet.php');
		$this->registerPage('TagCloudRedirection', 'TagCloudRedirectPage', __FILE__);
		$this->registerPortlet(array(), 'TagCloudPortlet', 'tagcloud.portlet', 'TagCloudPortlet.php');


        // Check if the tagcloud fielset entry exists, if not, create it
        if(!TagCloudPlugin::tagFieldsetExists()){
        	$oFieldset = TagCloudPlugin::createFieldset();
        	if (PEAR::isError($oFieldset)) {
	            return false;
	        }
	        if($oFieldset){
	        	// make the fieldset id viewable
	        	$iFieldsetId = $oFieldset->iId;
	        }
        }else{ // if the entry exists, make the fieldset id viewable anyway
        	$iFieldsetId = TagCloudPlugin::tagFieldsetExists();
        }

        // Check if the tagcloud document field entry exists, if not, create it
        if(!TagCloudPlugin::tagFieldExists()){
        	$oField = TagCloudPlugin::createDocumentField($iFieldsetId);
        	if (PEAR::isError($oField)) {
	            return false;
	        }
        }

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('Tag Cloud Plugin', '/plugins/tagcloud/templates');
    }

    /**
     * function to add fieldset entry to fieldsets table
     *
     * @return unknown
     */
    function createFieldset(){
    	// create the fieldsets entry
    	$oFieldset = KTFieldset::createFromArray(array(
            'name' => 'Tag Cloud',
	    	'description' => 'Tag Cloud',
            'namespace' => 'tagcloud',
            'mandatory' => false,
	    	'isConditional' => false,
            'isGeneric' => true,
            'isComplete' => false,
            'isComplex' => false,
            'isSystem' => false,
        ));

		return $oFieldset;
    }

    /**
     * function to add the tagcloud entry to the document_fields table
     *
     * @param int $parentId
     * @return int $id
     */
    function createDocumentField($parentId){
    	// create the document_field entry
    	$id = DocumentField::createFromArray(array(
            'Name' => 'Tag',
            'Description' => 'Tag Words',
            'DataType' => 'STRING',
            'IsGeneric' => false,
            'HasLookup' => false,
            'HasLookupTree' => false,
            'ParentFieldset' => $parentId,
            'IsMandatory' => false,
        ));

		return $id;
    }

    /**
     * function to check if the Tag field exists in the document_fields table
     *
     * @return boolean
     */
    function tagFieldExists(){
    	$sQuery = 'SELECT df.id AS id FROM document_fields AS df ' .
				'WHERE df.name = \'Tag\'';
		$sTag = DBUtil::getOneResultKey(array($sQuery), 'id');

        if (PEAR::isError($sTag)) {
            // XXX: log error
            return false;

        }
        if(!is_null($sTag)){
        	return $sTag;
        }else{
        	return false;
        }
    }

    /**
     * function to check if the fieldset exists in the database
     *
     * @return boolean
     */
    function tagFieldsetExists(){
    	$sQuery = 'SELECT fs.id AS id FROM fieldsets AS fs '.
    			'WHERE namespace = \'tagcloud\'';
		$iFieldset = DBUtil::getOneResultKey(array($sQuery), 'id');

        if (PEAR::isError($iFieldset)) {
            // XXX: log error
            return false;

        }
        if(!is_null($iFieldset)){
        	return $iFieldset;
        }else{
        	return false;
        }
    }
 }
$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('TagCloudPlugin', 'ktcore.tagcloud.plugin', __FILE__);

?>
