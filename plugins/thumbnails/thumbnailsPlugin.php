<?php
/**
 * $Id: $
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class thumbnailsPlugin extends KTPlugin {
    var $sNamespace = 'thumbnails.generator.processor.plugin';
    var $iVersion = 0;
    var $autoRegister = true;

    function thumbnailsPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Thumbnail Generator');
        $this->sSQLDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR;
        return $res;
    }

    /**
     * Setup the plugin: add the processor, viewlet action and template location
     *
     */
    function setup() {
        $plugin_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $dir = $plugin_dir . 'thumbnails.php';
        $this->registerProcessor('thumbnailGenerator', 'thumbnails.generator.processor', $dir);
        $this->registerAction('documentviewlet', 'ThumbnailViewlet', 'thumbnail.viewlets', $dir);

        require_once(KT_LIB_DIR . '/templating/templating.inc.php');
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('thumbnails', $plugin_dir.'templates', 'thumbnails.generator.processor.plugin');
        
	    // check for existing config settings entry and only add if not already present
        $sql = 'SELECT id FROM `config_settings` WHERE group_name = "externalBinary" AND item = "convertPath"';
        $result = DBUtil::getOneResult($sql);
	    if(PEAR::isError($result) || empty($result)) {
	    	DBUtil::runQuery('INSERT INTO `config_settings` (group_name, display_name, description, item, value, default_value, type, options, can_edit) '
	    					. 'VALUES ("externalBinary", "convert", "The path to the ImageMagick \"convert\" binary", "convertPath", "default", "convert", '
	    					. '"string", NULL, 1);');
		}
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('thumbnailsPlugin', 'thumbnails.generator.processor.plugin', __FILE__);
?>