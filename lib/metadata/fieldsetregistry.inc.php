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

/* fun with adaptors.
 *
 *  Each fieldset type needs to handle
 *      - widget generation
 *      - validator generation
 *      - view rendering
 *      - configuration
 *      - fulltext generation
 *      - search widget/criteria
 *
 *  Very little of this is actually handled in this version.  In fact, at the
 *  moment the entire thing is faked.  This is done to make add/edit a little
 *  more sane.
 *
 *  In the long term, the "correct" approach here will be to make the actual
 *  KTFieldsetType a class, and to extend fieldset with a "config_array" and
 *  "fieldset_type_namespace" params.
 *
 *  Comments and suggestions on this are welcome.
 */

require_once(KT_LIB_DIR . "/widgets/widgetfactory.inc.php");
require_once(KT_LIB_DIR . "/validation/validatorfactory.inc.php");

class KTFieldsetRegistry {
    var $fieldsettypes;

    var $oVF;
    var $oWF;

    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTFieldsetRegistry')) {
            $GLOBALS['_KT_PLUGIN']['oKTFieldsetRegistry'] = new KTFieldsetRegistry;
        }
        return $GLOBALS['_KT_PLUGIN']['oKTFieldsetRegistry'];
    }

    function registerType($sClassname, $sNS, $sFile) {
        // stub
    }

    function formElementsForFieldset($fieldsetOrType, $sContainerName, $oDocument = null) {
        // we want to create:
        //
        //  - validators
        //  - widgets
        //
        // use $oDocument as a stub *if* its set.

    }

    
    /*
     * Returns an array of fieldIds where field type is HTML hence mce editor type
     * 
     */
    function getHtmlFields($oFieldset, $idPre = 'metadata_') {
            $fields = $oFieldset->getFields();
		$textAreaIds = array();
		
		foreach($fields as $field) {
			if ($field->getIsHtml()) {
				$textAreaIds[] = $idPre . $field->getId();
			}
		}
		
		return $textAreaIds;
    }    

    
    /*
     * Returns an array of fieldIds which belong to a generic fieldset
     * 
     */
    function getGenericFields($oFieldset, $idPre = 'metadata_') {
    	//return false if this isn't a generic fieldset
    	if (!$oFieldset->getIsGeneric()) {
    		return false;
    	}
    	
        $fields = $oFieldset->getFields();
		$fieldIds = array();
		
		foreach($fields as $field) {
			$fieldIds[] = $idPre . $field->getId();
		}
		
		return $fieldIds;
    }    
    
    
    function widgetsForFieldset($fieldsetOrType, $sContainerName, $oDocument = null) {
        // this is likely to be called repeatedly.
        if (is_null($this->oWF)) {
            $this->oWF =& KTWidgetFactory::getSingleton();
        }

        // we're going to create one of two things, here:
        //   - conditional fieldset widget
        //   - a "Fieldset" widget

        // FIXME delegate.
        $oFieldset =& $fieldsetOrType;

        $widgets = array();
        $fields = $oFieldset->getFields();

        if ($oFieldset->getIsConditional()) {
            $iMasterId = $oFieldset->getMasterFieldId();

            $oMasterField = DocumentField::get($iMasterId);
        
            $newfields = array();
            $newfields[] = $oMasterField;
            foreach($fields as $oField) {
                if($oField->getId() != $iMasterId) {
                    $newfields[] = $oField;
                }
            }
        
            foreach ($newfields as $oField) {
                $fname = 'metadata_' . $oField->getId(); 
                $value = null;
                    
                if (!is_null($oDocument)) {
                    $oFL = DocumentFieldLink::getByDocumentAndField($oDocument, $oField);
                    if (!is_null($oFL) && (!PEAR::isError($oFL))) {
                        $value = $oFL->getValue();
                    }
                }
                
                $widgets[] = $this->oWF->get('ktcore.widgets.conditionalselection', 
                         array(
                           'label' => $oField->getName(),
                           'required' => $oField->getIsMandatory(),
                           'name' => $fname,
                           'value' => $value,
                           'description' => $oField->getDescription(),
                           'vocab' => MetaData::getEnabledByDocumentField($oField),
                           'id_method' => 'getName',
                           'label_method' => 'getName',
                           'unselected_label' => _kt("No selection."),
                           'simple_select' => false,
                           'master' => ($oField->getId() == $iMasterId),
                           'masterid' => $iMasterId,
                           'fieldset' => $oFieldset->getId(),
                           'field' => $oField->getId(),
                           ));
            }
        } else {
            foreach ($fields as $oField) {

                $fname = 'metadata_' . $oField->getId();
                $value = null;

                // check if we had an old value
                if (!is_null($oDocument)) {
                    $oFL = DocumentFieldLink::getByDocumentAndField($oDocument, $oField);
                    if (!is_null($oFL) && (!PEAR::isError($oFL))) {
                        $value = $oFL->getValue();
                    }
                }

                // we have to hack in support for the hardcoded types of fields
                // handled by the "generic" widget.
                //
                // we try to make this a little more "sane"
                $type = '';
                if ($oField->getHasLookup()) {
                    if ($oField->getHasLookupTree()) {
                        $type = 'ktcore.fields.tree';
                    } else {
                        $type = 'ktcore.fields.lookup';
                    }
                } else if($oField->getHasInetLookup()) {
            $type = 'ktcore.fields.multiselect';
        } else {
                    $type = 'ktcore.fields.string';
                }
                
                if ($oField->getDataType() == 'LARGE TEXT') {
                    $type = 'ktcore.fields.largetext';                  
                } else if ($oField->getDataType() == 'DATE') {
                    $type = 'ktcore.fields.date';
                }

                if ($type == 'ktcore.fields.string') {
                    $widgets[] = $this->oWF->get('ktcore.widgets.string', array(
                        'label' => $oField->getName(),
                        'required' => $oField->getIsMandatory(),
                        'name' => $fname,
                        'value' => $value,
                        'description' => $oField->getDescription(),
                    ));
                } else if ($type == 'ktcore.fields.lookup') {
                    $widgets[] = $this->oWF->get('ktcore.widgets.entityselection', array(
                        'label' => $oField->getName(),
                        'required' => $oField->getIsMandatory(),
                        'name' => $fname,
                        'value' => $value,
                        'description' => $oField->getDescription(),
                        'vocab' => MetaData::getEnabledByDocumentField($oField),
                        'id_method' => 'getName',
                        'label_method' => 'getName',
                        'unselected_label' => _kt("No selection."),
                        'simple_select' => false,
                    ));
                } else if ($type == 'ktcore.fields.tree') {
                    $widgets[] = $this->oWF->get('ktcore.widgets.treemetadata', array(
                        'label' => $oField->getName(),
                        'required' => $oField->getIsMandatory(),
                        'name' => $fname,
                        'value' => $value,
                        'description' => $oField->getDescription(),
                        'vocab' => MetaData::getEnabledByDocumentField($oField),
                        'field_id' => $oField->getId(),
                    ));
                } else if ($type == 'ktcore.fields.largetext') {
                	                    
                    $widgets[] = $this->oWF->get('ktcore.widgets.textarea', array(
                        'label' => $oField->getName(),
                        'required' => $oField->getIsMandatory(),
                        'name' => $fname,
                    	'id' => $fname,
                        'value' => $value,
                        'description' => $oField->getDescription(),
                        'field' => $oField,
                    ));
                } else if ($type == 'ktcore.fields.date') {                 
                    $widgets[] = $this->oWF->get('ktcore.widgets.date', array(
                        'label' => $oField->getName(),
                        'required' => $oField->getIsMandatory(),
                        'name' => $fname,
                        'value' => $value,
                        'description' => $oField->getDescription(),
                    ));
                } else if ($type == 'ktcore.fields.multiselect') {
                    if($oField->getInetLookupType() == "multiwithlist") {
                        $widgets[] = $this->oWF->get('ktcore.widgets.entityselection', array(
                            'label' => $oField->getName(),
                            'required' => $oField->getIsMandatory(),
                            'name' => $fname,
                            'value' => explode(", ",$value),
                            'description' => $oField->getDescription(),
                            'vocab' => MetaData::getEnabledByDocumentField($oField),
                            'id_method' => 'getName',
                            'label_method' => 'getName',
                            'unselected_label' => false,
                            'simple_select' => false,
                            'multi'=> true,
                        ));
            } else if($oField->getInetLookupType() == "multiwithcheckboxes") {
                        $widgets[] = $this->oWF->get('ktcore.widgets.entityselection', array(
                            'label' => $oField->getName(),
                            'required' => $oField->getIsMandatory(),
                            'name' => $fname,
                            'value' => explode(", ",$value),
                            'description' => $oField->getDescription(),
                            'vocab' => MetaData::getEnabledByDocumentField($oField),
                                'field_id' => $oField->getId(),
                            'id_method' => 'getName',
                            'label_method' => 'getName',
                            'unselected_label' => false,
                            'simple_select' => true,
                            'multi'=> true,
                        ));
            } else {
                                $widgets[] = $this->oWF->get('ktcore.widgets.entityselection', array(
                            'label' => $oField->getName(),
                            'required' => $oField->getIsMandatory(),
                            'name' => $fname,
                            'value' => $value,
                            'description' => $oField->getDescription(),
                                'field' => $oField,
                            'vocab' => MetaData::getEnabledByDocumentField($oField),
                            'id_method' => 'getName',
                            'label_method' => 'getName',
                            'unselected_label' => _kt("No selection."),
                            'simple_select' => false,
                        ));
            }
                   
                } else if ($type == 'ktcore.fields.tree') {
                    $widgets[] = $this->oWF->get('ktcore.widgets.treemetadata', array(
                        'label' => $oField->getName(),
                        'required' => $oField->getIsMandatory(),
                        'name' => $fname,
                        'value' => $value,
                        'description' => $oField->getDescription(),
                        'vocab' => MetaData::getEnabledByDocumentField($oField),
                        'field_id' => $oField->getId(),
                    ));
                }
            }


        }
    
    return array($this->oWF->get('ktcore.widgets.fieldset',
                     array(
                'label' => $oFieldset->getName(),
                'description' => $oFieldset->getDescription(),
                'name' => $sContainerName,
                'widgets' => $widgets,
            )));
    
    }


    function validatorsForFieldset($fieldsetOrType, $sContainerName, $oDocument = null, $bIncludeAuto = false) {
        // this is likely to be called repeatedly.
        if (is_null($this->oVF)) {
            $this->oVF =& KTValidatorFactory::getSingleton();
        }

        // FIXME delegate.
        $oFieldset =& $fieldsetOrType;
        if ($oFieldset->getIsConditional()) {
            $validators = array();
            $fields = $oFieldset->getFields();
            
            if ($bIncludeAuto) {
                $widgets = $this->widgetsForFieldset($oFieldset, $sContainerName, $sDocument);
                $validators = kt_array_merge($validators, $widgets[0]->getValidators());
            }
                    
            foreach ($fields as $oField) {
                $fname = 'metadata_' . $oField->getId();

        // Change back to 'membership'
        $validators[] = $this->oVF->get('ktcore.validators.membership',
                        array(
                              'test' => $fname,
                              'output' => $fname,
                              'vocab' => MetaData::getEnabledValuesByDocumentField($oField),
                              'id_method' => 'getName',
                              ));
        }
        } else {
            $validators = array();
            $fields = $oFieldset->getFields();

            if ($bIncludeAuto) {
                // we need to do *all* validation
                // since we may be used outside a form.
                //
                // to prevent code duplication, we cheat and get the autovalidators
                // this makes add/edit forms marginally less efficient, but we'll deal.

                $widgets = $this->widgetsForFieldset($oFieldset, $sContainerName, $sDocument);
                $validators = kt_array_merge($validators, $widgets[0]->getValidators());
            }

            $config = KTConfig::getSingleton();
            $maxLength = $config->get('KnowledgeTree/maxMetadataLength',10240);

            foreach ($fields as $oField) {

                $type = '';
                if ($oField->getHasLookup()) {
                    if ($oField->getHasLookupTree()) {
                        $type = 'ktcore.fields.tree';
                    } else {
                        $type = 'ktcore.fields.lookup';
                    }
                } else if($oField->getHasInetLookup()) {
            $type = 'ktcore.fields.multiselect';
        } else {
                    $type = 'ktcore.fields.string';
                }

                $fname = 'metadata_' . $oField->getId();
                if ($type == 'ktcore.fields.string') {
                    $validators[] = $this->oVF->get('ktcore.validators.string',array(
                        'test' => $fname,
                        'max_length'=>$maxLength,
                        'output' => $fname,
                    ));
                } else if ($type == 'ktcore.fields.lookup') {
                    $validators[] = $this->oVF->get('ktcore.validators.membership',array(
                        'test' => $fname,
                        'output' => $fname,
                        'vocab' => MetaData::getEnabledValuesByDocumentField($oField),
                        'id_method' => 'getName',
                    ));
                } else if ($type == 'ktcore.fields.tree') {
                    // FIXME we really need to make tree entries richer
                    $validators[] = $this->oVF->get('ktcore.validators.membership',array(
                        'test' => $fname,
                        'output' => $fname,
                        'vocab' => MetaData::getEnabledValuesByDocumentField($oField),
                        'id_method' => 'getName',
                    ));
                } else if ($type == 'ktcore.fields.multiselect') {
                    if($oField->getInetLookupType() == "multiwithlist")
                    {
                        $validators[] = $this->oVF->get('ktcore.validators.membership',array(
                            'test' => $fname,
                            'output' => $fname,
                            'vocab' => MetaData::getEnabledValuesByDocumentField($oField),
                            'id_method' => 'getName',
                            'multi' => true,
                        ));
                    }
                    else if($oField->getInetLookupType() == "multiwithcheckboxes")
                    {
                        $validators[] = $this->oVF->get('ktcore.validators.membership',array(
                            'test' => $fname,
                            'output' => $fname,
                            'vocab' => MetaData::getEnabledValuesByDocumentField($oField),
                            'id_method' => 'getName',
                            'multi' => true,
                        ));
                    }
                    else
                    {
                        $validators[] = $this->oVF->get('ktcore.validators.membership',array(
                            'test' => $fname,
                            'output' => $fname,
                            'vocab' => MetaData::getEnabledValuesByDocumentField($oField),
                            'id_method' => 'getName',
                        ));
                    }
                   
                } else {
                    $validators[] = PEAR::raiseError(sprintf(_kt("Unable to deal with field: id %d"), $oField->getId()));
                }
            }

        }
    return array($this->oVF->get('ktcore.validators.fieldset',
                     array(
                'test' => $sContainerName,
                'output' => $sContainerName,
                'validators' => $validators,
            )));
        }
    }

?>
