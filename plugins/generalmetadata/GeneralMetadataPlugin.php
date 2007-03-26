<?php

/**
 * $Id: GeneralMetadataPlugin.php,v 1.1 2006/02/28 16:53:49 nbm Exp $
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.knowledgetree.com/
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