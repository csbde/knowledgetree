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

// main library routines and defaults
require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/unitmanagement/Unit.inc");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/widgets/forms.inc.php");

class PreferencesDispatcher extends KTStandardDispatcher {
    var $sSection = 'preferences';

    function check() {
        $oConfig =& KTConfig::getSingleton();
        if ($this->oUser->getId() == -2 ||
            ($oConfig->get('user_prefs/restrictPreferences', false) && !Permission::userIsSystemAdministrator($this->oUser->getId()))) {
            return false;
        }
        $this->aBreadcrumbs = array(array('action' => 'preferences', 'name' => _kt('Preferences')));
        return parent::check();
    }

    function form_main() {
        $oForm = new KTForm;

        $oForm->setOptions(array(
            'context' => &$this,
            'identifier' => 'ktcore.preferences.main',
            'action' => 'updatePreferences',
            'fail_action' => 'main',
            'label' => _kt('Your Details'),
            'submit_label' => _kt('Update Preferences'),
            'extraargs' => $this->meldPersistQuery("","", true),
        ));

        // widgets
        $oForm->setWidgets(array(
            array('ktcore.widgets.string', array(
                'label' => _kt('Name'),
                'description' => _kt('Your full name.  This is shown in reports and listings.  e.g. <strong>John Smith</strong>'),
                'required' => true,
                'name' => 'name',
                'value' => sanitizeForHTML($this->oUser->getName()),
                'autocomplete' => false)),
            array('ktcore.widgets.string', array(
                'label' => _kt('Email Address'),
                'description' => _kt('Your email address.  Notifications and alerts are mailed to this address if <strong>email notifications</strong> is set below. e.g. <strong>jsmith@acme.com</strong>'),
                'required' => false,
                'name' => 'email_address',
                'value' => sanitizeForHTML($this->oUser->getEmail()),
                'autocomplete' => false)),
            array('ktcore.widgets.boolean', array(
                'label' => _kt('Email Notifications'),
                'description' => _kt('If this is specified then the you will receive certain notifications.  If it is not set, then you will only see notifications on the <strong>Dashboard</strong>'),
                'required' => false,
                'name' => 'email_notifications',
                'value' => $this->oUser->getEmailNotification(),
                'autocomplete' => false)),
        ));

        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'name',
                'output' => 'name')),
            array('ktcore.validators.emailaddress', array(
                'test' => 'email_address',
                'output' => 'email_address')),
            array('ktcore.validators.boolean', array(
                'test' => 'email_notifications',
                'output' => 'email_notifications')),
        ));

        return $oForm;

    }

    function form_password() {
        $oForm = new KTForm;

        $oForm->setOptions(array(
            'context' => &$this,
            'identifier' => 'ktcore.preferences.password',
            'action' => 'updatePassword',
            'fail_action' => 'setPassword',
            'cancel_action' => 'main',
            'label' => _kt('Change your password'),
            'submit_label' => _kt('Set password'),
            'extraargs' => $this->meldPersistQuery("","", true),
        ));

        // widgets
        $oForm->setWidgets(array(
            array('ktcore.widgets.password', array(
                'label' => _kt('Password'),
                'description' => _kt('Specify your new password.'),
                'confirm_description' => _kt('Confirm the new password you specified above.'),
                'confirm' => true,
                'required' => true,
                'name' => 'new_password',
                'autocomplete' => false)),
        ));


        $KTConfig =& KTConfig::getSingleton();
        $minLength = ((int) $KTConfig->get('user_prefs/passwordLength', 6));

        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'new_password',
                'min_length' => $minLength,
                'min_length_warning' => sprintf(_kt("Your password is too short - passwords must be at least %d characters long."), $minLength),
                'output' => 'password')),
        ));

        return $oForm;

    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("Your Preferences"));
        $this->oPage->title = _kt("Dashboard");
        $oUser =& $this->oUser;

        $oForm = $this->form_main();

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/preferences");
        $iSourceId = $oUser->getAuthenticationSourceId();
        $bChangePassword = true;
        if ($iSourceId) {
            $bChangePassword = false;
        }
        $aTemplateData = array(
              "context" => $this,
              'edit_form' => $oForm,
              "show_password" => $bChangePassword,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_setPassword() {
        $this->oPage->setBreadcrumbDetails(_kt("Your Password"));
        $this->oPage->title = _kt("Dashboard");

        $oForm = $this->form_password();

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/password");
        $aTemplateData = array(
              "context" => $this,
              'form' => $oForm,
        );
        return $oTemplate->render($aTemplateData);
    }



    function do_updatePassword() {
        $oForm = $this->form_password();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            $oForm->handleError();
        }
        $res = $res['results'];

        $this->startTransaction();

        $oUser =& $this->oUser;


        // FIXME this almost certainly has side-effects.  do we _really_ want
        $oUser->setPassword(md5($res['password'])); //

        $res = $oUser->update();


        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectToMain(_kt('Failed to update user.'));
        }

        $this->commitTransaction();
        $this->successRedirectToMain(_kt('Your password has been changed.'));

    }


    function do_updatePreferences() {
        $aErrorOptions = array(
            'redirect_to' => array('main'),
        );

        $oForm = $this->form_main();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            $oForm->handleError();
        }

        $res = $res['results'];

        $this->startTransaction();
        $oUser =& $this->oUser;
        $oUser->setName($res['name']);
        $oUser->setEmail($res['email_address']);
        $oUser->setEmailNotification($res['email_notifications']);



        // old system used the very evil store.php.
        // here we need to _force_ a limited update of the object, via a db statement.
        //
        // $res = $oUser->update();
        $res = $oUser->doLimitedUpdate(); // ignores a fix blacklist of items.

        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectToMain(_kt('Failed to update your details.'));
        }

        $this->commitTransaction();
        $this->successRedirectToMain(_kt('Your details have been updated.'));

    }


}

$oDispatcher = new PreferencesDispatcher();
$oDispatcher->dispatch();

?>
