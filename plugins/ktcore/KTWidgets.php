<?php

require_once(KT_LIB_DIR . "/widgets/basewidget.inc.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");

class KTCoreStringWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.string';
    var $sTemplate = 'ktcore/forms/widgets/string';
}

class KTCoreFileWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.file';
    var $sTemplate = 'ktcore/forms/widgets/file';
    
    function wrapName($outer) {
        $this->sName = sprintf("_kt_attempt_unique_%s", $this->sName);
        // we don't have access via "wrap" when processing, so we can't actually
        // wrap.  just don't use a lot of names
    }
    
    function process($data){
        $tname = sprintf("_kt_attempt_unique_%s", $this->sName);
        return array($this->sBasename => $_FILES[$tname]);
    }
    
    function getValidators() {
        if (!$this->bAutoValidate) {
            return null;
        }
        
        $oVF =& KTValidatorFactory::getSingleton();        
        return $oVF->get('ktcore.validators.requiredfile', array(
            'test' => sprintf("_kt_attempt_unique_%s", $this->sName),
            'basename' => $this->sBasename,
        ));
    }
}


class KTCoreTextWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.text';
    var $sTemplate = 'ktcore/forms/widgets/text';
}


class KTCoreBooleanWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.boolean';
    var $sTemplate = 'ktcore/forms/widgets/boolean';
}

class KTCorePasswordWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.password';
    var $sTemplate = 'ktcore/forms/widgets/password';
    
    var $bConfirm = false;
    var $sConfirmDescription;
    
    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }
        
        $this->bConfirm = KTUtil::arrayGet($aOptions, 'confirm', false);
        $this->sConfirmDescription = KTUtil::arrayGet($aOptions, 'confirm_description');
    }
    
    function process($raw_data) {
        // since we're essentially a string, pass *that* out as the primary
        // but we also might want to confirm, and if so we use a private name
        $res = array();
        if ($this->bConfirm) {
            $res['_password_confirm_' . $this->sBasename] = array(
                'base' => $raw_data[$this->sBasename]['base'],
                'confirm' => $raw_data[$this->sBasename]['confirm'],                
            );
            $res[$this->sBasename] = $raw_data[$this->sBasename]['base'];
        } else {
            $res[$this->sBasename] = $raw_data[$this->sName];
        }
        return $res;
    }
    
    function getValidators() {
        if (!$this->bAutoValidate) {
            return null;   
        }
        $oVF =& KTValidatorFactory::getSingleton();

        $val = array();
        $val[] = parent::getValidators(); // required, etc.
        $val[] = $oVF->get('ktcore.validators.password', array(
            'test' => $this->sOrigname,
            'basename' => $this->sBasename
        ));
        
        return $val;
    }
}


class KTCoreSelectionWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.selection';
    
    var $bMulti = false;    // multiselection
    var $USE_SIMPLE = 10;   // point at which to switch to a dropdown/multiselect
    var $bUseSimple;    // only use checkboxes, regardless of size
    
    var $aVocab;
    
    var $sEmptyMessage;
    
    var $_valuesearch;
    
    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }
        
        $this->bUseSimple = KTUtil::arrayGet($aOptions, 'simple_select', null, false);
        $this->bMulti = KTUtil::arrayGet($aOptions, 'multi', false);
        
        $this->aVocab = (array) KTUtil::arrayGet($aOptions, 'vocab');
        $this->sEmptyMessage = KTUtil::arrayGet($aOptions, 'empty_message', 
            _kt('No options available for this field.'));
    }
    
    function getWidget() {
        // very simple, general purpose passthrough.  Chances are this is sufficient,
        // just override the template being used.
        $bHasErrors = false;       
        if (count($this->aErrors) != 0) { $bHasErrors = true; }

        // at this last moment we pick the template to use        
        $total = count($this->aVocab);
        if ($this->bUseSimple === true) {
            $this->sTemplate = 'ktcore/forms/widgets/simple_selection';        
        } else if ($this->bUseSimple === false) {
            $this->sTemplate = 'ktcore/forms/widgets/selection';        
        } else if (is_null($this->bUseSimple) && ($total <= $this->USE_SIMPLE)) {
            $this->sTemplate = 'ktcore/forms/widgets/simple_selection';
        } else {
            $this->sTemplate = 'ktcore/forms/widgets/selection';
        }
        
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate($this->sTemplate);

        // performance optimisation for large selected sets.
        if ($this->bMulti) {
            $this->_valuesearch = array();
            $value = (array) $this->value;
            foreach ($value as $v) {
                $this->_valuesearch[$v] = true;
            }
        }
        
        $aTemplateData = array(
            "context" => $this,
            "name" => $this->sName,
            "has_id" => ($this->sId !== null),
            "id" => $this->sId,
            "has_value" => ($this->value !== null),
            "value" => $this->value,
            "options" => $this->aOptions,
            'vocab' => $this->aVocab,
        );
        return $oTemplate->render($aTemplateData);
    }
    
    function selected($lookup) {
        if ($this->bMulti) {
            return $this->_valuesearch[$lookup];
        } else {
            return ($this->value == $lookup);
        }
    }
    
    function process($raw_data) {
        return array($this->sBasename => $raw_data[$this->sBasename]);
    }    
}

// this happens so often, its worth creating a util function for it
class KTCoreEntitySelectionWidget extends KTCoreSelectionWidget {
    var $sNamespace = 'ktcore.widgets.entityselection';
    
    var $sIdMethod;
    var $sLabelMethod;
    
    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) { 
            return $res;
        }
        
        // the selection widget's configure method has already setup almost
        // all the vars we need.  we have one utility here, where you pass 
        // in a list of existing entities that match the query, and we work
        // from there.
        
        $this->sIdMethod = KTUtil::arrayGet($aOptions, 'id_method', 'getId');
        $this->sLabelMethod = KTUtil::arrayGet($aOptions, 'label_method');
        if (empty($this->sLabelMethod)) {
            return PEAR::raiseError(_kt('No label method specified.'));
        }
        $existing_entities = (array) KTUtil::arrayGet($aOptions, 'existing_entities');
        
        // now we construct the "value" array from this set 
        // BUT ONLY IF WE DON'T HAVE A "VALUE" array.
        if (empty($this->value)) {
            $this->value = array();
            foreach ($existing_entities as $oEntity) {
                $this->value[] = call_user_func(array(&$oEntity, $this->sIdMethod));
            }
        }
        
        // we next walk the "vocab" array, constructing a new one based on the
        // functions passed in so far.
        $new_vocab = array();
        foreach ($this->aVocab as $oEntity) {
            $id = call_user_func(array(&$oEntity, $this->sIdMethod));
            $label = call_user_func(array(&$oEntity, $this->sLabelMethod));
            $new_vocab[$id] = $label;
        }
        $this->aVocab = $new_vocab;
    }
}

// wrap a set of fields into a core, basic one.
//
// this *also* subdivides the form data output namespace.
// to do this, it encapsulates a *large* amount of the KTWidget API
class KTCoreFieldsetWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.fieldset';

    var $_widgets;
    var $sDescription;
    var $sLabel;

    function configure($aOptions) {
        // do NOT use parent.
        $this->sLabel = KTUtil::arrayGet($aOptions, 'label');
        $this->sDescription = KTUtil::arrayGet($aOptions, 'description');    
        $this->sName = KTUtil::arrayGet($aOptions, 'name');
        $this->sBasename = $this->sName;    
        
        $aWidgets = (array) KTUtil::arrayGet($aOptions, 'widgets');
        // very similar to the one in forms.inc.php
        if (is_null($this->_oWF)) {
            $this->_oWF =& KTWidgetFactory::getSingleton();
        }
        
        $this->_widgets = array();
        // we don't want to expose the factory stuff to the user - its an
        // arbitrary distinction to the user.  Good point from NBM ;)
        foreach ($aWidgets as $aInfo) {
            if (is_null($aInfo)) {
                continue;
            } else if (is_object($aInfo)) {
                // assume this is a fully configured object
                $this->_widgets[] = $aInfo;
            } else {
                $namespaceOrObject = $aInfo[0];
                $config = (array) $aInfo[1];
                
                $this->_widgets[] = $this->_oWF->get($namespaceOrObject, $config);
            }
        }        

    }

    function render() {
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate('ktcore/forms/widgets/fieldset');
        
        $aTemplateData = array(
            "context" => $this,
            "label" => $this->sLabel,
            "description" => $this->sDescription,
            "widgets" => $this->renderWidgets(),
        );
        return $oTemplate->render($aTemplateData);   
    }

    function renderWidgets() {
        $rendered = array();

        foreach ($this->_widgets as $v) {
            if (PEAR::isError($v)) {
                $rendered[] = sprintf(_kt('<div class="ktError"><p>Unable to show widget &mdash; %s</p></div>'), $v->getMessage());
            } else {
                $rendered[] = $v->render();
            }
        }
        
        return implode(' ', $rendered);
    }

    function getDefault() { 
        // we need to do a little more admin here
        // to obtain the default
        // annoyingly
        $d = array();
        foreach ($this->_widgets as $w) {
            if (PEAR::isError($w)) {
                continue;
            }
            $d[$w->getBasename()] = $w->getDefault();
        }
        return $d;
    }
    
    function setDefault($aValue) {
        $d = (array) $aValue;
        foreach ($this->_widgets as $k => $w) {
            $oWidget =& $this->_widgets[$k];
            $oWidget->setDefault(KTUtil::arrayGet($d, $oWidget->getBasename(), $oWidget->getDefault()));
        }
    }
    
    function wrapName($sOuter) { 
        $this->sName = sprintf('%s[%s]', $sOuter, $this->sBasename);
        // now, chain to our children
        foreach ($this->_widgets as $k => $v) {
            $oWidget =& $this->_widgets[$k];
            if (PEAR::isError($oWidget)) {
                continue;
            }
            $oWidget->wrapName($this->sName);
        }
    }
    
    function setErrors($aErrors = null) {
        if (is_array($aErrors)) {
            $this->aErrors = $aErrors;
        }
        
        foreach ($this->_widgets as $k => $w) {
            $oWidget =& $this->_widgets[$k];
            $oWidget->setErrors(KTUtil::arrayGet($aErrors, $oWidget->getBasename()));
        }
    }


    function getValidators() {
        // we use a fieldsetValidator here.
        $extra_validators = array();
        
        foreach ($this->_widgets as $oWidget) {
            $res = $oWidget->getValidators();
            
            if (!is_null($res)) {
                if (is_array($res)) {
                    $extra_validators = kt_array_merge($extra_validators, $res);
                } else {
                    $extra_validators[] = $res;
                }
            }
        }
        
        $oVF =& KTValidatorFactory::getSingleton(); 
        return array($oVF->get('ktcore.validators.fieldset', array(
            'test' => $this->sBasename,        
            'validators' => &$extra_validators,
        )));
    }
    
    function process($raw_data) {
        $d = (array) KTUtil::arrayGet($raw_data, $this->sBasename);
        $o = array();
        
        // we now need to recombine the process
        foreach ($this->_widgets as $oWidget) {
            $o =& kt_array_merge($o, $oWidget->process($d));
        }
        
        return array($this->sBasename => $o);
    }

}

class KTCoreTransparentFieldsetWidget extends KTCoreFieldsetWidget {
    var $sNamespace = 'ktcore.widgets.transparentfieldset';
    
    function render() {
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate('ktcore/forms/widgets/transparent_fieldset');
        
        $aTemplateData = array(
            "widgets" => $this->renderWidgets(),
        );
        return $oTemplate->render($aTemplateData);   
    }    
}



class KTExtraConditionalFieldsetWidget extends KTCoreFieldsetWidget {
    var $sNamespace = 'ktextra.conditionalmetadata.fieldset';
    
    function render() {
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate('ktcore/forms/widgets/conditionalfieldset');
        
        $aTemplateData = array(
            "context" => $this,
            "label" => $this->sLabel,
            "description" => $this->sDescription,        
        );
        return $oTemplate->render($aTemplateData);   
    }    
}

?>
