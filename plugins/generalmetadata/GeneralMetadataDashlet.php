<?php

/*
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */


require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');

class GeneralMetadataDashlet extends KTBaseDashlet {
	var $oUser;
	var $sClass = 'ktBlock';
	
	/**
	 * Constructor method
	 *
	 * @return GeneralMetadataDashlet
	 */
	function GeneralMetadataDashlet(){
		$this->sTitle = _kt('General Metadata Search');
	}
	
	/**
	 * Check to see if user is active
	 *
	 * @param object $oUser
	 * @return boolean
	 */
	function is_active($oUser) {
		$this->oUser = $oUser;
		return true;
	}

	/**
	 * Render function for template
	 *
	 * @return unknown
	 */
	function render() {
		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('GeneralMetadata/dashlet');
		
		$oRegistry =& KTPluginRegistry::getSingleton();
		$oPlugin =& $oRegistry->getPlugin('ktcore.generalmetadata.plugin');
		$url = $oPlugin->getPagePath('GeneralMetadataPage');

		$aTemplateData = array(
			'context' => $this,
			'url' => $url,
		);
		return $oTemplate->render($aTemplateData);
    }    
}
?>
