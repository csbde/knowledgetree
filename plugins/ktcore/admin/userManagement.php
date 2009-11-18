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

require_once(KT_LIB_DIR . '/database/dbutil.inc');

require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');
require_once(KT_LIB_DIR . '/groups/Group.inc');

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

require_once(KT_LIB_DIR . "/authentication/authenticationsource.inc.php");
require_once(KT_LIB_DIR . "/authentication/authenticationproviderregistry.inc.php");
require_once(KT_LIB_DIR . "/authentication/builtinauthenticationprovider.inc.php");

class KTUserAdminDispatcher extends KTAdminDispatcher {
    var $sHelpPage = 'ktcore/admin/manage users.html';
    function do_main() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->oPage->setBreadcrumbDetails(_kt('select a user'));
        $this->oPage->setTitle(_kt("User Management"));

		$KTConfig =& KTConfig::getSingleton();
        $alwaysAll = $KTConfig->get("alwaysShowAll");

        $name = KTUtil::arrayGet($_REQUEST, 'search_name', KTUtil::arrayGet($_REQUEST, 'old_search'));
        $show_all = KTUtil::arrayGet($_REQUEST, 'show_all', $alwaysAll);
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');

        $no_search = true;

        if (KTUtil::arrayGet($_REQUEST, 'do_search', false) != false) {
            $no_search = false;
        }

        if ($name == '*') {
            $show_all = true;
            $name = '';
        }

        $search_fields = array();
        $search_fields[] =  new KTStringWidget(_kt('Username'), _kt("Enter part of the person's username.  e.g. <strong>ra</strong> will match <strong>brad</strong>."), 'search_name', $name, $this->oPage, true);

        // FIXME handle group search stuff.
        $search_results = null;
        if (!empty($name)) {
            $search_results =& User::getList('WHERE username LIKE \'%' . DBUtil::escapeSimple($name) . '%\' AND id > 0');
        } else if ($show_all !== false) {
            $search_results =& User::getList('id > 0');
            $no_search = false;
			$name = '*';
        }

        $aAuthenticationSources =& KTAuthenticationSource::getList();

        $bCanAdd = true;
        if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            $path = KTPluginUtil::getPluginPath('ktdms.wintools');
            require_once($path . 'baobabkeyutil.inc.php');
            $bCanAdd = BaobabKeyUtil::canAddUser();
            if (PEAR::isError($bCanAdd)) {
                $bCanAdd = false;
            }
        }

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/useradmin");
        $aTemplateData = array(
            "context" => $this,
            "search_fields" => $search_fields,
            "search_results" => $search_results,
            "no_search" => $no_search,
            "authentication_sources" => $aAuthenticationSources,
            "old_search" => $name,
            "can_add" => $bCanAdd,
        );
        return $oTemplate->render($aTemplateData);
    }


    function do_addUser() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->oPage->setBreadcrumbDetails(_kt('add a new user'));
        $this->oPage->setTitle(_kt("Add New User"));

        // Get persisted params
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        $username = KTUtil::arrayGet($_REQUEST, 'newusername');
        $emailAddress = KTUtil::arrayGet($_REQUEST, 'email_address');
        $mobileNum = KTUtil::arrayGet($_REQUEST, 'mobile_number');
        $maxSessions = KTUtil::arrayGet($_REQUEST, 'max_sessions', '3');

        // Check if parameters are being persisted before checking for the email notification parameter - otherwise it will always be true
        if(isset($_REQUEST['name']) || isset($_REQUEST['newusername'])){
            $emailNotification = (KTUtil::arrayGet($_REQUEST, 'email_notification') == 'on') ? true : false;
        }else{
            $emailNotification = true;
        }

        $show_all = KTUtil::arrayGet($_REQUEST, 'show_all', false);
        $add_user = KTUtil::arrayGet($_REQUEST, 'add_user', false);
        if ($add_user !== false) { $add_user = true; }
        $edit_user = KTUtil::arrayGet($_REQUEST, 'edit_user', false);

        $aOptions = array('autocomplete' => false);

        // sometimes even admin is restricted in what they can do.

		$KTConfig =& KTConfig::getSingleton();
		$minLength = ((int) $KTConfig->get('user_prefs/passwordLength', 6));
		$restrictAdmin = ((bool) $KTConfig->get('user_prefs/restrictAdminPasswords', false));
		$passwordAddRequirement = '';
		if ($restrictAdmin) {
		     $passwordAddRequirement = ' ' . sprintf('Password must be at least %d characters long.', $minLength);
		}

        $add_fields = array();
        $add_fields[] =  new KTStringWidget(_kt('Username'), sprintf(_kt('The username the user will enter to gain access to %s.  e.g. <strong>jsmith</strong>'), APP_NAME), 'newusername', $username, $this->oPage, true, null, null, $aOptions);
        $add_fields[] =  new KTStringWidget(_kt('Name'), _kt('The full name of the user.  This is shown in reports and listings.  e.g. <strong>John Smith</strong>'), 'name', $name, $this->oPage, true, null, null, $aOptions);
        $add_fields[] =  new KTStringWidget(_kt('Email Address'), _kt('The email address of the user.  Notifications and alerts are mailed to this address if <strong>email notifications</strong> is set below. e.g. <strong>jsmith@acme.com</strong>'), 'email_address', $emailAddress, $this->oPage, false, null, null, $aOptions);
        $add_fields[] =  new KTCheckboxWidget(_kt('Email Notifications'), _kt("If this is specified then the user will have notifications sent to the email address entered above.  If it isn't set, then the user will only see notifications on the <strong>Dashboard</strong>"), 'email_notifications', $emailNotification, $this->oPage, false, null, null, $aOptions);
        $add_fields[] =  new KTPasswordWidget(_kt('Password'), _kt('Specify an initial password for the user.') . $passwordAddRequirement, 'new_password', null, $this->oPage, true, null, null, $aOptions);
        $add_fields[] =  new KTPasswordWidget(_kt('Confirm Password'), _kt('Confirm the password specified above.'), 'confirm_password', null, $this->oPage, true, null, null, $aOptions);
        // nice, easy bits.
        $add_fields[] =  new KTStringWidget(_kt('Mobile Number'), _kt("The mobile phone number of the user.  e.g. <strong>999 9999 999</strong>"), 'mobile_number', $mobileNum, $this->oPage, false, null, null, $aOptions);
        $add_fields[] =  new KTStringWidget(_kt('Maximum Sessions'), _kt('As a safety precaution, it is useful to limit the number of times a given account can log in, before logging out.  This prevents a single account being used by many different people.'), 'max_sessions', $maxSessions, $this->oPage, true, null, null, $aOptions);

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/adduser");
        $aTemplateData = array(
            "context" => $this,
            "add_fields" => $add_fields,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_addUserFromSource() {
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $sProvider = $oSource->getAuthenticationProvider();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->aBreadcrumbs[] = array('url' => KTUtil::addQueryStringSelf('action=addUser'), 'name' => _kt('add a new user'));
        $oProvider->aBreadcrumbs = $this->aBreadcrumbs;
        $oProvider->oPage->setBreadcrumbDetails($oSource->getName());
        $oProvider->oPage->setTitle(_kt("Add New User"));

        $oProvider->dispatch();
        exit(0);
    }

    function do_editUser() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->oPage->setBreadcrumbDetails(_kt('modify user details'));
        $this->oPage->setTitle(_kt("Modify User Details"));

        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser =& User::get($user_id);

        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');

        if (PEAR::isError($oUser) || $oUser == false) {
            $this->errorRedirectToMain(_kt('Please select a user first.'));
            exit(0);
        }

        $name = KTUtil::arrayGet($_REQUEST, 'name', $oUser->getName());
        $username = KTUtil::arrayGet($_REQUEST, 'newusername', $oUser->getUsername());
        $emailAddress = KTUtil::arrayGet($_REQUEST, 'email_address', $oUser->getEmail());
        $mobileNum = KTUtil::arrayGet($_REQUEST, 'mobile_number', $oUser->getMobile());
        $maxSessions = KTUtil::arrayGet($_REQUEST, 'max_sessions', $oUser->getMaxSessions());

        if(isset($_REQUEST['name']) || isset($_REQUEST['newusername'])){
            $emailNotification = (KTUtil::arrayGet($_REQUEST, 'email_notification') == 'on') ? true : false;
        }else{
            $emailNotification = $oUser->getEmailNotification();
        }

        $this->aBreadcrumbs[] = array('name' => $oUser->getName());

        $edit_fields = array();
        $edit_fields[] =  new KTStringWidget(_kt('Username'), sprintf(_kt('The username the user will enter to gain access to %s.  e.g. <strong>jsmith</strong>'), APP_NAME), 'newusername', $username, $this->oPage, true);
        $edit_fields[] =  new KTStringWidget(_kt('Name'), _kt('The full name of the user.  This is shown in reports and listings.  e.g. <strong>John Smith</strong>'), 'name', $name, $this->oPage, true);
        $edit_fields[] =  new KTStringWidget(_kt('Email Address'), _kt('The email address of the user.  Notifications and alerts are mailed to this address if <strong>email notifications</strong> is set below. e.g. <strong>jsmith@acme.com</strong>'), 'email_address', $emailAddress, $this->oPage, false);
        $edit_fields[] =  new KTCheckboxWidget(_kt('Email Notifications'), _kt('If this is specified then the user will have notifications sent to the email address entered above.  If it is not set, then the user will only see notifications on the <strong>Dashboard</strong>'), 'email_notifications', $emailNotification, $this->oPage, false);
        $edit_fields[] =  new KTStringWidget(_kt('Mobile Number'), _kt("The mobile phone number of the user.  e.g. <strong>999 9999 999</strong>"), 'mobile_number', $mobileNum, $this->oPage, false);
        $edit_fields[] =  new KTStringWidget(_kt('Maximum Sessions'), _kt('As a safety precaution, it is useful to limit the number of times a given account can log in, before logging out.  This prevents a single account being used by many different people.'), 'max_sessions', $maxSessions, $this->oPage, true);

        $oAuthenticationSource = KTAuthenticationSource::getForUser($oUser);
        if (is_null($oAuthenticationSource)) {
            $oProvider =& new KTBuiltinAuthenticationProvider;
        } else {
            $sProvider = $oAuthenticationSource->getAuthenticationProvider();
            $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
            $oProvider = $oRegistry->getAuthenticationProvider($sProvider);
        }

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/edituser");
        $aTemplateData = array(
            "context" => $this,
            "edit_fields" => $edit_fields,
            "edit_user" => $oUser,
            "provider" => $oProvider,
            "source" => $oAuthenticationSource,
            'old_search' => $old_search,
        );
        return $oTemplate->render($aTemplateData);
    }


    function do_setPassword() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->oPage->setBreadcrumbDetails(_kt('change user password'));
        $this->oPage->setTitle(_kt("Change User Password"));

        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');

        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser =& User::get($user_id);

        if (PEAR::isError($oUser) || $oUser == false) {
            $this->errorRedirectToMain(_kt('Please select a user first.'));
            exit(0);
        }

        $this->aBreadcrumbs[] = array('name' => $oUser->getName());

        $edit_fields = array();
        $edit_fields[] =  new KTPasswordWidget(_kt('Password'), _kt('Specify an initial password for the user.'), 'new_password', null, $this->oPage, true);
        $edit_fields[] =  new KTPasswordWidget(_kt('Confirm Password'), _kt('Confirm the password specified above.'), 'confirm_password', null, $this->oPage, true);

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/updatepassword");
        $aTemplateData = array(
            "context" => $this,
            "edit_fields" => $edit_fields,
            "edit_user" => $oUser,
            'old_search' => $old_search,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_updatePassword() {
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');

        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');

        $password = KTUtil::arrayGet($_REQUEST, 'new_password');
        $confirm_password = KTUtil::arrayGet($_REQUEST, 'confirm_password');

   		$KTConfig =& KTConfig::getSingleton();
		$minLength = ((int) $KTConfig->get('user_prefs/passwordLength', 6));
		$restrictAdmin = ((bool) $KTConfig->get('user_prefs/restrictAdminPasswords', false));

        if ($restrictAdmin && (strlen($password) < $minLength)) {
		    $this->errorRedirectToMain(sprintf(_kt("The password must be at least %d characters long."), $minLength));
		} else if (empty($password)) {
            $this->errorRedirectToMain(_kt("You must specify a password for the user."));
        } else if ($password !== $confirm_password) {
            $this->errorRedirectToMain(_kt("The passwords you specified do not match."));
        }
        // FIXME more validation would be useful.
        // validated and ready..
        $this->startTransaction();

        $oUser =& User::get($user_id);
        if (PEAR::isError($oUser) || $oUser == false) {
            $this->errorRedirectToMain(_kt("Please select a user to modify first."));
        }


        // FIXME this almost certainly has side-effects.  do we _really_ want
        $oUser->setPassword(md5($password)); //

        $res = $oUser->update();
        //$res = $oUser->doLimitedUpdate(); // ignores a fix blacklist of items.

        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectoToMain(_kt('Failed to update user.'));
        }

        $this->commitTransaction();
        $this->successRedirectToMain(_kt('User information updated.'));

    }

    function do_editUserSource() {
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser =& $this->oValidator->validateUser($user_id);
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->aBreadcrumbs[] = array('name' => $oUser->getName());

        $oAuthenticationSource = KTAuthenticationSource::getForUser($oUser);
        if (is_null($oAuthenticationSource)) {
            $oProvider =& new KTBuiltinAuthenticationProvider;
        } else {
            $sProvider = $oAuthenticationSource->getAuthenticationProvider();
            $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
            $oProvider = $oRegistry->getAuthenticationProvider($sProvider);
        }

        $oProvider->subDispatch($this);
        exit();
    }

    function do_editgroups() {
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser = User::get($user_id);
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');
        if ((PEAR::isError($oUser)) || ($oUser === false)) {
            $this->errorRedirectToMain(_kt('No such user.'), sprintf("old_search=%s&do_search=1", $old_search));
        }



        $this->aBreadcrumbs[] = array('name' => $oUser->getName());
        $this->oPage->setBreadcrumbDetails(_kt('edit groups'));
        $this->oPage->setTitle(sprintf(_kt("Edit %s's groups"), $oUser->getName()));
        // generate a list of groups this user is authorised to assign.

        /* FIXME there is a nasty side-effect:  if a user cannot assign a group
        * to a user, and that user _had_ that group pre-edit,
        * then their privileges are revoked.
        * is there _any_ way to fix that?
        */

        $aInitialGroups = GroupUtil::listGroupsForUser($oUser);
        $aAllGroups = GroupUtil::listGroups();

        $aUserGroups = array();
        $aFreeGroups = array();
        foreach ($aInitialGroups as $oGroup) {
            $aUserGroups[$oGroup->getId()] = $oGroup;
        }
        foreach ($aAllGroups as $oGroup) {
            if (!array_key_exists($oGroup->getId(), $aUserGroups)) {
                $aFreeGroups[$oGroup->getId()] = $oGroup;
            }
        }

	$oJSONWidget = new KTJSONLookupWidget(_kt('Groups'),
					      _kt('Select the groups which this user should belong to from the left-hand list and then click the <strong>right pointing arrows</strong>. Once you have added all the groups that you require, press <strong>save changes</strong>.'),
					      'groups', '', $this->oPage, false, null, null,
					      array('action'=>'getGroups',
						    'assigned' => $aUserGroups,
						    'multi'=>'true',
						    'size'=>'8'));

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/usergroups");
        $aTemplateData = array(
            "context" => $this,
            "unused_groups" => $aFreeGroups,
            "user_groups" => $aUserGroups,
            "edit_user" => $oUser,
	    "widget" => $oJSONWidget,
            'old_search' => $old_search,
        );
        return $oTemplate->render($aTemplateData);
    }


    function json_getGroups() {
        $sFilter = KTUtil::arrayGet($_REQUEST, 'filter', false);
        $aGroupList = array('off' => _kt('-- Please filter --'));

        if($sFilter && trim($sFilter)) {
            $aGroups = Group::getList(sprintf('name like "%%%s%%"', $sFilter));
            $aGroupList = array();
            foreach($aGroups as $oGroup) {
                $aGroupList[$oGroup->getId()] = $oGroup->getName();
            }
        }

        return $aGroupList;
    }


    function do_saveUser() {
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');
        $aErrorOptions = array(
                'redirect_to' => array('editUser', sprintf('user_id=%d&old_search=%s&do_search=1', $user_id, $old_search))
        );
        $aInputKeys = array('newusername', 'name', 'email_address', 'email_notifications', 'mobile_number', 'max_sessions');
        $this->persistParams($aInputKeys);

        $name = $this->oValidator->validateString(
                KTUtil::arrayGet($_REQUEST, 'name'),
                KTUtil::meldOptions($aErrorOptions, array('message' => _kt("You must provide a name")))
        );

        $username = $this->oValidator->validateString(
                KTUtil::arrayGet($_REQUEST, 'newusername'),
                KTUtil::meldOptions($aErrorOptions, array('message' => _kt("You must provide a username")))
        );

        $email_address = KTUtil::arrayGet($_REQUEST, 'email_address');
        if(strlen(trim($email_address))) {
                $email_address = $this->oValidator->validateEmailAddress($email_address, $aErrorOptions);
        }

        $email_notifications = KTUtil::arrayGet($_REQUEST, 'email_notifications', false);
        if ($email_notifications !== false) $email_notifications = true;

        $mobile_number = KTUtil::arrayGet($_REQUEST, 'mobile_number');

        $max_sessions = KTUtil::arrayGet($_REQUEST, 'max_sessions', '3', false);

        // FIXME more validation would be useful.
        // validated and ready..
        $this->startTransaction();

        $oUser =& User::get($user_id);
        if (PEAR::isError($oUser) || $oUser == false) {
            $this->errorRedirectToMain(_kt("Please select a user to modify first."), sprintf("old_search=%s&do_search=1", $old_search));
        }

        $dupUser =& User::getByUserName($username);
        if(!PEAR::isError($dupUser)) {
            if ($dupUser->getId() != $oUser->getId()) {
                $this->errorRedirectTo('addUser', _kt("A user with that username already exists"));
            }
        }

        $oUser->setName($name);
        $oUser->setUsername($username);  // ?
        $oUser->setEmail($email_address);
        $oUser->setEmailNotification($email_notifications);
        $oUser->setMobile($mobile_number);
        $oUser->setMaxSessions($max_sessions);

        // old system used the very evil store.php.
        // here we need to _force_ a limited update of the object, via a db statement.
        //
        $res = $oUser->update();
        // $res = $oUser->doLimitedUpdate(); // ignores a fix blacklist of items.



        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectoToMain(_kt('Failed to update user.'), sprintf("old_search=%s&do_search=1", $old_search));
        }

        $this->commitTransaction();
        $this->successRedirectToMain(_kt('User information updated.'), sprintf("old_search=%s&do_search=1", $old_search));
    }

    function do_createUser() {
        // FIXME generate and pass the error stack to adduser.
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');
        $aErrorOptions = array(
                'redirect_to' => array('addUser', sprintf('old_search=%s&do_search=1', $old_search))
        );
        $aInputKeys = array('newusername', 'name', 'email_address', 'email_notifications', 'mobile_number', 'max_sessions');
        $this->persistParams($aInputKeys);

        $username = $this->oValidator->validateString(
                KTUtil::arrayGet($_REQUEST, 'newusername'),
                KTUtil::meldOptions($aErrorOptions, array('message' => _kt("You must specify a new username.")))
        );

        $name = $this->oValidator->validateString(
                KTUtil::arrayGet($_REQUEST, 'name'),
                KTUtil::meldOptions($aErrorOptions, array('message' => _kt("You must provide a name")))
        );


        $email_address = KTUtil::arrayGet($_REQUEST, 'email_address');
        $email_notifications = KTUtil::arrayGet($_REQUEST, 'email_notifications', false);
        if ($email_notifications !== false) $email_notifications = true;
        $mobile_number = KTUtil::arrayGet($_REQUEST, 'mobile_number');

        $max_sessions = $this->oValidator->validateInteger(
                KTUtil::arrayGet($_REQUEST, 'max_sessions'),
                KTUtil::meldOptions($aErrorOptions, array('message' => _kt("You must specify a numeric value for maximum sessions.")))
        );

        $password = KTUtil::arrayGet($_REQUEST, 'new_password');
        $confirm_password = KTUtil::arrayGet($_REQUEST, 'confirm_password');

        $KTConfig =& KTConfig::getSingleton();
        $minLength = ((int) $KTConfig->get('user_prefs/passwordLength', 6));
        $restrictAdmin = ((bool) $KTConfig->get('user_prefs/restrictAdminPasswords', false));

        if ($restrictAdmin && (strlen($password) < $minLength)) {
    	    $this->errorRedirectTo('addUser', sprintf(_kt("The password must be at least %d characters long."), $minLength), sprintf("old_search=%s&do_search=1", $old_search));
    	} else if (empty($password)) {
            $this->errorRedirectTo('addUser', _kt("You must specify a password for the user."), sprintf("old_search=%s&do_search=1", $old_search));
        } else if ($password !== $confirm_password) {
            $this->errorRedirectTo('addUser', _kt("The passwords you specified do not match."), sprintf("old_search=%s&do_search=1", $old_search));
        }

        if(preg_match('/[\!\$\#\%\^\&\*]/', $username)){
        	$this->errorRedirectTo('addUser', _kt("You have entered an invalid character in your username."));
        }

        if(preg_match('/[\!\$\#\%\^\&\*]/', $name)){
        	$this->errorRedirectTo('addUser', _kt("You have entered an invalid character in your name."));
        }

        $dupUser =& User::getByUserName($username);
        if(!PEAR::isError($dupUser)) {
            $this->errorRedirectTo('addUser', _kt("A user with that username already exists"));
        }



        $oUser =& User::createFromArray(array(
            "sUsername" => $username,
            "sName" => $name,
            "sPassword" => md5($password),
            "iQuotaMax" => 0,
            "iQuotaCurrent" => 0,
            "sEmail" => $email_address,
            "bEmailNotification" => $email_notifications,
            "sMobile" => $mobile_number,
            "bSmsNotification" => false,   // FIXME do we auto-act if the user has a mobile?
            "iMaxSessions" => $max_sessions,
        ));

        if (PEAR::isError($oUser) || ($oUser == false)) {
            $this->errorRedirectToMain(_kt("failed to create user."), sprintf("old_search=%s&do_search=1", $old_search));
            exit(0);
        }

        $this->successRedirectToMain(_kt('Created new user') . ': ' . $oUser->getUsername(), 'name=' . $oUser->getUsername(), sprintf("old_search=%s&do_search=1", $old_search));
    }

    function do_deleteUser() {
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser = User::get($user_id);
        if ((PEAR::isError($oUser)) || ($oUser === false)) {
            $this->errorRedirectToMain(_kt('Please select a user first.'));
        }
        $res = $oUser->delete();
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(sprintf(_kt('Unable to delete user - the user may still be referred by documents.'), $res->getMessage()), sprintf("old_search=%s&do_search=1", $old_search));
        }

        $this->successRedirectToMain(_kt('User deleted') . ': ' . $oUser->getName(), sprintf("old_search=%s&do_search=1", $old_search));
    }

    function do_updateGroups() {
        $old_search = KTUtil::arrayGet($_REQUEST, 'old_search');
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser = User::get($user_id);
        if ((PEAR::isError($oUser)) || ($oUser === false)) {
            $this->errorRedirectToMain(_kt('Please select a user first.'), sprintf("old_search=%s&do_search=1", $old_search));
        }
        $groupAdded = KTUtil::arrayGet($_REQUEST, 'groups_items_added','');
        $groupRemoved = KTUtil::arrayGet($_REQUEST, 'groups_items_removed','');


        $aGroupToAddIDs = explode(",", $groupAdded);
        $aGroupToRemoveIDs = explode(",", $groupRemoved);

        // FIXME we need to ensure that only groups which are allocatable by the admin are added here.

        // FIXME what groups are _allocatable_?

        $this->startTransaction();
        $groupsAdded = array();
        $groupsRemoved = array();

		$addWarnings = array();
		$removeWarnings = array();

        foreach ($aGroupToAddIDs as $iGroupID ) {
            if ($iGroupID > 0) {
                $oGroup = Group::get($iGroupID);
				$memberReason = GroupUtil::getMembershipReason($oUser, $oGroup);
				//var_dump($memberReason);
				if (!(PEAR::isError($memberReason) || is_null($memberReason))) {
					$addWarnings[] = $memberReason;
				}
                $res = $oGroup->addMember($oUser);
                if (PEAR::isError($res) || $res == false) {
                    $this->errorRedirectToMain(sprintf(_kt('Unable to add user to group "%s"'), $oGroup->getName()), sprintf("old_search=%s&do_search=1", $old_search));
                } else {
				    $groupsAdded[] = $oGroup->getName();

				}
            }
        }

        // Remove groups
        foreach ($aGroupToRemoveIDs as $iGroupID ) {
            if ($iGroupID > 0) {
                $oGroup = Group::get($iGroupID);
                $res = $oGroup->removeMember($oUser);
                if (PEAR::isError($res) || $res == false) {
                    $this->errorRedirectToMain(sprintf(_kt('Unable to remove user from group "%s"'), $oGroup->getName()), sprintf("old_search=%s&do_search=1", $old_search));
                } else {
				   $groupsRemoved[] = $oGroup->getName();
					$memberReason = GroupUtil::getMembershipReason($oUser, $oGroup);
					//var_dump($memberReason);
					if (!(PEAR::isError($memberReason) || is_null($memberReason))) {
						$removeWarnings[] = $memberReason;
					}
				}
            }
        }

		if (!empty($addWarnings)) {
		    $sWarnStr = _kt('Warning:  the user was already a member of some subgroups') . ' &mdash; ';
			$sWarnStr .= implode(', ', $addWarnings);
			$_SESSION['KTInfoMessage'][] = $sWarnStr;
		}

		if (!empty($removeWarnings)) {
		    $sWarnStr = _kt('Warning:  the user is still a member of some subgroups') . ' &mdash; ';
			$sWarnStr .= implode(', ', $removeWarnings);
			$_SESSION['KTInfoMessage'][] = $sWarnStr;
		}

        $msg = '';
        if (!empty($groupsAdded)) { $msg .= ' ' . _kt('Added to groups') . ': ' . implode(', ', $groupsAdded) . '.'; }
        if (!empty($groupsRemoved)) { $msg .= ' ' . _kt('Removed from groups') . ': ' . implode(', ',$groupsRemoved) . '.'; }

        if (!Permission::userIsSystemAdministrator($_SESSION['userID'])) {
            $this->rollbackTransaction();
            $this->errorRedirectTo('editgroups', _kt('For security purposes, you cannot remove your own administration priviledges.'), sprintf('user_id=%d&do_search=1&old_search=%s', $oUser->getId(), $old_search));
            exit(0);
        }

        $this->commitTransaction();
        $this->successRedirectToMain($msg, sprintf("old_search=%s&do_search=1", $old_search));
    }

	function getGroupStringForUser($oUser) {
		$aGroupNames = array();
		$aGroups = GroupUtil::listGroupsForUser($oUser);
		$MAX_GROUPS = 6;
		$add_elipsis = false;
		if (count($aGroups) == 0) { return _kt('User is currently not a member of any groups.'); }
		if (count($aGroups) > $MAX_GROUPS) {
		    $aGroups = array_slice($aGroups, 0, $MAX_GROUPS);
			$add_elipsis = true;
		}
		foreach ($aGroups as $oGroup) {
		    $aGroupNames[] = $oGroup->getName();
		}
		if ($add_elipsis) {
		    $aGroupNames[] = '&hellip;';
		}

		return implode(', ', $aGroupNames);
	}



    // change enabled / disabled status of users
    function do_change_enabled() {

        $this->startTransaction();
        $iLicenses = 0;
        $bRequireLicenses = false;
        if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            $path = KTPluginUtil::getPluginPath('ktdms.wintools');
            require_once($path . 'baobabkeyutil.inc.php');
            $iLicenses = BaobabKeyUtil::getLicenseCount();
            $bRequireLicenses = true;
        }
        // admin and anonymous are automatically ignored here.
        $iEnabledUsers = User::getNumberEnabledUsers();

 		if($_REQUEST['update_value'] == 'enable')
 		{
	        foreach(KTUtil::arrayGet($_REQUEST, 'edit_user', array()) as $sUserId => $v) {
	            // check that we haven't hit max user limit
	            if($bRequireLicenses && $iEnabledUsers >= $iLicenses) {
	                // if so, add to error messages, but commit transaction (break this loop)
	                $_SESSION['KTErrorMessage'][] = _kt('You may only have ') . $iLicenses . _kt(' users enabled at one time.');
	                break;
	            }

	            // else enable user
	            $oUser = User::get((int)$sUserId);
	            if(PEAR::isError($oUser)) { $this->errorRedirectToMain(_kt('Error getting user object')); }
	            $oUser->enable();
	            $res = $oUser->update();
	            if(PEAR::isError($res)) { $this->errorRedirectToMain(_kt('Error updating user')); }
	            $iEnabledUsers++;
	        }
 		}

 		if($_REQUEST['update_value'] == 'disable')
 		{
	        //echo 'got into disable';
	        //exit;

	        foreach(KTUtil::arrayGet($_REQUEST, 'edit_user', array()) as $sUserId => $v) {
	            $oUser = User::get((int)$sUserId);
	            if(PEAR::isError($oUser)) { $this->errorRedirectToMain(_kt('Error getting user object')); }
	            $oUser->disable();
	            $res = $oUser->update();
	            if(PEAR::isError($res)) { $this->errorRedirectToMain(_kt('Error updating user')); }
	            $iEnabledUsers--;
	        }
 		}

 		if($_REQUEST['update_value'] == 'delete')
 		{
 			//echo 'Delete called';

 			foreach(KTUtil::arrayGet($_REQUEST, 'edit_user', array()) as $sUserId => $v) {
	            $oUser = User::get((int)$sUserId);
	            if(PEAR::isError($oUser)) { $this->errorRedirectToMain(_kt('Error getting user object')); }
	            $oUser->delete();
	            $res = $oUser->update();
	            if(PEAR::isError($res)) { $this->errorRedirectToMain(_kt('Error updating user')); }
	            $iEnabledUsers--;
	        }
 		}

        $this->commitTransaction();
        $this->successRedirectToMain(_kt('Users updated'));

    }

}

?>
