<?php

require_once('../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

class JavascriptTranslationDispatcher extends KTDispatcher {

    function check() {
	    if (!parent::check()) { return false; }
		
		return true;
	}

    function do_main() {
	    header('Content-Type: application/javascript; charset=UTF-8');		        
	
        $oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate("ktcore/javascript_i18n");

		return $oTemplate->render();	        
    }
}

$oD =& new JavascriptTranslationDispatcher();
$oD->dispatch();

?>