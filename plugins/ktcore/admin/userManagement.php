<?php

/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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

require_once(KT_LIB_DIR . '/database/dbutil.inc');

require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/users/userutil.inc.php');
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');
require_once(KT_LIB_DIR . '/groups/Group.inc');

require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

require_once(KT_LIB_DIR . '/authentication/authenticationsource.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationproviderregistry.inc.php');
require_once(KT_LIB_DIR . '/authentication/builtinauthenticationprovider.inc.php');

class KTUserAdminDispatcher extends KTAdminDispatcher {

    public $sHelpPage = 'ktcore/admin/manage users.html';

    public function do_main()
    {
        $KTConfig = KTConfig::getSingleton();
        $alwaysAll = $KTConfig->get('alwaysShowAll');
        $alwaysAll = true;

        $userId = KTUtil::arrayGet($_REQUEST, 'user_id');
        $noSearch = (KTUtil::arrayGet($_REQUEST, 'do_search', false) === false);
        $name = KTUtil::arrayGet($_REQUEST, 'search_name', KTUtil::arrayGet($_REQUEST, 'old_search'));
        if ($name == '*') {
            $showAll = true;
            $name = '';
        }
        else {
            $showAll = KTUtil::arrayGet($_REQUEST, 'show_all', $alwaysAll);
        }

        $searchFields = array();
        $searchFields[] =  new KTStringWidget(_kt(''), _kt(""), 'search_name', $name, $this->oPage);

        // FIXME handle group search stuff.
        $searchResults = null;
        if (!empty($name)) {
            $searchResults = User::getList('WHERE username LIKE \'%' . DBUtil::escapeSimple($name) . '%\' AND id > 0');
        }
        else if ($showAll !== false) {
            $searchResults = User::getList('id > 0');
            $noSearch = false;
            $name = '*';
        }

        $authenticationSources = KTAuthenticationSource::getList();

        $canAdd = true;
        if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            $path = KTPluginUtil::getPluginPath('ktdms.wintools');
            require_once($path . 'baobabkeyutil.inc.php');
            $canAdd = BaobabKeyUtil::canAddUser();
            if (PEAR::isError($canAdd)) {
                $canAdd = false;
            }
        }

        $templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/principals/useradmin');
        $templateData = array(
            'context' => $this,
            'search_fields' => $searchFields,
            'search_results' => $searchResults,
            'no_search' => $noSearch,
            'authentication_sources' => $authenticationSources,
            'old_search' => $name,
            'can_add' => $canAdd,
            'invited' => false,
            'authentication' => ACCOUNT_ROUTING,
            'gravatar' => KTSmartyTemplate::md5Hash('$oUser->getEmail()'),
        );

        return $template->render($templateData);
    }

    public function do_resendInvite()
    {
        $userId = $_REQUEST['user_id'];
        $user = User::get($userId);

        if (PEAR::isError($user)) {
            $this->errorRedirectToMain(_kt("Error on resending the invitation to user ({$userId}) - {$user->getMessage()}"), 'show_all=1');
            exit;
        }

        $res = KTUserUtil::sendInvitations(array(array('id' => $userId, 'email' => $user->getEmail())));
        if ($res) {
            $this->successRedirectToMain('Invitation sent', 'show_all=1');
            exit;
        }

        $this->errorRedirectToMain(_kt("Invitation could not be sent to user ({$userId})"), 'show_all=1');
    }

    public function do_addUser()
    {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->oPage->setBreadcrumbDetails(_kt('add a new user'));
        $this->oPage->setTitle(_kt('Add New User'));

        // Get persisted params
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        $username = KTUtil::arrayGet($_REQUEST, 'newusername');
        $emailAddress = KTUtil::arrayGet($_REQUEST, 'email_address');
        $mobileNum = KTUtil::arrayGet($_REQUEST, 'mobile_number');
        $maxSessions = KTUtil::arrayGet($_REQUEST, 'max_sessions', '3');

        // Check if parameters are being persisted before checking for the email notification parameter - otherwise it will always be true
        if (isset($_REQUEST['name']) || isset($_REQUEST['newusername'])) {
            $emailNotification = (KTUtil::arrayGet($_REQUEST, 'email_notification') == 'on') ? true : false;
        }
        else {
            $emailNotification = true;
        }

        $showAll = KTUtil::arrayGet($_REQUEST, 'show_all', false);
        $addUser = KTUtil::arrayGet($_REQUEST, 'add_user', false);
        if ($addUser !== false) {
            $addUser = true; // HUH?
        }
        $editUser = KTUtil::arrayGet($_REQUEST, 'edit_user', false);
        $options = array('autocomplete' => false);

        // sometimes even admin is restricted in what they can do.

        $KTConfig = KTConfig::getSingleton();
        $minLength = ((int) $KTConfig->get('user_prefs/passwordLength', 6));
        $restrictAdmin = ((bool) $KTConfig->get('user_prefs/restrictAdminPasswords', false));
        $passwordAddRequirement = '';
        if ($restrictAdmin) {
            $passwordAddRequirement = ' ' . sprintf('Password must be at least %d characters long.', $minLength);
        }

        $useEmail = $KTConfig->get('user_prefs/useEmailLogin', false);
        if ($useEmail) {
            $addFields = $this->getNewAddUserFields($username, $emailAddress, $passwordAddRequirement, $maxSessions, $options);
        }
        else {
            $addFields = $this->getOldAddUserFields($username, $emailAddress, $passwordAddRequirement, $maxSessions, $options);
        }

        $templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/principals/adduser');
        $templateData = array(
            'context' => $this,
            'add_fields' => $addFields,
        );

        return $template->render($templateData);
    }

    private function getOldAddUserFields($username, $emailAddress, $passwordAddRequirement, $maxSessions, $options)
    {
        $addFields[] =  new KTStringWidget(_kt('Username'), sprintf(_kt('The username the user will enter to get access to %s.  e.g. <strong>jsmith</strong>'), APP_NAME), 'newusername', $username, $this->oPage, true, null, null, $options);
        $addFields[] =  new KTStringWidget(_kt('Name'), _kt('The full name of the user.  This is shown in reports and listings.  e.g. <strong>John Smith</strong>'), 'name', $name, $this->oPage, true, null, null, $options);
        $addFields[] =  new KTStringWidget(_kt('Email Address'), _kt('The email address of the user.  Notifications and alerts are mailed to this address if <strong>email notifications</strong> is set below. e.g. <strong>jsmith@acme.com</strong>'), 'email_address', $emailAddress, $this->oPage, false, null, null, $options);
        $addFields[] =  new KTBooleanWidget(_kt('Email Notifications'), _kt("If this is specified then the user will have notifications sent to the email address entered above.  If it isn't set, then the user will only see notifications on the Dashboard"), 'email_notifications', $emailNotification, $this->oPage, false, null, null, $options);
        $addFields[] =  new KTPasswordWidget(_kt('Password'), _kt('Specify an initial password for the user.') . $passwordAddRequirement, 'new_password', null, $this->oPage, true, null, null, $options);
        $addFields[] =  new KTPasswordWidget(_kt('Confirm Password'), _kt('Confirm the password specified above.'), 'confirm_password', null, $this->oPage, true, null, null, $options);
        // nice, easy bits.
        $addFields[] =  new KTStringWidget(_kt('Mobile Number'), _kt("The mobile phone number of the user.  e.g. <strong>999 9999 999</strong>"), 'mobile_number', $mobileNum, $this->oPage, false, null, null, $options);
        $addFields[] =  new KTStringWidget(_kt('Maximum Sessions'), _kt('As a safety precaution, it is useful to limit the number of times a given account can log in, before logging out.  This prevents a single account being used by many different people.'), 'max_sessions', $maxSessions, $this->oPage, true, null, null, $options);

        return $addFields;
    }

    private function getNewAddUserFields($username, $emailAddress, $passwordAddRequirement, $maxSessions, $options)
    {
        $userInfo = sprintf('The username the user will enter to get access to %s.  e.g. <strong>jsmith</strong>', APP_NAME);
        $addFields[] =  new KTStringWidget(_kt('Email Address'), _kt($userInfo . '<br/>Notifications and alerts are mailed to this address if <strong>email notifications</strong> is set below. e.g. <strong>jsmith@acme.com</strong>'), 'email_address', $emailAddress, $this->oPage, true, null, null, $options);
        $addFields[] =  new KTStringWidget(_kt('Name'), _kt('The full name of the user.  This is shown in reports and listings.  e.g. <strong>John Smith</strong>'), 'name', $name, $this->oPage, true, null, null, $options);
        $addFields[] =  new KTBooleanWidget(_kt('Email Notifications'), _kt("If this is specified then the user will have notifications sent to the email address entered above.  If it isn't set, then the user will only see notifications on the Dashboard"), 'email_notifications', $emailNotification, $this->oPage, false, null, null, $options);
        $addFields[] =  new KTPasswordWidget(_kt('Password'), _kt('Specify an initial password for the user.') . $passwordAddRequirement, 'new_password', null, $this->oPage, true, null, null, $options);
        $addFields[] =  new KTPasswordWidget(_kt('Confirm Password'), _kt('Confirm the password specified above.'), 'confirm_password', null, $this->oPage, true, null, null, $options);
        // nice, easy bits.
        $addFields[] =  new KTStringWidget(_kt('Mobile Number'), _kt("The mobile phone number of the user.  e.g. <strong>999 9999 999</strong>"), 'mobile_number', $mobileNum, $this->oPage, false, null, null, $options);
        $addFields[] =  new KTStringWidget(_kt('Maximum Sessions'), _kt('As a safety precaution, it is useful to limit the number of times a given account can log in, before logging out.  This prevents a single account being used by many different people.'), 'max_sessions', $maxSessions, $this->oPage, true, null, null, $options);

        return $addFields;
    }

    function do_addUserFromSource()
    {
        $authenticationSource = KTAuthenticationSource::get($_REQUEST['source_id']);
        $providerName = $authenticationSource->getAuthenticationProvider();
        $authenticationRegistry = KTAuthenticationProviderRegistry::getSingleton();
        $authenticationProvider = $authenticationRegistry->getAuthenticationProvider($providerName);

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->aBreadcrumbs[] = array('url' => KTUtil::addQueryStringSelf('action=addUser'), 'name' => _kt('add a new user'));
        $authenticationProvider->aBreadcrumbs = $this->aBreadcrumbs;
        $authenticationProvider->oPage->setBreadcrumbDetails($authenticationSource->getName());
        $authenticationProvider->oPage->setTitle(_kt('Add New User'));

        $authenticationProvider->dispatch();
    }

    function do_editUser()
    {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->oPage->setBreadcrumbDetails(_kt('modify user details'));
        $this->oPage->setTitle(_kt('Modify User Details'));

        $userId = KTUtil::arrayGet($_REQUEST, 'user_id');
        $user = User::get($userId);

        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');

        if (PEAR::isError($user) || $user == false) {
            $this->errorRedirectToMain(_kt('Please select a user first.'));
            exit(0);
        }

        $name = KTUtil::arrayGet($_REQUEST, 'name', $user->getName());
        $username = KTUtil::arrayGet($_REQUEST, 'newusername', $user->getUsername());
        $emailAddress = KTUtil::arrayGet($_REQUEST, 'email_address', $user->getEmail());
        $mobileNum = KTUtil::arrayGet($_REQUEST, 'mobile_number', $user->getMobile());
        $maxSessions = KTUtil::arrayGet($_REQUEST, 'max_sessions', $user->getMaxSessions());

        if (isset($_REQUEST['name']) || isset($_REQUEST['newusername'])) {
            $emailNotification = (KTUtil::arrayGet($_REQUEST, 'email_notification') == 'on') ? true : false;
        }
        else {
            $emailNotification = $user->getEmailNotification();
        }

        $this->aBreadcrumbs[] = array('name' => $user->getName());

        $KTConfig = KTConfig::getSingleton();
        $useEmail = $KTConfig->get('user_prefs/useEmailLogin', false);
        if ($useEmail) {
            $editFields = $this->getNewEditUserFields($username, $name, $emailNotification, $mobileNum, $emailAddress, $maxSessions);
        }
        else {
            $editFields = $this->getOldEditUserFields($username, $name, $emailNotification, $mobileNum, $emailAddress, $maxSessions);
        }

        $authenticationSource = KTAuthenticationSource::getForUser($user);
        if (is_null($authenticationSource)) {
            $authenticationProvider = new KTBuiltinAuthenticationProvider;
        }
        else {
            $providerName = $authenticationSource->getAuthenticationProvider();
            $authenticationRegistry = KTAuthenticationProviderRegistry::getSingleton();
            $authenticationProvider = $authenticationRegistry->getAuthenticationProvider($providerName);
        }

        $templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/principals/edituser');
        $templateData = array(
            'context' => $this,
            'edit_fields' => $editFields,
            'edit_user' => $user,
            'provider' => $authenticationProvider,
            'source' => $authenticationSource,
            'old_search' => $oldSearch,
        );

        return $template->render($templateData);
    }

    private function getOldEditUserFields($username, $name, $emailNotification, $mobileNum, $emailAddress, $maxSessions)
    {
        $editFields[] =  new KTStringWidget(_kt('Username'), sprintf(_kt('The username the user will enter to get access to %s.  e.g. <strong>jsmith</strong>'), APP_NAME), 'newusername', $username, $this->oPage, true);
        $editFields[] =  new KTStringWidget(_kt('Name'), _kt('The full name of the user.  This is shown in reports and listings.  e.g. <strong>John Smith</strong>'), 'name', $name, $this->oPage, true);
        $editFields[] =  new KTStringWidget(_kt('Email Address'), _kt('The email address of the user.  Notifications and alerts are mailed to this address if <strong>email notifications</strong> is set below. e.g. <strong>jsmith@acme.com</strong>'), 'email_address', $emailAddress, $this->oPage, false);
        $editFields[] =  new KTBooleanWidget(_kt('Email Notifications'), _kt('If this is specified then the user will have notifications sent to the email address entered above.  If it is not set, then the user will only see notifications on the <strong>Dashboard</strong>'), 'email_notifications', $emailNotification, $this->oPage, false);
        $editFields[] =  new KTStringWidget(_kt('Mobile Number'), _kt("The mobile phone number of the user.  e.g. <strong>999 9999 999</strong>"), 'mobile_number', $mobileNum, $this->oPage, false);
        $editFields[] =  new KTStringWidget(_kt('Maximum Sessions'), _kt('As a safety precaution, it is useful to limit the number of times a given account can log in, before logging out.  This prevents a single account being used by many different people.'), 'max_sessions', $maxSessions, $this->oPage, true);

        return $editFields;
    }

    private function getNewEditUserFields($username, $name, $emailNotification, $mobileNum, $emailAddress, $maxSessions)
    {
        $userInfo = sprintf('The username the user will enter to get access to %s.  e.g. <strong>jsmith</strong>', APP_NAME);
        $editFields[] =  new KTStringWidget(_kt('Email Address'), _kt($userInfo . '<br/>Notifications and alerts are mailed to this address if <strong>email notifications</strong> is set below. e.g. <strong>jsmith@acme.com</strong>'), 'email_address', $emailAddress, $this->oPage, true);
        $editFields[] =  new KTStringWidget(_kt('Name'), _kt('The full name of the user.  This is shown in reports and listings.  e.g. <strong>John Smith</strong>'), 'name', $name, $this->oPage, true);
        $editFields[] =  new KTBooleanWidget(_kt('Email Notifications'), _kt('If this is specified then the user will have notifications sent to the email address entered above.  If it is not set, then the user will only see notifications on the <strong>Dashboard</strong>'), 'email_notifications', $emailNotification, $this->oPage, true);
        $editFields[] =  new KTStringWidget(_kt('Mobile Number'), _kt("The mobile phone number of the user.  e.g. <strong>999 9999 999</strong>"), 'mobile_number', $mobileNum, $this->oPage, false);
        $editFields[] =  new KTStringWidget(_kt('Maximum Sessions'), _kt('As a safety precaution, it is useful to limit the number of times a given account can log in, before logging out.  This prevents a single account being used by many different people.'), 'max_sessions', $maxSessions, $this->oPage, true);

        return $editFields;
    }

    function do_setPassword()
    {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->oPage->setBreadcrumbDetails(_kt('change user password'));
        $this->oPage->setTitle(_kt('Change User Password'));

        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');

        $userId = KTUtil::arrayGet($_REQUEST, 'user_id');
        $user = User::get($userId);

        if (PEAR::isError($user) || $user == false) {
            $this->errorRedirectToMain(_kt('Please select a user first.'));
            exit(0);
        }

        $this->aBreadcrumbs[] = array('name' => $user->getName());

        $editFields = array();
        $editFields[] =  new KTPasswordWidget(_kt('Password'), _kt('Specify an initial password for the user.'), 'new_password', null, $this->oPage, true);
        $editFields[] =  new KTPasswordWidget(_kt('Confirm Password'), _kt('Confirm the password specified above.'), 'confirm_password', null, $this->oPage, true);

        $templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/principals/updatepassword');
        $templateData = array(
            'context' => $this,
            'edit_fields' => $editFields,
            'edit_user' => $user,
            'old_search' => $oldSearch,
        );

        return $template->render($templateData);
    }

    function do_updatePassword()
    {
        $userId = KTUtil::arrayGet($_REQUEST, 'user_id');

        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');

        $password = KTUtil::arrayGet($_REQUEST, 'new_password');
        $confirmPassword = KTUtil::arrayGet($_REQUEST, 'confirm_password');

        $KTConfig = KTConfig::getSingleton();
        $minLength = ((int) $KTConfig->get('user_prefs/passwordLength', 6));

        $restrictAdmin = ((bool) $KTConfig->get('user_prefs/restrictAdminPasswords', false));
        if ($restrictAdmin && (strlen($password) < $minLength)) {
            $this->errorRedirectToMain(sprintf(_kt('The password must be at least %d characters long.'), $minLength));
        }
        else if (empty($password)) {
            $this->errorRedirectToMain(_kt('You must specify a password for the user.'));
        }
        else if ($password !== $confirmPassword) {
            $this->errorRedirectToMain(_kt('The passwords you specified do not match.'));
        }

        // FIXME more validation would be useful.
        // validated and ready..
        $this->startTransaction();

        $user = User::get($userId);
        if (PEAR::isError($user) || $user == false) {
            $this->errorRedirectToMain(_kt('Please select a user to modify first.'));
        }

        // FIXME this almost certainly has side-effects.  do we _really_ want
        $user->setPassword(md5($password)); //

        $res = $user->update();
        //$res = $user->doLimitedUpdate(); // ignores a fix blacklist of items.

        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectoToMain(_kt('Failed to update user.'));
        }

        $this->commitTransaction();
        $this->successRedirectToMain(_kt('User information updated.'));
    }

    function do_editUserSource()
    {
        $userId = KTUtil::arrayGet($_REQUEST, 'user_id');
        $user = $this->oValidator->validateUser($userId);
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->aBreadcrumbs[] = array('name' => $user->getName());

        $authenticationSource = KTAuthenticationSource::getForUser($user);
        if (is_null($authenticationSource)) {
            $authenticationProvider = new KTBuiltinAuthenticationProvider;
        }
        else {
            $providerName = $authenticationSource->getAuthenticationProvider();
            $authenticationRegistry = KTAuthenticationProviderRegistry::getSingleton();
            $authenticationProvider = $authenticationRegistry->getAuthenticationProvider($providerName);
        }

        $authenticationProvider->subDispatch($this);
    }

    function do_editGroups()
    {
        $userId = KTUtil::arrayGet($_REQUEST, 'user_id');
        $user = User::get($userId);
        if ((PEAR::isError($user)) || ($user === false)) {
            $this->errorRedirectToMain(_kt('No such user.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->oPage->setBreadcrumbDetails($user->getName() .': ' . _kt('edit groups'));
        $this->oPage->setTitle(sprintf(_kt("Edit %s's groups"), $user->getName()));

        // generate a list of groups this user is authorised to assign.

        // NOTE is this still relevant? [2011-03-08]
        /* FIXME there is a nasty side-effect:  if a user cannot assign a group
        * to a user, and that user _had_ that group pre-edit,
        * then their privileges are revoked.
        * is there _any_ way to fix that?
        */

        $members = KTJSONLookupWidget::formatMemberGroups(GroupUtil::listGroupsForUser($user));
        $options = array('selection_default' => 'Select groups', 'optgroups' => false);
        $label['header'] = 'Groups';
        $label['text'] = 'Select the groups which this user should belong to from the drop down list. Remove groups by clicking the X.  Once you have added all the groups that you require, press <strong>save changes</strong>.';
        $jsonWidget = KTJSONLookupWidget::getGroupSelectorWidget(
                                                                $label,
                                                                'group',
                                                                'groups',
                                                                $members,
                                                                $options
        );

        $templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/principals/usergroups');
        $templateData = array(
            'context' => $this,
            'edit_user' => $user,
            'widget' => $jsonWidget,
            'old_search' => $oldSearch,
        );

        return $template->render($templateData);
    }

    private function saveEmailUser()
    {
        $userId = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');
        $errorOptions = array(
            'redirect_to' => array('editUser', sprintf('user_id=%d&old_search=%s&do_search=1', $userId, $oldSearch))
        );

        $inputKeys = array('name', 'email_address', 'email_notifications', 'mobile_number', 'max_sessions');
        $this->persistParams($inputKeys);

        $name = $this->oValidator->validateString(
            KTUtil::arrayGet($_REQUEST, 'name'),
            KTUtil::meldOptions($errorOptions, array('message' => _kt('You must provide a name')))
        );

        $emailAddress = KTUtil::arrayGet($_REQUEST, 'email_address');
        if (strlen(trim($emailAddress))) {
            $emailAddress = $this->oValidator->validateEmailAddress($emailAddress, $errorOptions);
        }

        $emailNotifications = KTUtil::arrayGet($_REQUEST, 'email_notifications', false);
        if ($emailNotifications !== false) {
            $emailNotifications = true;
        }

        $mobileNumber = KTUtil::arrayGet($_REQUEST, 'mobile_number');
        $maxSessions = KTUtil::arrayGet($_REQUEST, 'max_sessions', '3', false);

        $this->startTransaction();

        $user = User::get($userId);
        if (PEAR::isError($user) || $user == false) {
            $this->errorRedirectToMain(_kt('Please select a user to modify first.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        $duplicateUser = User::getByUserName($emailAddress);
        if (!PEAR::isError($duplicateUser)) {
            if ($duplicateUser->getId() != $user->getId()) {
                $this->errorRedirectTo('addUser', _kt('A user with that email address already exists'));
            }
        }

        $user->setName($name);
        $user->setUsername($emailAddress);
        $user->setEmail($emailAddress);
        $user->setEmailNotification($emailNotifications);
        $user->setMobile($mobileNumber);
        $user->setMaxSessions($maxSessions);

        $res = $user->update();
        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectoToMain(_kt('Failed to update user.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        $this->commitTransaction();
        $this->successRedirectToMain(_kt('User information updated.'), sprintf('old_search=%s&do_search=1', $oldSearch));
    }

    function do_saveUser()
    {
        $KTConfig = KTConfig::getSingleton();

        $useEmail = $KTConfig->get('user_prefs/useEmailLogin', false);
        if ($useEmail) { return $this->saveEmailUser(); }

        $userId = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');
        $errorOptions = array(
            'redirect_to' => array('editUser', sprintf('user_id=%d&old_search=%s&do_search=1', $userId, $oldSearch))
        );
        $inputKeys = array('newusername', 'name', 'email_address', 'email_notifications', 'mobile_number', 'max_sessions');
        $this->persistParams($inputKeys);

        $name = $this->oValidator->validateString(
            KTUtil::arrayGet($_REQUEST, 'name'),
            KTUtil::meldOptions($errorOptions, array('message' => _kt('You must provide a name')))
        );

        $username = $this->oValidator->validateString(
        KTUtil::arrayGet($_REQUEST, 'newusername'),
            KTUtil::meldOptions($errorOptions, array('message' => _kt('You must provide a username')))
        );

        $emailAddress = KTUtil::arrayGet($_REQUEST, 'email_address');
        if (strlen(trim($emailAddress))) {
            $emailAddress = $this->oValidator->validateEmailAddress($emailAddress, $errorOptions);
        }

        $emailNotifications = KTUtil::arrayGet($_REQUEST, 'email_notifications', false);
        if ($emailNotifications !== false) $emailNotifications = true;

        $mobileNumber = KTUtil::arrayGet($_REQUEST, 'mobile_number');
        $maxSessions = KTUtil::arrayGet($_REQUEST, 'max_sessions', '3', false);

        // FIXME more validation would be useful.
        // validated and ready..
        $this->startTransaction();

        $user = User::get($userId);
        if (PEAR::isError($user) || $user == false) {
            $this->errorRedirectToMain(_kt('Please select a user to modify first.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        $duplicateUser = User::getByUserName($username);
        if (!PEAR::isError($duplicateUser)) {
            if ($duplicateUser->getId() != $user->getId()) {
                $this->errorRedirectTo('addUser', _kt('A user with that username already exists'));
            }
        }

        $user->setName($name);
        $user->setUsername($username);  // ?
        $user->setEmail($emailAddress);
        $user->setEmailNotification($emailNotifications);
        $user->setMobile($mobileNumber);
        $user->setMaxSessions($maxSessions);

        // old system used the very evil store.php.
        // here we need to _force_ a limited update of the object, via a db statement.
        //
        $res = $user->update();
        // $res = $user->doLimitedUpdate(); // ignores a fix blacklist of items.

        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectoToMain(_kt('Failed to update user.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        $this->commitTransaction();
        $this->successRedirectToMain(_kt('User information updated.'), sprintf('old_search=%s&do_search=1', $oldSearch));
    }

    function createEmailUser()
    {
        // FIXME generate and pass the error stack to adduser.
        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');
        $errorOptions = array(
            'redirect_to' => array('addUser', sprintf('old_search=%s&do_search=1', $oldSearch))
        );

        $inputKeys = array('name', 'email_address', 'email_notifications', 'mobile_number', 'max_sessions');
        $this->persistParams($inputKeys);

        $name = $this->oValidator->validateString(
        KTUtil::arrayGet($_REQUEST, 'name'),
            KTUtil::meldOptions($errorOptions, array('message' => _kt('You must provide a name')))
        );

        $emailAddress = $this->oValidator->validateEmailAddress(
            trim(KTUtil::arrayGet($_REQUEST, 'email_address')),
            KTUtil::meldOptions($errorOptions, array('message' => _kt('You must provide a valid email address.')))
        );

        $emailNotifications = KTUtil::arrayGet($_REQUEST, 'email_notifications', false);
        if ($emailNotifications !== false) { $emailNotifications = true; }

        $mobileNumber = KTUtil::arrayGet($_REQUEST, 'mobile_number');

        $maxSessions = $this->oValidator->validateInteger(
            KTUtil::arrayGet($_REQUEST, 'max_sessions'),
            KTUtil::meldOptions($errorOptions, array('message' => _kt('You must specify a numeric value for maximum sessions.')))
        );

        $password = KTUtil::arrayGet($_REQUEST, 'new_password');
        $confirmPassword = KTUtil::arrayGet($_REQUEST, 'confirm_password');
        $KTConfig = KTConfig::getSingleton();
        $minLength = ((int) $KTConfig->get('user_prefs/passwordLength', 6));

        $restrictAdmin = ((bool) $KTConfig->get('user_prefs/restrictAdminPasswords', false));
        if ($restrictAdmin && (strlen($password) < $minLength)) {
            $this->errorRedirectTo('addUser', sprintf(_kt('The password must be at least %d characters long.'), $minLength), sprintf('old_search=%s&do_search=1', $oldSearch));
        }
        else if (empty($password)) {
            $this->errorRedirectTo('addUser', _kt('You must specify a password for the user.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }
        else if ($password !== $confirmPassword) {
            $this->errorRedirectTo('addUser', _kt('The passwords you specified do not match.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        if (preg_match('/[\!\$\#\%\^\&\*]/', $name)) {
            $this->errorRedirectTo('addUser', _kt('You have entered an invalid character in your name.'));
        }

        $user = KTUserUtil::createUser($emailAddress, $name, $password, $emailAddress, $emailNotifications, $mobileNumber, $maxSessions);
        if (PEAR::isError($user)) {
            if ($user->getMessage() == _kt('A user with that username already exists')) {
                $this->errorRedirectTo('addUser', _kt('A user with that email address already exists'));
                exit();
            }
            $this->errorRedirectToMain(_kt('failed to create user.'), sprintf('old_search=%s&do_search=1', $oldSearch));
            exit;
        }

        $this->successRedirectToMain(_kt('Created new user') . ': ' . $user->getUsername(), 'name=' . $user->getUsername(), sprintf('old_search=%s&do_search=1', $oldSearch));

        return ;
    }

    function do_createUser()
    {
        $KTConfig = KTConfig::getSingleton();

        if ($KTConfig->get('user_prefs/useEmailLogin', false)) { return $this->createEmailUser(); }

        // FIXME generate and pass the error stack to adduser.
        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');
        $errorOptions = array(
            'redirect_to' => array('addUser', sprintf('old_search=%s&do_search=1', $oldSearch))
        );

        $inputKeys = array('newusername', 'name', 'email_address', 'email_notifications', 'mobile_number', 'max_sessions');
        $this->persistParams($inputKeys);

        $username = $this->oValidator->validateString(
        KTUtil::arrayGet($_REQUEST, 'newusername'),
            KTUtil::meldOptions($errorOptions, array('message' => _kt('You must specify a new username.')))
        );

        $name = $this->oValidator->validateString(
            KTUtil::arrayGet($_REQUEST, 'name'),
            KTUtil::meldOptions($errorOptions, array('message' => _kt('You must provide a name')))
        );

        $emailAddress = KTUtil::arrayGet($_REQUEST, 'email_address');
        $emailNotifications = KTUtil::arrayGet($_REQUEST, 'email_notifications', false);
        if ($emailNotifications !== false) { $emailNotifications = true; }

        $mobileNumber = KTUtil::arrayGet($_REQUEST, 'mobile_number');
        $maxSessions = $this->oValidator->validateInteger(
            KTUtil::arrayGet($_REQUEST, 'max_sessions'),
            KTUtil::meldOptions($errorOptions, array('message' => _kt('You must specify a numeric value for maximum sessions.')))
        );

        $password = KTUtil::arrayGet($_REQUEST, 'new_password');
        $confirmPassword = KTUtil::arrayGet($_REQUEST, 'confirm_password');

        $KTConfig = KTConfig::getSingleton();
        $minLength = ((int) $KTConfig->get('user_prefs/passwordLength', 6));
        $restrictAdmin = ((bool) $KTConfig->get('user_prefs/restrictAdminPasswords', false));

        if ($restrictAdmin && (strlen($password) < $minLength)) {
            $this->errorRedirectTo('addUser', sprintf(_kt('The password must be at least %d characters long.'), $minLength), sprintf('old_search=%s&do_search=1', $oldSearch));
        }
        else if (empty($password)) {
            $this->errorRedirectTo('addUser', _kt('You must specify a password for the user.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }
        else if ($password !== $confirmPassword) {
            $this->errorRedirectTo('addUser', _kt('The passwords you specified do not match.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        if (preg_match('/[\!\$\#\%\^\&\*]/', $username)) {
            $this->errorRedirectTo('addUser', _kt('You have entered an invalid character in your username.'));
        }

        if (preg_match('/[\!\$\#\%\^\&\*]/', $name)) {
            $this->errorRedirectTo('addUser', _kt('You have entered an invalid character in your name.'));
        }

        $user = KTUserUtil::createUser($username, $name, $password, $emailAddress, $emailNotifications, $mobileNumber, $maxSessions);
        if (PEAR::isError($user)) {
            if ($user->getMessage() == _kt('A user with that username already exists')) {
                $this->errorRedirectTo('addUser', _kt('A user with that username already exists'));
                exit();
            }

            $this->errorRedirectToMain(_kt('failed to create user.'), sprintf('old_search=%s&do_search=1', $oldSearch));
            exit;
        }

        $this->successRedirectToMain(_kt('Created new user') . ': ' . $user->getUsername(), 'name=' . $user->getUsername(), sprintf('old_search=%s&do_search=1', $oldSearch));

        return ;
    }

    function do_deleteUser()
    {
        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');

        $userId = KTUtil::arrayGet($_REQUEST, 'user_id');
        $user = User::get($userId);
        if ((PEAR::isError($user)) || ($user === false)) {
            $this->errorRedirectToMain(_kt('Please select a user first.'));
        }

        $res = $user->delete();
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(sprintf(_kt('Unable to delete user - the user may still be referred by documents.'), $res->getMessage()), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        $this->successRedirectToMain(_kt('User deleted') . ': ' . $user->getName(), sprintf('old_search=%s&do_search=1', $oldSearch));
    }

    function do_updateGroups()
    {
        $oldSearch = KTUtil::arrayGet($_REQUEST, 'old_search');

        $userId = KTUtil::arrayGet($_REQUEST, 'user_id');
        $user = User::get($userId);
        if ((PEAR::isError($user)) || ($user === false)) {
            $this->errorRedirectToMain(_kt('Please select a user first.'), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        // FIXME we need to ensure that only groups which are allocatable by the admin are added here.
        // FIXME what groups are _allocatable_?

        $this->startTransaction();

        // Detect existing group memberships (and diff with current, to see which were removed.)
        $currentGroups = GroupUtil::listGroupsForUser($user);
        // Probably should add a function for just getting this info, but shortcut for now.
        foreach ($currentGroups as $key => $group) {
            $currentGroups[$key] = $group->getName();
        }

        // Remove any current groups for this user.
        if (!empty($currentGroups) && !GroupUtil::removeGroupsForUser($user)) {
            $this->errorRedirectToMain(sprintf(_kt('Unable to remove existing group memberships')), sprintf('old_search=%s&do_search=1', $oldSearch));
        }

        // Insert submitted groups for this user.

        $groupsAdded = array();
        $addWarnings = array();
        // TODO I am sure we can do this much better, create a single insert query instead of one per added group.
        $groups = trim(KTUtil::arrayGet($_REQUEST, 'groups_roles'), ',');
        if (!empty($groups)) {
            $groups = explode(',', $groups);
            foreach ($groups as $idString) {
                $idData = explode('_', $idString);
                $group = Group::get($idData[1]);
                // Not sure this has any validity in the new method.
                $memberReason = GroupUtil::getMembershipReason($user, $group);
                if (!(PEAR::isError($memberReason) || is_null($memberReason))) {
                    $addWarnings[] = $memberReason;
                }

                $res = $group->addMember($user);
                if (PEAR::isError($res) || $res == false) {
                    $this->rollbackTransaction();
                    $this->errorRedirectToMain(sprintf(_kt('Unable to add user to group "%s"'), $group->getName()), sprintf('old_search=%s&do_search=1', $oldSearch));
                }
                else {
                    $groupsAdded[] = $group->getName();
                }
            }
        }

        $groupsRemoved = array_diff($currentGroups, $groupsAdded);
        $groupsAdded = array_diff($groupsAdded, $currentGroups);

        if (!empty($addWarnings)) {
            $warnStr = _kt('Warning:  the user was already a member of some subgroups') . ' &mdash; ';
            $warnStr .= implode(', ', $addWarnings);
            $_SESSION['KTInfoMessage'][] = $warnStr;
        }

        $msg = '';
        if (!empty($groupsAdded)) { $msg .= ' ' . _kt('Added to groups') . ': ' . implode(', ', $groupsAdded) . '.'; }
        if (!empty($groupsRemoved)) { $msg .= ' ' . _kt('Removed from groups') . ': ' . implode(', ',$groupsRemoved) . '.'; }

        if (!Permission::userIsSystemAdministrator($_SESSION['userID'])) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('editGroups', _kt('For security purposes, you cannot remove your own administration priviledges.'), sprintf('user_id=%d&do_search=1&old_search=%s', $user->getId(), $oldSearch));
            exit(0);
        }

        $this->commitTransaction();

        // Update the permissions cache for the user
        include_once(KT_LIB_DIR . '/security/PermissionCache.php');
        $cache = PermissionCache::getSingleton();
        $cache->updateCacheForUser($userId);

        $this->successRedirectToMain($msg, sprintf('old_search=%s&do_search=1', $oldSearch));
    }

    function getGroupStringForUser($user)
    {
        $groupNames = array();
        $groups = GroupUtil::listGroupsForUser($user);
        $maxGroups = 6;
        $addElipsis = false;

        if ($user->getDisabled() == 4) {
            return _kt('Shared users cannot be assigned to groups.');
        }

        if (count($groups) == 0) {
            return _kt('');
        }

        if (count($groups) > $maxGroups) {
            $groups = array_slice($groups, 0, $maxGroups);
            $addElipsis = true;
        }

        foreach ($groups as $group) {
            $groupNames[] = $group->getName();
        }

        if ($addElipsis) {
            $groupNames[] = '&hellip;';
        }

        return implode(', ', $groupNames);
    }

    // change enabled / disabled status of users
    function do_change_enabled()
    {
        $this->startTransaction();
        $licenses = 0;
        $requireLicenses = false;
        if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            $path = KTPluginUtil::getPluginPath('ktdms.wintools');
            require_once($path . 'baobabkeyutil.inc.php');
            $licenses = BaobabKeyUtil::getLicenseCount();
            $requireLicenses = true;
        }

        // admin and anonymous are automatically ignored here.
        $enabledUsers = User::getNumberEnabledUsers();

        if ($_REQUEST['update_value'] == 'enable') {
            foreach (KTUtil::arrayGet($_REQUEST, 'edit_user', array()) as $userId => $v) {
                // check that we haven't hit max user limit
                if ($requireLicenses && $enabledUsers >= $licenses) {
                    // if so, add to error messages, but commit transaction (break this loop)
                    $_SESSION['KTErrorMessage'][] = _kt('You may only have ') . $licenses . _kt(' users enabled at one time.');
                    break;
                }

                // else enable user
                $user = User::get((int)$userId);
                if (PEAR::isError($user)) {
                    $this->errorRedirectToMain(_kt('Error getting user object'));
                }

                $user->enable();

                $res = $user->update();
                if (PEAR::isError($res)) {
                    $this->errorRedirectToMain(_kt('Error updating user'));
                }

                ++$enabledUsers;
            }
        }

        if ($_REQUEST['update_value'] == 'disable') {
            foreach (KTUtil::arrayGet($_REQUEST, 'edit_user', array()) as $userId => $v) {
                $user = User::get((int)$userId);
                if (PEAR::isError($user)) {
                    $this->errorRedirectToMain(_kt('Error getting user object'));
                }

                $user->disable();

                $res = $user->update();
                if (PEAR::isError($res)) {
                    $this->errorRedirectToMain(_kt('Error updating user'));
                }

                --$enabledUsers;
            }
        }

        if ($_REQUEST['update_value'] == 'delete') {
            foreach (KTUtil::arrayGet($_REQUEST, 'edit_user', array()) as $userId => $v) {
                $user = User::get((int)$userId);
                if (PEAR::isError($user)) {
                    $this->errorRedirectToMain(_kt('Error getting user object'));
                }

                $user->delete();

                $res = $user->update();
                if (PEAR::isError($res)) {
                    $this->errorRedirectToMain(_kt('Error updating user'));
                }

                $enabledUsers--;
            }
        }

        if ($_REQUEST['update_value'] == 'invite') {
            $inviteList = array();
            foreach (KTUtil::arrayGet($_REQUEST, 'edit_user', array()) as $userId => $v) {
                $user = User::get((int)$userId);
                if (PEAR::isError($user)) {
                    $this->errorRedirectToMain(_kt('Error getting user object'));
                }

                if ($user->getDisabled() == 3) {
                    $inviteList[] = array('id' => $userId, 'email' => $user->getEmail());
                }
            }

            $res = KTUserUtil::sendInvitations($inviteList);
        }

        $this->commitTransaction();
        $this->successRedirectToMain(_kt('Users updated'), 'show_all=1');
    }

    public function handleOutput($output)
    {
        print $output;
    }

}

?>
