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

require_once(KT_LIB_DIR . '/widgets/basewidget.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/browse/DocumentCollection.inc.php');

class KTCoreInfoWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.info';
    var $sTemplate = 'ktcore/forms/widgets/info';
}

class KTCoreStringWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.string';
    var $sTemplate = 'ktcore/forms/widgets/string';
}

class KTCoreHiddenWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.hidden';
    var $sTemplate = 'ktcore/forms/widgets/hidden';
}

class KTCoreFileWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.file';
    var $sTemplate = 'ktcore/forms/widgets/file';

    function wrapName($outer) {
        $this->sName = sprintf('_kt_attempt_unique_%s', $this->sName);
        // we don't have access via "wrap" when processing, so we can't actually
        // wrap.  just don't use a lot of names
    }

    function process($data){
        $tname = sprintf('_kt_attempt_unique_%s', $this->sName);
        return array($this->sBasename => $_FILES[$tname]);
    }

    function getValidators() {
        if (!$this->bAutoValidate) {
            return null;
        }

        $oVF =& KTValidatorFactory::getSingleton();
        return $oVF->get('ktcore.validators.requiredfile', array(
            'test' => sprintf('_kt_attempt_unique_%s', $this->sName),
            'basename' => $this->sBasename,
        ));
    }
}


class KTCoreTextWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.text';
    var $sTemplate = 'ktcore/forms/widgets/text';
}

class KTCoreReasonWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.reason';
    var $sTemplate = 'ktcore/forms/widgets/text';

    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }

        // FIXME make required *either* per-action property
        // FIXME or a global pref.
        $global_required_default = true;
        $this->bRequired = (KTUtil::arrayGet($aOptions, 'required', $global_required_default, false) == true);

        $this->aOptions['cols'] = KTUtil::arrayGet($aOptions, 'cols', 60);
        $this->aOptions['rows'] = KTUtil::arrayGet($aOptions, 'rows', 3);
    }
}

class KTCoreBooleanWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.boolean';
    var $sTemplate = 'ktcore/forms/widgets/boolean';

    function setDefault($mValue) {
        $this->value = ($mValue == true);
    }
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

        // have to do this here, and not in "configure" since it breaks
        // entity-select.
        $unselected = KTUtil::arrayGet($this->aOptions, 'unselected_label');
        if (!empty($unselected)) {
            // NBM:  we get really, really nasty interactions if we try merge
            // NBM:  items with numeric (but important) key values and other
            // NBM:  numerically / null keyed items
            $vocab = array();
            $vocab[] = $unselected;
            foreach ($this->aVocab as $k => $v) {
                $vocab[$k] = $v;
            }

            $this->aVocab = $vocab;

            // make sure its the selected one if there's no value specified.
            if (empty($this->value)) {
                $this->value = '0';
            }
        }

        // performance optimisation for large selected sets.
        if ($this->bMulti) {
            $this->_valuesearch = array();
            $value = (array) $this->value;
            foreach ($value as $v) {
                $this->_valuesearch[$v] = true;
            }
        }

        $aTemplateData = array(
            'context' => $this,
            'name' => $this->sName,
            'has_id' => ($this->sId !== null),
            'id' => $this->sId,
            'has_value' => ($this->value !== null),
            'value' => $this->value,
            'options' => $this->aOptions,
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


class KTDescriptorSelectionWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.descriptorselection';
    var $sTemplate = 'ktcore/forms/widgets/descriptor';

    var $aJavascript = array('resources/js/jsonlookup.js');

    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }


    }

    function getWidget() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate($this->sTemplate);

        $src_location = $this->aOptions['src'];
        $sJS = sprintf('addLoadEvent(initJSONLookup("%s", "%s"));', $this->sBasename, $src_location);


        // its bad, but that's life.
        $oPage =& $GLOBALS['main'];
        $oPage->requireJSStandalone($sJS);

        $this->aOptions['multi'] = true;

        $aTemplateData = array(
            'context' => $this,
            'label' => $this->sLabel,
            'description' => $this->sDescription,
            'name' => $this->sName,
            'required' => $this->bRequired,
            'has_id' => ($this->sId !== null),
            'id' => $this->sId,
            'has_value' => ($this->value !== null),
            'value' => $this->value,
            'has_errors' => $bHasErrors,
            'errors' => $this->aErrors,
            'short_name' => $this->sBasename,
            'options' => $this->aOptions,
        );
        return $oTemplate->render($aTemplateData);
    }
}

class KTCoreTreeMetadataWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.treemetadata';
    var $iFieldId;
    var $aCSS = array('resources/css/kt-treewidget.css');

    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }

        $this->iFieldId = KTUtil::arrayGet($aOptions, 'field_id');
        if (is_null($this->iFieldId)) {
            return PEAR::raiseError(_kt('Tree metadata fields must be associated with a particular type.'));
        }
    }

    function getWidget() {
        // very simple, general purpose passthrough.  Chances are this is sufficient,
        // just override the template being used.
        $bHasErrors = false;

        require_once(KT_LIB_DIR . '/documentmanagement/MDTree.inc');

        $fieldTree = new MDTree();
        $fieldTree->buildForField($this->iFieldId);
        $fieldTree->setActiveItem($this->value);
        return $fieldTree->_evilTreeRenderer($fieldTree, $this->sName);
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
            'context' => $this,
            'label' => $this->sLabel,
            'description' => $this->sDescription,
            'widgets' => $this->renderWidgets(),
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
            'widgets' => $this->renderWidgets(),
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
            'context' => $this,
            'label' => $this->sLabel,
            'description' => $this->sDescription,
        );
        return $oTemplate->render($aTemplateData);
    }
}


class KTCoreCollectionWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.collection';
    var $sTemplate = 'ktcore/forms/widgets/collectionframe';

    var $oCollection;
    var $sCode;

    function configure($aOptions) {
        $aOptions['broken_name'] = KTUtil::arrayGet($aOptions, 'broken_name', true, false);

        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }

        $this->oCollection = KTUtil::arrayGet($aOptions, 'collection');
        if(empty($this->oCollection)) return PEAR::raiseError(_kt('No collection specified.'));

        $this->iFolderId = KTUtil::arrayGet($aOptions, 'folder_id');
        if(empty($this->iFolderId)) return PEAR::raiseError(_kt('No initial folder specified specified.'));

        $this->aBCUrlParams = KTUtil::arrayGet($aOptions, 'bcurl_params', array());

        $this->aCols = array();
        foreach($this->oCollection->columns as $oCol) {
            $this->aCols[] = $oCol->namespace;
        }

        $this->sCode = KTUtil::randomString();
        $this->sCollection = serialize($this->oCollection);
        $_SESSION['collection_widgets'][$this->sCode] = serialize($this);

        $this->requireJSResource('resources/js/collectionframe.js');


    }

    function getTargetURL() {
        $oPluginRegistry =& KTPluginRegistry::getSingleton();
        $oPlugin =& $oPluginRegistry->getPlugin('ktcore.plugin');
        $sPath = $oPlugin->getPagePath('collection');
        $oKTConfig =& KTConfig::getSingleton();

        $sName = $this->sName;
        if (KTUtil::arrayGet($this->aOptions, 'broken_name', false)) {
            $this->sName = 'fFolderId';
        }

        $sPath = KTUtil::addQueryString($sPath, array('code'=>$this->sCode,
                                                      'fFolderId'=>$this->iFolderId,
                                                      'varname' => $sName));

        return $sPath;
    }

    function getCollection() {
        $oCR =& KTColumnRegistry::getSingleton();
        //print '<pre>';
        foreach($this->aCols as $ns) {

            $oCR->getColumn($ns);
        }
        $this->oCollection = unserialize($this->sCollection);
        return $this->oCollection;
    }
}


class KTCoreFolderCollectionWidget extends KTCoreCollectionWidget {
    var $sNamespace = 'ktcore.widgets.foldercollection';
    var $sTemplate = 'ktcore/forms/widgets/collectionframe';


    function configure($aOptions) {

        if (!isset($aOptions['value'])) {
            $aOptions['value'] = KTUtil::arrayGet($aOptions,'folder_id', 1);
        }
        $this->value = $aOptions['value'];


        $collection = new AdvancedCollection();
        $oCR =& KTColumnRegistry::getSingleton();
        $col = $oCR->getColumn('ktcore.columns.title');
        $col->setOptions(array('qs_params'=>array('fMoveCode'=>$sMoveCode,
                                                  'fFolderId'=> $this->value,
                                                  'action'=>'startMove')));
        $collection->addColumn($col);

        $qObj = new FolderBrowseQuery(KTUtil::arrayGet($aOptions,'value'));
        $collection->setQueryObject($qObj);

        $aO = $collection->getEnvironOptions();
        $collection->setOptions($aO);

        $aOptions['collection'] = $collection;
        $aOptions['broken_name'] = $false;

        return parent::configure($aOptions);
    }

    function getDefault() { return $this->value; }
    function setDefault($mValue) {
        if ($mValue != $this->value) {
            $this->oCollection->setQueryObject(new FolderBrowseQuery($mValue));
            $this->value = $mValue;
            $this->aOptions['folder_id'] = $this->value;
            $this->iFolderId = $this->value;
            $this->sCollection = serialize($this->oCollection);
            $_SESSION['collection_widgets'][$this->sCode] = serialize($this);
        }
    }
}

class KTCoreCollectionPage extends KTStandardDispatcher {

    function _generate_breadcrumbs(&$oFolder, $sCode, $aURLParams, $sName = 'fFolderId') {
        $aBreadcrumbs = array();
        $folder_path_names = $oFolder->getPathArray();
        $folder_path_ids = explode(',', $oFolder->getParentFolderIds());
        $folder_path_ids[] = $oFolder->getId();

        if (!empty($folder_path_ids) && empty($folder_path_ids[0]))
        {
			array_shift($folder_path_ids);
        }

        $oRoot = Folder::get(1);
        $folder_path_names = array_merge(array($oRoot->getName()), $folder_path_names);


        foreach (range(0, count($folder_path_ids) - 1) as $index) {
            $id = $folder_path_ids[$index];

            $aParams = kt_array_merge($aURLParams, array('fFolderId'=>$id, 'code'=>$sCode, 'varname'=>$sName));
            $url = KTUtil::addQueryString($_SERVER['PHP_SELF'], $aParams);
            $aBreadcrumbs[] = array('url' => $url, 'name' => $folder_path_names[$index]);
        }

        return $aBreadcrumbs;
    }



    function do_main() {

        $sCode = KTUtil::arrayGet($_REQUEST, 'code');
        $sName = KTUtil::arrayGet($_REQUEST, 'varname','fFolderId');
        $oWidget = unserialize($_SESSION['collection_widgets'][$sCode]);

        $oCollection = $oWidget->getCollection();

        $oFolder = Folder::get(KTUtil::arrayGet($_REQUEST, 'fFolderId', 1));
        if (PEAR::isError($oFolder)) {
            $this->errorRedirectToMain(_kt('Invalid folder selected.'));
            exit(0);
        }

        $aOptions = array('ignorepermissions' => KTBrowseUtil::inAdminMode($this->oUser, $oFolder));
        $oCollection->_queryObj->folder_id = $oFolder->getId();

        $aOptions = $oCollection->getEnvironOptions();
        $aOptions['return_url'] = KTUtil::addQueryString($_SERVER['PHP_SELF'], array('code'=>$sCode, 'varname' => $sName, 'fFolderId' => $oFolder->getId()));

        $oCollection->setOptions($aOptions);

        // add the collection code to the title column QS params

        foreach($oWidget->aCols as $ns) {
            $aColOpts = $oCollection->getColumnOptions($ns);
            $aColOpts['qs_params'] = kt_array_merge(KTUtil::arrayGet($aColOpts, 'qs_params', array()),
                                                    array('code' => $sCode, 'varname' => $sName));
            $oCollection->setColumnOptions($ns, $aColOpts);
        }

        // make the breadcrumbs
        $aBreadcrumbs = $this->_generate_breadcrumbs($oFolder, $sCode, $oWidget->aBCUrlParams, $sName);

        print KTTemplating::renderTemplate('ktcore/forms/widgets/collection',
            array(
                'collection'=> $oCollection,
                'folder' => $oFolder,
                'breadcrumbs' => $aBreadcrumbs,
                'targetname' => $sName,
            )
        );

        exit(0);
    }
}



// based on the selection widget, this carries a mapping array,
// which is converted to JSON and inserted into the output. javascript
// enforces the various relationships between conditional fields.

class KTCoreConditionalSelectionWidget extends KTCoreSelectionWidget {
    var $sNamespace = 'ktcore.widgets.conditionalselection';

    var $sIdMethod;
    var $sLabelMethod;

    var $bIsMaster;
    var $bMappings;

    function _getFieldIdForMetadataId($iMetadata) {
	$sTable = 'metadata_lookup';
	$sQuery = "SELECT document_field_id FROM " . $sTable . " WHERE id = ?";
	$aParams = array($iMetadata);

	$res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'document_field_id');
	if (PEAR::isError($res)) {
	    return false;
	}
	return $res;
    }


    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }

        $this->sIdMethod = KTUtil::arrayGet($aOptions, 'id_method', 'getId');
        $this->sLabelMethod = KTUtil::arrayGet($aOptions, 'label_method');
        if (empty($this->sLabelMethod)) {
            return PEAR::raiseError(_kt('No label method specified.'));
        }
        $existing_entities = (array) KTUtil::arrayGet($aOptions, 'existing_entities');

        if (empty($this->value)) {
            $this->value = array();
            foreach ($existing_entities as $oEntity) {
                $this->value[] = call_user_func(array(&$oEntity, $this->sIdMethod));
            }
        }

	$this->iField = KTUtil::arrayGet($aOptions, 'field');
	$this->iMasterId = KTUtil::arrayGet($aOptions, 'masterid');

	// if we're the master, we have to build the dependancy array and store it as JSON
	// also, include the javascript
	if(KTUtil::arrayGet($aOptions, 'master', false)) {
	    $this->bMaster = true;
	    $this->aJavascript = array('resources/js/conditional_selection.js');

	    $oFieldset = KTFieldset::get(KTUtil::arrayGet($aOptions, 'fieldset'));
	    $aLookups = array();
	    $aConnections = array();

	    foreach($oFieldset->getFields() as $oField) {
		$c = array();

		foreach($oField->getEnabledValues() as $oMetadata) {
		    $a = array();
		    // print '<pre>';

		    $nvals = KTMetadataUtil::getNextValuesForLookup($oMetadata->getId());
		    if($nvals) {
			foreach($nvals as $i=>$aVals) {
			    $a = array_merge($a, $aVals);

			    foreach($aVals as $id) {
			      $field = $this->_getFieldIdForMetadataId($id);
			      // print 'id ' . $id . ' is in field ' . $field . "<br/>";
			      if(!in_array($field, $c)) {
				$c[] = $field;
			      }
			    }
			}
		    }

		    $aLookups[$oMetadata->getId()] = $a;
		}
		$aConnections[$oField->getId()] = $c;
	    }

	    //exit(0);

	    $oJSON = new Services_JSON;
	    $this->sLookupsJSON = $oJSON->encode($aLookups);
	    $this->sConnectionsJSON = $oJSON->encode($aConnections);
	}


        $new_vocab = array();
        foreach ($this->aVocab as $oEntity) {
            $id = call_user_func(array(&$oEntity, $this->sIdMethod));
            $label = call_user_func(array(&$oEntity, $this->sLabelMethod));
            $new_vocab[$id] = array($label, $oEntity->getId());
        }
        $this->aVocab = $new_vocab;
    }

    function getWidget() {
        $bHasErrors = false;
        if (count($this->aErrors) != 0) { $bHasErrors = true; }

	$this->sTemplate = 'ktcore/forms/widgets/conditional_selection';

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate($this->sTemplate);

        $unselected = KTUtil::arrayGet($this->aOptions, 'unselected_label');
        if (!empty($unselected)) {
            $vocab = array();
            $vocab[] = $unselected;
            foreach ($this->aVocab as $k => $v) {
                $vocab[$k] = $v;
            }
            $this->aVocab = $vocab;
            if (empty($this->value)) {
                $this->value = '0';
            }
        }

        if ($this->bMulti) {
            $this->_valuesearch = array();
            $value = (array) $this->value;
            foreach ($value as $v) {
                $this->_valuesearch[$v] = true;
            }
        }

        $aTemplateData = array(
            'context' => $this,
            'name' => $this->sName,
            'has_id' => ($this->sId !== null),
            'id' => $this->sId,
            'has_value' => ($this->value !== null),
            'value' => $this->value,
            'options' => $this->aOptions,
            'vocab' => $this->aVocab,
	    'lookups' => $this->sLookupsJSON,
	    'connections' => $this->sConnectionsJSON,
	    'master' => $this->bMaster,
	    'masterid' => $this->iMasterId,
	    'field' => $this->iField,
        );
        return $oTemplate->render($aTemplateData);
    }
}

class KTCoreTextAreaWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.textarea';
    var $sTemplate = 'ktcore/forms/widgets/textarea';

    function configure($aOptions) {
        $res = parent::configure($aOptions);
        if (PEAR::isError($res)) {
            return $res;
        }

        // FIXME make required *either* per-action property
        // FIXME or a global pref.
        $global_required_default = true;
        $this->bRequired = (KTUtil::arrayGet($aOptions, 'required', $global_required_default, false) == true);

		// Part of the space on the mce editor is taken up by the toolbars, so make the plain text field slightly smaller (if using the default size)
        $default_rows = 20;
        if(isset($this->aOptions['field'])){
            $oField = $this->aOptions['field'];
            if(!$oField->getIsHTML()){
                $default_rows = 15;
            }
        }

        $this->aOptions['cols'] = KTUtil::arrayGet($aOptions, 'cols', 80);
        $this->aOptions['rows'] = KTUtil::arrayGet($aOptions, 'rows', $default_rows);
        $this->aOptions['field'] = KTUtil::arrayGet($aOptions, 'field');
    }

    function render() {
        // very simple, general purpose passthrough.  Chances are this is sufficient,
        // just override the template being used.
        $bHasErrors = false;       
        if (count($this->aErrors) != 0) { $bHasErrors = true; }
        //var_dump($this->aErrors);
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate('ktcore/forms/widgets/base');
		
      	$this->aJavascript[] = 'thirdpartyjs/jquery/jquery-1.3.2.js';
        $this->aJavascript[] = 'thirdpartyjs/tinymce/jscripts/tiny_mce/tiny_mce.js';
    	$this->aJavascript[] = 'resources/js/kt_tinymce_init.js';
    	
        if (!empty($this->aJavascript)) {
            // grab our inner page.
            $oPage =& $GLOBALS['main'];            
            $oPage->requireJSResources($this->aJavascript);
        }
        if (!empty($this->aCSS)) {
            // grab our inner page.
            $oPage =& $GLOBALS['main'];            
            $oPage->requireCSSResources($this->aCSS);
        }
        
        $widget_content = $this->getWidget();
        
        $aTemplateData = array(
            "context" => $this,
            "label" => $this->sLabel,
            "description" => $this->sDescription,
            "name" => $this->sName,
            "required" => $this->bRequired,
            "has_id" => ($this->sId !== null),
            "id" => $this->sId,
            "has_value" => ($this->value !== null),
            "value" => $this->value,
            "has_errors" => $bHasErrors,
            "errors" => $this->aErrors,
            "options" => $this->aOptions,
            "widget" => $widget_content,
        );
        return $oTemplate->render($aTemplateData);   
    }    
    
}

class KTCoreDateWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.date';
    var $sTemplate = 'ktcore/forms/widgets/date';

    function getValidators() {
        if (!$this->bAutoValidate) {
            return null;
        }
        $validators = parent::getValidators(); // required, etc.
		
        $oVF =& KTValidatorFactory::getSingleton();

        $val = array();
        if(!empty($validators) && !PEAR::isError($validators)) $val[] = $validators;
        $val[] = $oVF->get('ktcore.validators.date', array(
            'test' => $this->sOrigname,
            'basename' => $this->sBasename
        ));

        return $val;
    }
}

class KTCoreButtonWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.button';
    var $sTemplate = 'ktcore/forms/widgets/button';
}

class KTCoreLayerWidget extends KTWidget {
    var $sNamespace = 'ktcore.widgets.layer';
    var $sTemplate = 'ktcore/forms/widgets/layer';
}
