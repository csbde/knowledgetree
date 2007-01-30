<?php
/*
 * Created on 03 Jan 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
require_once(KT_LIB_DIR . "/plugins/plugin.inc.php");
require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php");
require_once('PDFGeneratorAction.php');

 class PDFGeneratorPlugin extends KTPlugin
 {
 	var $sNamespace = 'ktstandard.pdf.plugin';
 	
 	function PDFGeneratorPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('PDF Generator Plugin');
        return $res;
    }
    
    function setup() {
        $this->registerAction('documentaction', 'PDFGeneratorAction', 'ktstandard.pdf.action', $sFilename = null);
    }
 }
$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('PDFGeneratorPlugin', 'ktstandard.pdf.plugin', __FILE__);
?>
