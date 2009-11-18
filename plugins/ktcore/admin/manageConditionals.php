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
 *
 */

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/metadata/fieldset.inc.php");
$sectionName = "Administration";
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");



class ManageConditionalDispatcher extends KTAdminDispatcher {
    var $ru;


    function ManageConditionalDispatcher() {
        parent::KTAdminDispatcher();
        global $default;
        $this->ru = $default->rootUrl;
        // this is not useful:  we _still_ don't chain through the right dispatcher (!)
        $this->aBreadcrumbs[] = array('url' => KTUtil::ktLink('/admin.php','documents'), 'name' => _kt('Document Metadata and Workflow Configuration'));
        $this->aBreadcrumbs[] = array('url' => KTUtil::ktLink('/admin.php','documents/fieldmanagement'), 'name' => _kt('Document Field Management'));


    }

    function do_main() {

        $aFieldsets = KTFieldset::getList("is_conditional = 1 AND disabled != true");
        $oTemplating =& KTTemplating::getSingleton();

        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/select_fieldset");
        $aTemplateData = array(
            "context" => &$this,
            "available_fieldsets" => $aFieldsets,
        );
        return $oTemplate->render($aTemplateData);
    }

    // FIXME refactor this into do_editSimple(fieldset_id);
    function do_editFieldset() {
        $fieldset_id = KTUtil::arrayGet($_REQUEST, "fieldset_id");
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/editsimple");
        /* alright:  to "do" this we need at least:
         *   1. the list of all the columns (id, name) and their available values.
         *   2. the fieldset_id.
         *  we can then render in/out.   Everything "intelligent" happens
         *  in AJAX (doing it with submits sucks arse.
         * 
         */
        
        $oFieldset =& KTFieldset::get($fieldset_id);
        $aFields =& $oFieldset->getFields();

        $this->aBreadcrumbs[] = array(
            'url' => KTUtil::ktLink('admin.php','documents/fieldmanagement','action=edit&fFieldsetId=' . $oFieldset->getId()),
            'name' => $oFieldset->getName()
        );
        $this->aBreadcrumbs[] = array(
            'url' => KTUtil::ktLink('admin.php','documents/fieldmanagement','action=manageConditional&fFieldsetId=' . $oFieldset->getId()),        
            'name' => _kt('Manage conditional fieldset'),
        );
        $this->oPage->setBreadcrumbDetails(_kt('Manage simple conditional'));
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array(
            "SELECT parent_field_id, child_field_id FROM $sTable WHERE fieldset_id = ?",
            array($oFieldset->getId())
        );
        $aFieldOrders = DBUtil::getResultArray($aQuery);        
        $aOrders = array();
        foreach ($aFieldOrders as $row) {
            $aChildren = KTUtil::arrayGet($aOrders, $row['parent_field_id'], array());
            $aChildren[] = $row['child_field_id'];
            $aOrders[$row['parent_field_id']] = $aChildren;
        } 
        
        // for useability, they can go in any order
        // but master field should be first.  beyond that 
        // it can get odd anyway. 
        
        $aKeyedFields = array();
        $aOrderedFields = array();
        $aStack = array($oFieldset->getMasterFieldId());
        
        // first, key
        foreach ($aFields as $oField) {
            $aKeyedFields[$oField->getId()] = $oField;
        }
        
        while (!empty($aStack)) {
            $iKey = array_shift($aStack);
            // this shouldn't happen, but avoid it anyway.
            if (!is_null($aKeyedFields[$iKey])) {
                $aOrderedFields[] = $aKeyedFields[$iKey];
                unset($aKeyedFields[$iKey]);
            }
            // add children to stack
            $aStack = kt_array_merge($aStack, $aOrders[$iKey]);
        }
        
        
        $aTemplateData = array(
            "context" => &$this,
            "fieldset_id" => $fieldset_id,
            "ordering" => $aOrders,
            "aFields" => $aOrderedFields,
            "iMasterFieldId" => $oFieldset->getMasterFieldId(),
        );
        return $oTemplate->render($aTemplateData);
    }
    
        // FIXME refactor this into do_editSimple(fieldset_id);
    function do_editComplexFieldset() {
        $fieldset_id = KTUtil::arrayGet($_REQUEST, "fieldset_id");
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/conditional/editcomplex");
        /* alright:  to "do" this we need at least:
         *   1. the list of all the columns (id, name) and their available values.
         *   2. the fieldset_id.
         *  we can then render in/out.   Everything "intelligent" happens
         *  in AJAX (doing it with submits sucks arse.
         * 
         *  FIXME we fake it here with nested arrays...
         */
        $oFieldset =& KTFieldset::get($fieldset_id);
        $aFields =& $oFieldset->getFields();        
        
        $this->aBreadcrumbs[] = array(
            'url' => KTUtil::ktLink('admin.php','documents/fieldmanagement','action=edit&fFieldsetId=' . $oFieldset->getId()),
            'name' => $oFieldset->getName()
        );
        $this->aBreadcrumbs[] = array(
            'url' => KTUtil::ktLink('admin.php','documents/fieldmanagement','action=manageConditional&fFieldsetId=' . $oFieldset->getId()),        
            'name' => _kt('Manage conditional fieldset'),
        );
        
        $sTable = KTUtil::getTableName('field_orders');
        $aQuery = array(
            "SELECT parent_field_id, child_field_id FROM $sTable WHERE fieldset_id = ?",
            array($oFieldset->getId())
        );
        $aFieldOrders = DBUtil::getResultArray($aQuery);        
        $aOrders = array();
        foreach ($aFieldOrders as $row) {
            $aChildren = KTUtil::arrayGet($aOrders, $row['parent_field_id'], array());
            $aChildren[] = $row['child_field_id'];
            $aOrders[$row['parent_field_id']] = $aChildren;
        } 
        

        $aKeyedFields = array();
        $aOrderedFields = array();
        $aStack = array($oFieldset->getMasterFieldId());
        
        // first, key
        foreach ($aFields as $oField) {
            $aKeyedFields[$oField->getId()] = $oField;
        }
        
        while (!empty($aStack)) {
            $iKey = array_shift($aStack);
            // this shouldn't happen, but avoid it anyway.
            if (!is_null($aKeyedFields[$iKey])) {
                $aOrderedFields[] = $aKeyedFields[$iKey];
                unset($aKeyedFields[$iKey]);
            }
            // add children to stack
            $aStack = kt_array_merge($aStack, $aOrders[$iKey]);
        }        
        
        $this->oPage->setBreadcrumbDetails(_kt('Manage complex conditional'));
        $aTemplateData = array(
            "context" => &$this,
            "fieldset_id" => $fieldset_id,
            "ordering" => $aOrders,
            "aFields" => $aOrderedFields,
            "iMasterFieldId" => $oFieldset->getMasterFieldId(),
        );
        return $oTemplate->render($aTemplateData);
    }
}

$oDispatcher = new ManageConditionalDispatcher();
$oDispatcher->dispatch();

?>
