<?php

/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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