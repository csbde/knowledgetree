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
            if (PEAR::isError($oFieldset))
            {
                global $default;
                $default->log->error('Could not resolve Fieldset by namespace: ' . $nsname . '. Error: ' . $oFieldset->getMessage());
                return 'SimpleFieldsetDisplay';
            }
            if (is_a($oFieldset, 'KTEntityNoObjects'))
            {
                global $default;
                $default->log->error('Could not resolve Fieldset by namespace: ' . $nsname . '. Error: KTEntityNoObjects returned.');
                return 'SimpleFieldsetDisplay';
            }
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
