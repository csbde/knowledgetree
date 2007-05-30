<?php

/**
 * $Id$
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
 *
 */

require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');

require_once(KT_LIB_DIR . '/widgets/widgetfactory.inc.php');
require_once(KT_LIB_DIR . '/validation/validatorfactory.inc.php');

class KTFieldsetDisplayRegistry {
    
    var $fieldset_types = array();
    
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTFieldsetDisplayRegistry')) {
            $GLOBALS['oKTFieldsetDisplayRegistry'] = new KTFieldsetDisplayRegistry;
        }
        return $GLOBALS['oKTFieldsetDisplayRegistry'];
    }
    // }}}


    // FIXME include a reg-class, so that lower items can ensure they require_once.
    // pass in:
    //   nsname (e.g. ktcore/subscription)
    //   classname (e.g. KTSimpleFieldset)
    function registerFieldsetDisplay($nsname, $className) {
        $this->fieldset_types[$nsname] = $className;
    }
    
    function getHandler($nsname) {
        if (!array_key_exists($nsname, $this->fieldset_types)) {
            // unfortunately, we need to do a bit more spelunking here.  
            // if its conditional, we use a different item.  ns is unsufficient.
            // 
            // FIXME this is slightly wasteful from a performance POV, though DB caching should make it OK.
            $oFieldset =& KTFieldset::getByNamespace ($nsname);
            if ($oFieldset->getIsConditional() && KTMetadataUtil::validateCompleteness($oFieldset)) {
                return 'ConditionalFieldsetDisplay';
            } else {
                return 'SimpleFieldsetDisplay';
            }
        } else {
            return $this->fieldset_types[$nsname];
        }
    }
    
    // simple function to adapt a fieldset to a form
    // passing in $oDocument will set the initial default
    // values to those attached to the document.
    //
    // since the field names are stable, there isn't really a problem here
    function formAdaptor($oFieldset, $oDocument = null) {
        $widgets = array();
        $validators = array();
        
        $oVF =& KTValidationFactory::getSingleton();
        $oWF =& KTWidgetFactory::getSingleton();        
        
        $fields =& $oFieldset->getFields();
        foreach ($fields as $oField) {
            // FIXME we probably want to use some form of factory here.
            $field_name = 'metadata_' . $oField->getId();
            if ($field->getHasLookup()) {
                // lookups
                if ($field->getHasLookupTree()) {
                    // tree                
                    // FIXME we don't handle trees yet
                    continue;
                    /*

                    $fieldTree = new MDTree();
                    $fieldTree->buildForField($field->getId());
                    $fieldTree->setActiveItem($current_value);
    	    		$fieldOptions['tree'] = $fieldTree->_evilTreeRenderer($fieldTree, $fieldName);                    
                    $oField = new KTTreeWidget($fieldLabel, $fieldDescription, $fieldName, $fieldValue, $page, $fieldRequired, null, $fieldErrors, $fieldOptions);          
                    
                    */
                } else {
                    // normal
                    
                    $widgets[] = $oWF->get('ktcore.widgets.entityselection', array(
                        'label' => $oField->getName(),
                        'name' => 'metadata_' . $oField->getId(),
                        'description' => $oField->getDescription(),
                        'vocab' => MetaData::getEnabledByDocumentField($oField),
                        'id_method' => 'getName',
                        'label_method' => 'getName',
                        'required' => $oField->getIsMandatory(),
                    ));
                    
                    if ($oField->getIsMandatory()) {
                        $validators[] = $oVF->get('ktcore.validators.required', array(
                            'test' => $field_name,
                            'basename' => 'metadata_',
                        ));
                    }
                    
                    $validators[] = $oVF->get('ktcore.validators.membership', array(
                        'test' => $field_name,
                        'output' => $field_name,
                        'basename' => $field_name,
                        'vocab' => MetaData::getEnabledValuesForField($oField),
                    ));
                }
            } else {
                $widgets[] = $oWF->get('ktcore.widgets.string', array(
                    'label' => $oField->getName(),
                    'output' => $field_name,
                ));

                if ($oField->getIsMandatory()) {
                    $validators[] = $oVF->get('ktcore.validators.required', array(
                        'test' => $field_name,
                        'basename' => $field_name,
                    ));
                }                
            }               
        }
        
        return array(
            'widgets' => $widgets,
            'validators' => $validators,
        );
    }
}

?>
