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
        $input = $_REQUEST['key'];

        // check if user already exists and redirect to login
        $key = 'skfjiwefjaldi';
        $user = KTUtil::decode($input, $key);
        $user = json_decode($user);

        $oUser = User::get($user->id);

        if(PEAR::isError($oUser)){
            $default->log->error('Invited login: error getting user obj - '. $oUser->getMessage());

            $rootUrl = $default->rootUrl;
            redirect($rootUrl. '/login.php');
            exit;
        }

        $disabled = $oUser->getDisabled();
        $fullname = '';
        $username = isset($_REQUEST['username']) ? $_REQUEST['username'] : $oUser->getEmail();

        if($disabled != 3){
            $default->log->debug('Invited login: user already created - '.$user->id);

            $rootUrl = $default->rootUrl;
            redirect($rootUrl. '/login.php');
            exit;
        }

        // Validate the details
        if(isset($_POST['save'])){
            $fullname = $_REQUEST['fullname'];
            $password = $_REQUEST['password'];
            $confirm_password = $_REQUEST['confirm_password'];

            if(preg_match('/[\!\$\#\%\^\&\*]/', $fullname)){
                $errorMessage = _kt('You have entered an invalid character in your name, the following characters are not allowed: !$#%^&*.').' ';
            }

            if(empty($fullname)){
                $errorMessage = _kt('Please enter your full name.').' ';
            }

            if(empty($username)){
                $errorMessage = _kt('Please enter a username.').' ';
            }

            if(strlen($password) < 6){
                $errorMessage .= _kt('Your password must be longer than 6 characters.').' ';
            }

            if($password != $confirm_password){
                $errorMessage .= _kt('The passwords do not match.').' ';
            }

            if(empty($errorMessage)){
                $default->log->debug('Invited login: new user created - '.$oUser->getId());
                $session = $this->saveDetails($oUser, $username, $fullname, $password);

                if(PEAR::isError($session)){
                    $errorMessage = _kt('An error occurred during login: '.$session->getMessage());
                    redirect('/login?errorMessage='.$errorMessage);
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
            'key' => $input,
            'use_email' => $useEmail,
            'fullname' => $fullname,
            'username' => $username
        );
        return $oTemplate->render($aTemplateData);
    }

    public function saveDetails($oUser, $username, $fullname, $password)
    {
        // Update the user details
        $oUser->setUserName($username);
        $oUser->setName($fullname);
        $oUser->setPassword(md5($password));
        $oUser->setDisabled(0);
        $oUser->update();

        // Refresh the user object
        $oUser = User::get($oUser->getId());

        // Create the session and log the user in
        $session = new Session();
        $sessionID = $session->create($oUser);
        if (PEAR::isError($sessionID)) {
            $default->log->error("Invited login: couldn\'t create session for user ({$oUser->getId()}) - {$sessionID->getMessage()}");
            return $sessionID;
        }

        $rootUrl = $default->rootUrl;
        $redirect = '/browse.php';
        if (KTPluginUtil::pluginIsActive('gettingstarted.plugin')) {
            $path = KTPluginUtil::getPluginPath('gettingstarted.plugin');
            $uri = str_replace(KT_DIR, '', $path);
            $redirect = $uri . 'GettingStarted.php';
        }
        redirect($rootUrl . $redirect);
        exit;
    }
}

$dispatcher = new NewUserLoginDispatcher();
$dispatcher->dispatch();

?>