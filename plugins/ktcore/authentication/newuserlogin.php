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

// main library routines and defaults
require_once('../../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');

class NewUserLoginDispatcher extends KTDispatcher {

    public function do_main()
    {
        global $default;
        $key = $_REQUEST['key'];

        // check if user already exists and redirect to login
        //$key = 'skfjiwefjaldi';
        //$user = KTUtil::decode($input, $key);
        //$user = json_decode($user);
        $userId = str_replace('88', '', $key);
        $userId = base_convert($userId, 25, 10);
        $userId = (int)$userId / 354;

        $user = User::get($userId);

        if (PEAR::isError($user)) {
            $errorMessage = _kt('An error occurred: '.$user->getMessage());
            $default->log->error('Invited login: error getting user obj - '. $user->getMessage());

            $rootUrl = $default->rootUrl;
            redirect($rootUrl. '/login.php?errorMessage='.$errorMessage);
            exit;
        }

        // Get the user status flag
        // 0: live; 1: disabled; 2: deleted; 3: invited; 4: shared
        $disabled = $user->getDisabled();

        // Check the number of available licenses (but not for shared users)
        if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            $path = KTPluginUtil::getPluginPath('ktdms.wintools');
            require_once($path . 'baobabkeyutil.inc.php');
            // value is true for shared users (disabled = 4) and if there are more licenses
            $canAdd = ($disabled != 4) ? BaobabKeyUtil::canAddUser() : true;

            if (PEAR::isError($canAdd)) {
                $errorMessage = _kt('An error occurred: No new users are allowed in the system, please contact your System Administrator');
                $default->log->error('Invited login: No licenses available '. $canAdd->getMessage());
            }
        }

        $fullname = '';
        $username = isset($_REQUEST['username']) ? $_REQUEST['username'] : $user->getEmail();

        // perform specific actions for values other than 3 (3 should go on to use the code following the switch)
        switch ($disabled) {
            case 1:
            case 2: {
                $default->log->error("Invited login: user ({$userId}) has been disabled or deleted (status = {$disabled})");
                $errorMessage = _kt('Your login is no longer valid, please contact your System Administrator');
                redirect($rootUrl. '/login.php?errorMessage='.$errorMessage);
                exit;
                // no need for a break since we are exiting the current script
            }
            case 3: {
                // do nothing, continue with script
                break;
            }
            case 4: {
                // check if the user has previously logged in (they will have a user name and password,)
                // if so drop through to default, else break and continue with script
                if (($user->getName() == '') || ($user->getPassword() == '')) {
                    break;
                }
            }
            default: {
                $default->log->debug('Invited login: user already created - ' . $userId);
                redirect($rootUrl. '/login.php');
                exit;
                // no need for a break since we are exiting the current script
            }
        }

        // old code before switch version - retained in case of problems with switch.  DOES NOT work for shared users.
        /*if ($disabled != 3 && $disabled != 4) {
            $rootUrl = $default->rootUrl;

            if ($disabled == 2 || $disabled == 1) {
                $default->log->error("Invited login: user ({$userId}) has been disabled or deleted (status = {$disabled})");
                $errorMessage = _kt('Your login is no longer valid, please contact your System Administrator');
                redirect($rootUrl. '/login.php?errorMessage='.$errorMessage);
                exit;
            }

            $default->log->debug('Invited login: user already created - '.$userId);
            redirect($rootUrl. '/login.php');
            exit;
        }*/

        // Validate the details
        if (isset($_POST['save'])) {
            $fullname = $_REQUEST['fullname'];
            $password = $_REQUEST['password'];
            $confirm_password = $_REQUEST['confirm_password'];

            if (preg_match('/[\!\$\#\%\^\&\*]/', $fullname)) {
                $errorMessage = _kt('You have entered an invalid character in your name, the following characters are not allowed: !$#%^&*.').' ';
            }

            if (empty($fullname)) {
                $errorMessage = _kt('Please enter your full name.').' ';
            }

            if (empty($username)) {
                $errorMessage = _kt('Please enter a username.').' ';
            }

            if (strlen($password) < 6) {
                $errorMessage .= _kt('Your password must be longer than 6 characters.').' ';
            }

            if ($password != $confirm_password) {
                $errorMessage .= _kt('The passwords do not match.').' ';
            }

            if (empty($errorMessage)) {
                $default->log->debug('Invited login: new user created - '.$userId);

                $newDisabled = ($disabled == 3) ? 0 : $disabled;
                $session = $this->saveDetails($user, $username, $fullname, $password, $newDisabled);

                if (PEAR::isError($session)) {
                    $errorMessage = _kt('An error occurred during login: '.$session->getMessage());
                    $rootUrl = $default->rootUrl;
                    redirect($rootUrl . '/login?errorMessage='.$errorMessage);
                    exit;
                }
            }
        }

        // Check if using the username or email address
        $oConfig = KTConfig::getSingleton();
        $useEmail = $oConfig->get('user_prefs/useEmailLogin', false);

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/authentication/invite_login');
        $aTemplateData = array(
            'errorMessage' => $errorMessage,
            'key' => $key,
            'use_email' => $useEmail,
            'fullname' => $fullname,
            'username' => $username
        );

        return $oTemplate->render($aTemplateData);
    }

    public function saveDetails($user, $username, $fullname, $password, $disabled = 0)
    {
        // Update the user details
        $user->setUserName($username);
        $user->setName($fullname);
        $user->setPassword(md5($password));
        $user->setDisabled($disabled);
        $user->update();

        // Refresh the user object
        $user = User::get($user->getId());

        // Create the session and log the user in
        $session = new Session();
        $sessionID = $session->create($user);
        if (PEAR::isError($sessionID)) {
            global $default;
            $default->log->error("Invited login: couldn\'t create session for user ({$user->getId()}) - {$sessionID->getMessage()}");
            return $sessionID;
        }

        $rootUrl = $default->rootUrl;
        $redirect = '/browse.php';
        /*
        if (KTPluginUtil::pluginIsActive('gettingstarted.plugin')) {

            // Set the first login pref to prevent redirecting to getting started again
            $user_pref_path = KTPluginUtil::getPluginPath('user.preferences.plugin');
            require_once($user_pref_path . DIRECTORY_SEPARATOR . 'UserPreferences.inc.php');
            UserPreferences::saveUserPreferences($user->getId(), 'firstLogin', date('Y-m-d H:i:s'));

            // redirect to the getting started page for first time login
            $path = KTPluginUtil::getPluginPath('gettingstarted.plugin');
            $uri = str_replace(KT_DIR, '', $path);
            $redirect = $uri . 'GettingStarted.php';
        }
        */

        redirect($rootUrl . $redirect);
        exit;
    }
}

$dispatcher = new NewUserLoginDispatcher();
$dispatcher->dispatch();

?>
