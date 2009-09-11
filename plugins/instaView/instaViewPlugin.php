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

$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..';
$dir = realpath($dir).DIRECTORY_SEPARATOR;
require_once($dir . 'wintools/baobabkeyutil.inc.php');

class instaViewPlugin extends KTPlugin {
    var $sNamespace = 'instaview.processor.plugin';
    var $iVersion = 0;
    var $autoRegister = true;

    function instaViewPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('InstaView Document Viewer');
        return $res;
    }

    function setup() {
    	$plugin_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
        $dir = $plugin_dir . 'instaView.php';
        $this->registerProcessor('InstaView', 'instaview.generator.processor', $dir);
        $this->registerAction('documentaction', 'instaViewLinkAction', 'instaview.processor.link', 'instaViewLinkAction.php');
         require_once(KT_LIB_DIR . '/templating/templating.inc.php');
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('InstaView', $plugin_dir.'templates', 'instaview.processor.plugin');
    }

    function run_setup() {
        // Check that the license is valid
        if (BaobabKeyUtil::getLicenseCount() < MIN_LICENSES) {
            return false;
        }
        return true;
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('instaViewPlugin', 'instaview.processor.plugin', __FILE__);
?>