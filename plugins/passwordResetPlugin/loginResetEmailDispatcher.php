<?php
/**
 * $Id: $
 *
 * This page handles logging a user into the dms.
 * This page displays the login form, and performs the business logic login processing.
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
 */

// FIXME This should inherit from or otherwise share code with loginResetDispatcher.  There is a lot of duplication.

// main library routines and defaults
require_once('../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/session/control.inc');
require_once(KT_LIB_DIR . '/session/Session.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');
require_once(KT_LIB_DIR . '/help/help.inc.php');
require_once(KT_LIB_DIR . '/help/helpreplacement.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/util/ktutil.inc');

require_once(realpath(dirname(__FILE__) . '/loginResetDispatcher.php'));

class loginResetEmailDispatcher extends loginResetDispatcher {

    protected function getAdditionalJS($plugin, $useEmail = false)
    {
        return $plugin->getURLPath('resources/passwordResetEmailUsers.js');
    }

    protected function getEmail() {
        $resetKey = (isset($_REQUEST['pword_reset'])) ? $_REQUEST['pword_reset'] : '';
    	if (!empty($resetKey)) {
            // Get the user id from the key
            $key = explode('_', $resetKey);
            $id = isset($key[1]) ? $key[1] : '';
    		$user = User::get($id);
    		if (!PEAR::isError($user)) {
    			$email = $user->getEmail();
    		}
    	}

        return $email;
    }

    protected function getFailedLoginMessage() {
        return 'Login failed.  Please check your email address and password, and try again.';
    }

    protected function doFailedLoginRedirect($url, $queryParams) {
        $message = 'Login failed.  Please check your email address and password, and try again.';
        $this->simpleRedirectToMain(_kt($message), $url, $queryParams);
        exit(0);
    }

    function do_sendResetRequest() {
        $email = $_REQUEST['email'];
	$id = $this->validateEmailUser($email);
        if (!is_numeric($id) || $id < 1) {
       	    return _kt('Please check that you have entered a valid email address.');
        }

        // Generate a random key that expires after 24 hours
        $expiryDate = time() + 86400;
        $randomKey = rand(20000, 100000) . "_{$id}_" . KTUtil::getSystemIdentifier();
        KTUtil::setSystemSetting('password_reset_expire-' . $id, $expiryDate);
        KTUtil::setSystemSetting('password_reset_key-' . $id, $randomKey);

        // Create the link to reset the password
        $query = 'pword_reset=' . $randomKey;
        $url = KTUtil::addQueryStringSelf($query);

        $subject = APP_NAME . ': ' . _kt('password reset request');

        $body = '<dd><p>';
        $body .= _kt('You have requested to reset the password for your account. To confirm that the request was submitted by you
        click on the link below, you will then be able to reset your password.');
        $body .= "</p><p><a href = '$url'>" . _kt('Confirm password reset') . '</a></p></dd>';

        $oEmail = new Email();
        $res = $oEmail->send($email, $subject, $body);

        if ($res === true) {
            return _kt('A verification email has been sent to your email address.');
        }

        return _kt('An error occurred while sending the email. Please try again.');
    }

    function do_resetPassword() {
        $email = $_REQUEST['email'];
        $user = $_REQUEST['username'];
        $password = $_REQUEST['password'];
        $confirm = $_REQUEST['confirm'];
	$KTConfig = KTConfig::getSingleton();

	return $this->resetPasswordEmailUser($email, $password);
    }

}

$dispatcher = new loginResetEmailDispatcher();
$dispatcher->dispatch();

?>
