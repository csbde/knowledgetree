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

class MyDropDocumentsDashlet extends KTBaseDashlet {
	var $oUser;
	//var $sClass = 'ktError';
	
	function MyDropDocumentsDashlet(){
		$this->sTitle = _kt('My Dropped Documents');
	}
	
	function is_active($oUser) {
		$this->oUser = $oUser;
		return true;
	}
	
	
/*	
	function is_active($oUser) {
		$this->oUser = $oUser;
		return Permission::userIsSystemAdministrator($oUser);
	}
*/
	
	function render() {
		global $main;
        $main->requireJSResource("plugins/MyDropDocumentsPlugin/js/update.js");
        
         $oPlugin =& $this->oPlugin;
		
		$oTemplating =& KTTemplating::getSingleton();
       	$oTemplate = $oTemplating->loadTemplate('MyDropDocumentsPlugin/dashlet'); 
       	       	
		$aTemplateData = array(
			'context' => $this,
			'url' => $oPlugin->getPagePath('MyDropDocuments'),
		
		);
		
      
        return $oTemplate->render($aTemplateData);
    }
}
?>