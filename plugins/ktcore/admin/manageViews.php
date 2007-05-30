<?php
/**
 * $Id: view.php 6584 2007-05-23 13:43:15Z kevin_fourie $
 *    
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
 */

require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/widgets/reorderdisplay.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/browse/columnregistry.inc.php');
 
class ManageViewDispatcher extends KTAdminDispatcher {

    function check() {
    
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Manage Views'));
        return parent::check();
    }

    function do_main() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/misc/columns/select_view');          
        
        $oColumnRegistry =& KTColumnRegistry::getSingleton();        
        
        $aViews = $oColumnRegistry->getViews();

        $aTemplateData = array( 
              'context' => $this, 
              'views' => $aViews,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_editView() {    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/misc/columns/edit_view');          
        
        $oColumnRegistry =& KTColumnRegistry::getSingleton();        
        $aColumns = $oColumnRegistry->getColumnsForView($_REQUEST['viewNS']);
        //var_dump($aColumns); exit(0);
        $aAllColumns = $oColumnRegistry->getColumns();

        $view_name = $oColumnRegistry->getViewName($_REQUEST['viewNS']);
        $this->oPage->setTitle($view_name);
        $this->oPage->setBreadcrumbDetails($view_name);        

        $aOptions = array();
        $vocab = array();
        foreach ($aAllColumns as $aInfo) {
            $vocab[$aInfo['namespace']] = $aInfo['name'];
        }
        $aOptions['vocab'] = $vocab;
        $add_field = new KTLookupWidget(_kt("Columns"), _kt("Select a column to add to the view.  Please note that while you can add multiple copies of a column, they will all behave as a single column"), 'column_ns', null, $this->oPage, true, null, $aErrors = null, $aOptions);

        $aTemplateData = array( 
              'context' => $this,
              'current_columns' => $aColumns,
              'all_columns' => $aAllColumns,
              'view' => $_REQUEST['viewNS'],
              'add_field' => $add_field,
        );
        return $oTemplate->render($aTemplateData);     
    } 
    
    function do_deleteEntry() { 
        $entry_id = KTUtil::arrayGet($_REQUEST, 'entry_id'); 
        $view = KTUtil::arrayGet($_REQUEST, 'viewNS');         
        
        // none of these conditions can be reached "normally".        
        
        $oEntry = KTColumnEntry::get($entry_id);
        if (PEAR::isError($oEntry)) {
            $this->errorRedirectToMain(_kt("Unable to locate the entry"));
        }
        
        if ($oEntry->getRequired()) {
            $this->errorRedirectToMain(_kt("That column is required"));
        }
        
        if ($oEntry->getViewNamespace() != $view) {
            $this->errorRedirectToMain(_kt("That column is not for the specified view"));
        }
        
        $res = $oEntry->delete();
        
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(sprintf(_kt("Failed to remove that column: %s"), $res->getMessage()));
        }        
        
        $this->successRedirectTo("editView", _kt("Deleted Entry"), sprintf("viewNS=%s", $view));
    }     
    
    function do_addEntry() {
        $column_ns = KTUtil::arrayGet($_REQUEST, 'column_ns'); 
        $view = KTUtil::arrayGet($_REQUEST, 'viewNS');      

        $this->startTransaction();        
        
        $oEntry = KTColumnEntry::createFromArray(array(
    		'ColumnNamespace' => $column_ns,
    		'ViewNamespace' => $view,
    		'Position' => 1000,             // start it at the bottom
    		'config' => array(),            // stub, for now.
    		'Required' => 0
        ));       

        $this->successRedirectTo("editView", _kt("Added Entry"), sprintf("viewNS=%s", $view));  
    }

}

?>