<?php

/**
 * $Id: GeneralMetadataDashlet.php,v 1.1 2006/02/28 16:53:49 nbm Exp $
 *
 * Copyright (c) 2007 Jam Warehouse http://www.jamwarehouse.com
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
