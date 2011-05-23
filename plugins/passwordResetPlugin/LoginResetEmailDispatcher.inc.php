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

require_once(realpath(dirname(__FILE__) . '/LoginResetDispatcher.inc.php'));

class LoginResetEmailDispatcher extends LoginResetDispatcher {

    protected function getAdditionalJS($plugin, $useEmail = false)
    {
        return $plugin->getURLPath('resources/passwordResetEmailUsers.js');
    }

    protected function getEmail()
    {
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

    protected function getFailedLoginMessage()
    {
        return _kt('Login failed.  Please check your email address and password, and try again.');
    }

    protected function doFailedLoginRedirect($url, $queryParams)
    {
        $message = _kt('Login failed.  Please check your email address and password, and try again.');
        $this->simpleRedirectToMain(_kt($message), $url, $queryParams);
        exit(0);
    }

    public function do_sendResetRequest()
    {
        $email = $_REQUEST['email'];
        $id = $this->validateEmailUser($email);
        if (!is_numeric($id) || $id < 1) {
               return _kt('Please check that you have entered a valid email address.');
        }

        return $this->sendResetEmail($id, $email);
    }

    public function do_resetPassword()
    {
        $email = $_REQUEST['email'];
        $user = $_REQUEST['username'];
        $password = $_REQUEST['password'];
        $confirm = $_REQUEST['confirm'];

        return $this->resetPasswordEmailUser($email, $password);
    }

}

?>
