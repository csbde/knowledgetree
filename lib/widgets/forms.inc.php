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

/* handle basic machinery for form handling, including working with
 * widgets, sessions and validation
 */

require_once(KT_LIB_DIR . "/widgets/widgetfactory.inc.php");
require_once(KT_LIB_DIR . "/validation/validatorfactory.inc.php");

class KTForm {
    // serialisation info
    var $_kt_form_name;
    var $sIdentifier; // a simple identifier.

    // visual options
    var $sLabel;
    var $sDescription;

    // core storage options
    var $_widgets;          // what widgets get stored
    var $_validators;       // validators
    var $_submitlabel;      // what is the "submit" button called
    var $_action;           // where does the success message go
    var $_event;           // where does the success message go
    var $_extraargs;        // various extra arguments
    var $_failaction;       // should this error out, which action handles it
    var $_failurl;          // if we don't have a failaction, try this url
    var $_cancelurl;        // where do we get redirected if we cancel?
    var $bCancel;
    var $_context;
    var $_errors;
    var $_method;
    var $_noframe;

    var $_oVF;
    var $_oWF;



    // we don't use a constructor here, rather use aOptions
    function setOptions($aOptions) {
        // we grab the "context" dispatcher(ish) object here
        $context =& KTUtil::arrayGet($aOptions, 'context');
        $this->_context =& $context;

        // form identifier (namespace)
        $this->sIdentifier = KTUtil::arrayGet($aOptions, 'identifier','kt.default');
        // form name
        $this->_kt_form_name = KTUtil::arrayGet($aOptions, '_kt_form_name',
            $this->generateFormName($this->sIdentifier), false);


        // form labelling
        $this->sLabel = KTUtil::arrayGet($aOptions, 'label');
        $this->sDescription = KTUtil::arrayGet($aOptions, 'description');

        // actions
        $this->_action = KTUtil::arrayGet($aOptions, 'action');
        $qs = KTUtil::arrayGet($aOptions, 'actionparams','');
        $this->_enctype = KTUtil::arrayGet($aOptions, 'encoding');
        if (empty($this->_enctype)) {
            if (KTUtil::arrayGet($aOptions, 'file_upload', false)) {
                $this->_enctype="multipart/form-data";
            }
        }

        $targeturl = KTUtil::arrayGet($aOptions, 'targeturl', false);
        if($targeturl === false) {
            $this->_actionurl = KTUtil::addQueryStringSelf($qs);
        } else {
            $this->_actionurl = KTUtil::addQueryString($targeturl, $qs);
        }

        $this->_failaction = KTUtil::arrayGet($aOptions, 'fail_action');
        $this->_failurl = KTUtil::arrayGet($aOptions, 'fail_url');
        $this->_submitlabel = KTUtil::arrayGet($aOptions, 'submit_label',
            _kt('Submit'));

        $this->_event = KTUtil::arrayGet($aOptions, 'event');
        if (empty($this->_event)) {
            if (!is_null($context)) {
                $this->_event = $context->event_var;
            } else {
                $this->_event = "action";
            }
        }

        $this->_noframe = KTUtil::arrayGet($aOptions, 'noframe', false);

        // cancel
        // there are a few options here:
        //   1. cancel_action
        //   2. cancel_url
        $cancel_action = KTUtil::arrayGet($aOptions, 'cancel_action');
        $cancel_url = KTUtil::arrayGet($aOptions, 'cancel_url');

        if (!empty($cancel_action)) {
            $this->bCancel = true;
            // there are two cases here - if we have a context, we can
            // use the meldPersistQuery to create the url.
            if (!is_null($context)) {
                $sQuery = $context->meldPersistQuery("",
                    $cancel_action);
                $this->_cancelurl =
                    KTUtil::addQueryString($_SERVER['PHP_SELF'], $sQuery);
            } else {
                // give it a try using addQSSelf
                $this->_cancelurl = KTUtil::addQueryStringSelf(
                    sprintf('%s=%s', $this->_event, $cancel_action));
            }


        } else if (!empty($cancel_url)) {
            $this->bCancel = true;
            $this->_cancelurl = $cancel_url;
        } else {
            $this->bCancel = false;
        }

        // FIXME process extra arguments more intelligently
        $default_args = array();
        if (!is_null($this->_context)) {
            $default_args = $this->_context->meldPersistQuery("","",true);
        }
        $this->_extraargs = KTUtil::arrayGet($aOptions,
            'extraargs', $default_args);

        // method
        $this->_method = KTUtil::arrayGet($aOptions, 'method', 'post');

        $this->_extraargs['postReceived'] = 1;
    }

    function getWidget(&$aInfo) {
        if (is_null($this->_oWF)) {
            $this->_oWF =& KTWidgetFactory::getSingleton();
        }

        if (is_null($aInfo)) {
            $widget = null;
        } else if (is_object($aInfo)) {

            // assume this is a fully configured object
            $widget =& $aInfo;
        } else {
            $namespaceOrObject = $aInfo[0];
            $config = (array) $aInfo[1];

            $widget =& $this->_oWF->get($namespaceOrObject, $config);
        }

        return $widget;
    }

    function getValidator($aInfo) {
        if (is_null($this->_oVF)) {
            $this->_oVF =& KTValidatorFactory::getSingleton();
        }

        $validator = null;

        // we don't want to expose the factory stuff to the user - its an
        // arbitrary distinction to the user.  Good point from NBM ;)
        if (is_null($aInfo)) {
            $validator = null;
        } else if (is_object($aInfo)) {
            // assume this is a fully configured object
            $validator =& $aInfo;
        } else {
            $namespaceOrObject = $aInfo[0];
            $config = (array) $aInfo[1];

            $validator =& $this->_oVF->get($namespaceOrObject, $config);
        }

        return $validator;
    }

    // set the "form widgets" that will be used.
    // these are pushed into the "data" component
    function setWidgets($aWidgets) {
        $this->_widgets = array();

        if (is_null($this->_oWF)) {
            $this->_oWF =& KTWidgetFactory::getSingleton();
        }

        $this->addWidgets($aWidgets);
    }

    function addWidgets($aWidgets) {
        foreach ($aWidgets as $aInfo) {
            $widget = $this->getWidget($aInfo);

            if (is_null($widget)) {
                continue;
            } else {
                $this->_widgets[] = $widget;
            }
        }
    }

    function setValidators($aValidators) {
        $this->_validators = array();

        if (is_null($this->_oVF)) {
            $this->_oVF =& KTValidatorFactory::getSingleton();
        }

        $this->addValidators($aValidators);
    }

    function addValidators($aValidators) {
        // we don't want to expose the factory stuff to the user - its an
        // arbitrary distinction to the user.  Good point from NBM ;)
        foreach ($aValidators as $aInfo) {
            $validator = $this->getValidator($aInfo);

            if (is_null($validator)) {
                continue;
            } else {
                $this->_validators[] = $validator;
            }
        }
    }

    function addValidator($aInfo) {
        $validator = $this->getValidator($aInfo);

        if (is_null($validator)) {
            return false;
        } else {
            $this->_validators[] =& $validator;
        }
    }

    function addWidget($aInfo) {
        $widget = $this->getWidget($aInfo);

        if (is_null($widget)) {
            return false;
        } else {
            $this->_widgets[] =& $widget;
        }
    }

    function addInitializedWidget($oWidget) {
        $this->_widgets[] = $oWidget;
    }

    function render() {
        $sWidgets = $this->renderWidgets();
        $sButtons = $this->renderButtons();

        return $this->renderContaining($sWidgets . ' ' . $sButtons);
    }

    function renderPage($sTitle = null, $sDescription = null) {
        if ($sTitle == null) {
            $sTitle = $this->sLabel;
        }
        $pageval =  $this->render();
        $sHelpText = '';
        if (!is_null($sDescription)) {
            $sHelpText = sprintf('<p class="descriptiveText">%s</p>', $sDescription);
        }
        return sprintf('<h2>%s</h2> %s %s', sanitizeForHTML($sTitle), $sHelpText, $pageval);
    }

    function getErrors() {
        $aErrors = array();
        $old_data = KTUtil::arrayGet((array) $_SESSION['_kt_old_data'],
            $this->_kt_form_name, array());
        if (KTUtil::arrayGet($old_data, 'identifier') == $this->sIdentifier) {
            $aErrors = (array) unserialize(KTUtil::arrayGet($old_data, 'errors'));
        }
        return $aErrors;
    }

    function renderWidgets() {
        if (empty($this->_widgets)) {
            return '&nbsp;';
        }

        // do this all at the *last* possible moment
        // now we need to do two things:
        //
        //   1. inform each "widget" that it needs to wrap itself inside
        //      the "data" var
        //   2. replace the widget's default values with the ones from the
        //      failed request, as appropriate.
        $bUseOld = false;
        $aOldData = array();
        $aErrors = array();
        $old_data = KTUtil::arrayGet((array) $_SESSION['_kt_old_data'],
                                     $this->_kt_form_name, array());
        if (KTUtil::arrayGet($old_data, 'identifier') == $this->sIdentifier) {
            $bUseOld = true;
            $aStoredData = (array) unserialize(KTUtil::arrayGet($old_data, 'data'));
            $aOldData = array();
            foreach ($aStoredData as $k => $v) {
                $aOldData[$k] = unserialize($v);
            }
            $aErrors = (array) unserialize(KTUtil::arrayGet($old_data, 'errors'));
        }

        foreach ($this->_widgets as $k => $v) {
            if (PEAR::isError($v)) {
                continue; // error, handle it in render.
            }
            $widget =& $this->_widgets[$k]; // reference needed since we're changing them
            $widget->wrapName('data');
            if ($bUseOld) {
                $widget->setDefault(KTUtil::arrayGet($aOldData, $widget->getBasename(),
                    $widget->getDefault(), false));
                $widget->setErrors(KTUtil::arrayGet($aErrors, $widget->getBasename()));
            }
        }

        // too much overhead by half to use a template here
        // so we do it the "old fashioned" way.
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

    function renderButtons() {
        $oKTTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oKTTemplating->loadTemplate('ktcore/forms/buttons');

        // now do the render.
        $oTemplate->setData(array(
           'context' => &$this,
        ));

        return $oTemplate->render();
    }

    function renderContaining() {

        $args = func_get_args();
        $sInner = implode(' ', $args);

        $oKTTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oKTTemplating->loadTemplate('ktcore/forms/outerform');

        // remove inner "action" var from extraargs
        // if its there at all.
        unset($this->_extraargs[$this->_event]);
        $this->_extraargs['_kt_form_name'] = $this->_kt_form_name;

        // now do the render.
        $oTemplate->setData(array(
           'context' => &$this,
           'inner' => $sInner,
        ));

        return $oTemplate->render();
    }

    function generateFormName($sIdentifier = null) {
        if (!is_null($sIdentifier)) {
            // try use the existing one from the request.
            $existing = KTUtil::arrayGet($_REQUEST, '_kt_form_name');

            if (!empty($existing)) {
                // check that its the same form
                $data = KTUtil::arrayGet($_SESSION['_kt_old_data'], $existing);
                if ($data['identifier'] == $sIdentifier) {
                    return $existing;
                }
            }
        }
        return KTUtil::randomString(32); // unique 32 char string
    }

    function validate() {
        // we first ask each widget to pull its data out.
        // while we do that, we create the storage set for the session
        // that widgets can call on later.

        $raw_data = KTUtil::arrayGet($_REQUEST, 'data');
        $processed_data = array();
        foreach ($this->_widgets as $oWidget) {
            if (PEAR::isError($oWidget)) {
                continue;
            }

            // widgets are expected to place their data in the "basename"
            // entry in the processed data area
            //
            // they should also be able to reconstruct their inputs from this
            // since its what they get later.

            $res = $oWidget->process($raw_data);
            $processed_data = kt_array_merge($processed_data, $res);
        }

        // before we validate ANYTHING we store data into the session

        $store_data = array(); // we only want to store serialized values here
        foreach ($processed_data as $k => $v) {
            $store_data[$k] = serialize($v);
        }

        $_SESSION['_kt_old_data'][$this->_kt_form_name]['data'] = serialize($store_data);
        $_SESSION['_kt_old_data'][$this->_kt_form_name]['identifier'] =
            $this->sIdentifier;
        $_SESSION['_kt_old_data'][$this->_kt_form_name]['created'] =
            getCurrentDateTime();

        $results = array();
        $errors = array();

        // some things can be checked by the actual widgets involved.  These
        // are obvious (e.g. required) and shouldn't require the developer to
        // think about them.
        //
        // to accomplish this, we call each widget's "getValidators" method.
        //
        // note that autovalidation can be turned off for a widget by passing
        // "autovalidate" => "false" in the widget's config.

        $extra_validators = array();
        foreach ($this->_widgets as $oWidget) {
            if (PEAR::isError($oWidget)) {
                continue;
            }

            $res = $oWidget->getValidators();

            if (!is_null($res)) {
                if (is_array($res)) {
                    $extra_validators = kt_array_merge($extra_validators, $res);
                } else {
                    $extra_validators[] = $res;
                }
            }
        }

        $validators = kt_array_merge($extra_validators, $this->_validators);

        foreach ($validators as $oValidator) {
            if (PEAR::isError($oValidator)) {
                // don't bother with broken validators, but warn the user/dev
                $errors['_kt_global'][] = $oValidator->getMessage();
                continue;
            }

            $res = $oValidator->validate($processed_data);

            // results comes out with a set of names and values.
            // these *shouldn't* overlap, so just merge them
            $extra_results = KTUtil::arrayGet($res, 'results', array());
            $results = kt_array_merge($results, $extra_results);

            // errors *can* overlap
            // the format is:
            //   basename => array(errors)
            // so that a given field can have multiple errors
            // from multiple validators
            //
            // there is also a "global" error notice stored against the var
            // _kt_global
            $extra_errors = KTUtil::arrayGet($res, 'errors', array());
            foreach ($extra_errors as $varname => $aErrors) {
                if (is_string($aErrors)) {
                    $errors[$varname][] = $aErrors;
                } else {
                    $errors[$varname] = kt_array_merge($errors[$varname], $aErrors);
                }
            }
        }

        $this->_errors = $errors; // store for later use without unserialising
        if (!empty($errors)) {
            $_SESSION['_kt_old_data'][$this->_kt_form_name]['errors'] =
                serialize($errors);
        }

        //var_dump($errors); exit(0);

        return array(
            'errors' => $errors,
            'results' => $results,
        );
    }

    function handleError($sGlobalError = null, $aSimplerErrors = null) {
        if (!is_null($sGlobalError)) {
            $this->_errors['_kt_global'][] = $sGlobalError;
        }
        if (!is_null($aSimplerErrors)) {
            foreach ($aSimplerErrors as $k => $v) {
                $this->_errors[$k] = kt_array_merge($this->_errors[$k], $v);
            }
            // since we've changed them, update the stored version
            $_SESSION['_kt_old_data'][$this->_kt_form_name]['errors'] =
                serialize($this->_errors);
        }
        if (is_array($this->_errors)) {
            $global_errors = KTUtil::arrayGet($this->_errors, '_kt_global', array());
            $_SESSION['KTErrorMessage'] = kt_array_merge($_SESSION['KTErrorMessage'], $global_errors);
        }

        if (!empty($this->_failaction) && !is_null($this->_context)) {
            $this->_context->errorRedirectTo($this->_failaction,
                _kt("Please correct the errors indicated."),
                sprintf("_kt_form_name=%s",$this->_kt_form_name));
            exit(0);
        } else if ($this->_failurl){
            redirect(KTUtil::addQueryString($this->_failurl,
                sprintf("_kt_form_name=%s",$this->_kt_form_name)));
            exit(0);
        } else {
            return '<div class="ktError"><p>' . _kt("An error occured, and no error handlers were configured.") . '</p></div>';
            exit(0);
        }
    }
}

?>
