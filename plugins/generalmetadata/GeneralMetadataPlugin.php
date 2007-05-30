<?php

/*
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */
 
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once('GeneralMetadataPage.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

 /**
  * Tag Cloud Plugin class
  *
  */
 class GeneralMetadataPlugin extends KTPlugin{

 	var $sNamespace = 'ktcore.generalmetadata.plugin';
 	
 	/**
 	 * Constructor method for plugin
 	 *
 	 * @param string $sFilename
 	 * @return GeneralMetadataPlugin
 	 */
 	function GeneralMetadataPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('General Metadata Search Plugin');
        return $res;
    }
    
    /**
     * Setup function for plugin
     *
     */
    function setup() {
    	// Register plugin components
		$this->registerCriterion('GeneralMetadataCriterion', 'ktcore.criteria.generalmetadata', KT_LIB_DIR . '/browse/Criteria.inc');
		$this->registerDashlet('GeneralMetadataDashlet', 'ktcore.generalmetadata.dashlet', 'GeneralMetadataDashlet.php');
		$this->registerPage('GeneralMetadataPage', 'GeneralMetadataPage', __FILE__);
		
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('General Metadata Search', '/plugins/generalmetadata/templates');
    }
 }
$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('GeneralMetadataPlugin', 'ktcore.generalmetadata.plugin', __FILE__);