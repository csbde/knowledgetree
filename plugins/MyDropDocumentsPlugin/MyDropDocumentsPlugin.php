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
 
require_once(KT_LIB_DIR . "/plugins/plugin.inc.php");
require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php");

 class MyDropDocumentsPlugin extends KTPlugin
 {
	var $sNamespace = 'ktlive.mydropdocuments.plugin';
	var $autoRegister = true;
 	 	
 	function MyDropDocumentsPlugin($sFilename = null) {
 		
		$res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('My Drop Documents');
        return $res;
		
    }
    
    function setup() {
    	
		$this->registerDashlet('MyDropDocumentsDashlet', 'klive.mydropdocuments.dashlet', 'MyDropDocumentsDashlet.php');
		$this->registerPage('MyDropDocuments', 'MyDropDocumentsPage', 'MyDropDocumentsPage.php');
		
        require_once(KT_LIB_DIR . "/templating/templating.inc.php");
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('MyDropDocumentsDashlet', '/plugins/MyDropDocumentsPlugin/templates');
    }
 }
$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('MyDropDocumentsPlugin', 'ktlive.mydropdocuments.plugin', __FILE__);

?>
