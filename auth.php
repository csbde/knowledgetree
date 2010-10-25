<?php
/**
 * $Id$
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

// main library routines and defaults
require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/session/Session.inc');

class AuthenticationDispatcher extends KTDispatcher {

    function do_main()
    {
        global $default;

        // TODO move this code to within the plugin?  may wish to share it between methods if we add more auth methods?
        // dispatch based on received authentication content
        // OneLogin SAML authentication
        if (!empty($_POST['SAMLResponse']) && KTPluginUtil::pluginIsActive('auth.onelogin.plugin')) {
            try {
				require_once(KTPluginUtil::getPluginPath('auth.onelogin.plugin') . 'SAMLConsumer.inc.php');
                $user = null;
				$consumer = new SAMLConsumer();
				if ($consumer->authenticate($_POST['SAMLResponse'], $user)) {
				    // determine user from supplied username
				    $res = DBUtil::getOneResult("SELECT id FROM users WHERE username = '$user'");
				    if (PEAR::isError($res) || empty($res['id'])) {
				        $default->log->error("Error finding user $user (OneLogin SAML authentication)" 
				                             . (PEAR::isError($res) ? ': ' . $res->getMessage() : ''));
				        // redirect to login screen with appropriate error
				        header('Location: login.php?errorMessage=Login+failed.++Please+check+your+onelogin+username+and+try+again.');
				    }
				    
				    // set user as logged in
				    $user = User::get($res['id']);
				    if (PEAR::isError($user)) {
				        $default->log->error("User $user does not exist (OneLogin SAML authentication): " . $user->getMessage());
				        // redirect to login screen with appropriate error
				        header('Location: login.php?errorMessage=Login+failed.++Please+check+your+onelogin+username+and+try+again.');
				    }
				    $session = new Session();
				    $sessionID = $session->create($user);
				    if (PEAR::isError($sessionID)) {
				        $default->log->error("Error creating session for user $user (OneLogin SAML authentication): " . $sessionID->getMessage());
				        // redirect to login screen with appropriate error
				        header('Location: login.php?errorMessage=Login+failed.++Please+check+your+onelogin+username+and+try+again.');
				    }
				    
				    // log authentication method used
				    $default->log->info('User logged in (OneLogin SAML authentication)');
				    
                    // add a flag to check for bulk downloads after login is succesful; this will be cleared in the code which checks
				    $_SESSION['checkBulkDownload'] = true;


				    // DEPRECATED initialise page-level authorisation array
				    $_SESSION['pageAccess'] = null;

				    $redirect = strip_tags(KTUtil::arrayGet($_REQUEST, 'redirect'));
				    $cookietest = KTUtil::randomString();
				    setcookie('CookieTestCookie', $cookietest, 0);

				    $this->redirectTo('checkCookie', array(
				    'cookieVerify' => $cookietest,
				    'redirect' => $redirect,
				    ));
				    
				    exit(0);
				}
				else {
				    // redirect to login screen with appropriate error
                    header('Location: login.php?errorMessage=Login+failed.++Please+check+your+onelogin+username+and+try+again.');
				}
			}
			catch (Exception $e) {
			    // redirect to login screen with appropriate error
			    header('Location: login.php?errorMessage=Login+failed.++Please+check+your+onelogin+username+and+try+again.');
			}
        }
        
        // redirect to main login page
        header('Location: login.php?errorMessage=Login+failed.++Please+check+your+username+and+password%2C+and+try+again.');
    }

}

$dispatcher = new AuthenticationDispatcher();
$dispatcher->dispatch();

?>
