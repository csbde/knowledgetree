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

require_once(KT_LIB_DIR . '/authentication/authenticationprovider.inc.php');
require_once(KT_LIB_DIR . '/authentication/Authenticator.inc');
require_once(KT_LIB_DIR . '/authentication/DBAuthenticator.inc');

class KTBuiltinAuthenticationProvider extends KTAuthenticationProvider {
    var $sNamespace = 'ktcore.authentication.builtin';

    function KTBuiltinAuthenticationProvider() {
        $this->sName = _kt('Built-in authentication provider');
        parent::KTAuthenticationProvider();
    }

    function &getAuthenticator($oSource) {
        // $oSource is null, since the built-in authentication provider
        // only has a single, non-registered, instance.
        $ret= new BuiltinAuthenticator();
        return $ret;
    }
    
    function showUserSource($oUser, $oSource) {
        $sQuery = sprintf('action=editUserSource&user_id=%d', $oUser->getId());
        $sUrl = KTUtil::addQueryString($_SERVER['PHP_SELF'], $sQuery);
        return '<p class="descriptiveText"><a href="' . $sUrl . '">' . sprintf(_kt('Change %s\'s password'), $oUser->getName()) . '</a></p>';
    }

    function do_editUserSource() {
        $this->redispatch('subaction', 'editUserSource');
        exit(0);
    }

    function editUserSource_main() {
        $this->oPage->setBreadcrumbDetails(_kt('Change User Password'));
        $this->oPage->setTitle(_kt('Change User Password'));

        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser =& User::get($user_id);

        if (PEAR::isError($oUser) || $oUser == false) {
            $this->errorRedirectToMain(_kt('Please select a user first.'));
            exit(0);
        }

        $edit_fields = array();
        $edit_fields[] =  new KTPasswordWidget(_kt('Password'), _kt('Specify an initial password for the user.'), 'password', null, $this->oPage, true);         $edit_fields[] =  new KTPasswordWidget(_kt('Confirm Password'), _kt('Confirm the password specified above.'), 'confirm_password', null, $this->oPage, true); 
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/principals/updatepassword');
        $aTemplateData = array(
            'context' => $this,
            'edit_fields' => $edit_fields,
            'edit_user' => $oUser,
        );
        return $oTemplate->render($aTemplateData);
    }
    
    function editUserSource_forcePasswordChange() {
        $aErrorOptions = array(
            'redirect_to' => array('main'),
        );
        $oUser =& $this->oValidator->validateUser($_REQUEST['user_id'], $aErrorOptions);

        $oUser->setAuthenticationDetailsBool1(true);
        $res = $oUser->update();

        $aErrorOptions = array(
            'redirect_to' => array('editUserSource', sprintf('user_id=%d', $oUser->getId())),
            'message' => _kt('Failed to update user'),
        );
        $this->oValidator->notErrorFalse($res, $aErrorOptions);

        $this->commitTransaction();
        $this->successRedirectTo('editUser', _kt('User will need to change password on next login.'), sprintf('user_id=%d', $oUser->getId()));
    }

    function editUserSource_updatePassword() {
        $aErrorOptions = array(
            'redirect_to' => array('main'),
        );
        $oUser =& $this->oValidator->validateUser($_REQUEST['user_id'], $aErrorOptions);

        $aErrorOptions = array(
            'redirect_to' => array('editUserSource', sprintf('user_id=%d', $oUser->getId())),
        );
        $sPassword = $this->oValidator->validatePasswordMatch($_REQUEST['password'], $_REQUEST['confirm_password'], $aErrorOptions);

        $KTConfig =& KTConfig::getSingleton();
        $minLength = ((int) $KTConfig->get('user_prefs/passwordLength', 6));
        $restrictAdmin = ((bool) $KTConfig->get('user_prefs/restrictAdminPasswords', false));

        if ($restrictAdmin && (strlen($sPassword) < $minLength)) {
            $this->errorRedirectToMain(sprintf(_kt('The password must be at least %d characters long.'), $minLength));
        }

        $this->startTransaction();

        // FIXME this almost certainly has side-effects.  do we _really_ want
        $oUser->setPassword(md5($sPassword)); //

        $res = $oUser->update();
        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectTo('editUser', _kt('Failed to update user.'),  sprintf('user_id=%d', $oUser->getId()));
        }

        $this->commitTransaction();
        $this->successRedirectTo('editUser', _kt('User information updated.'), sprintf('user_id=%d', $oUser->getId()));

    }

    function login($oUser) {
        $oConfig =& KTConfig::getSingleton();

        $iDays = $oConfig->get('builtinauth/password_change_interval');
        if ($iDays) {
            $dLastPasswordChange = $oUser->getAuthenticationDetailsDate1();
            if (empty($dLastPasswordChange)) {
                $oUser->setAuthenticationDetailsDate1(formatDateTime(time()));
                $oUser->update();
            }
            $sTable = KTUtil::getTableName('users');
            $dNoLaterThan = formatDateTime(time() - ($iDays * 24 * 60 * 60));
            $aSql = array("SELECT id FROM $sTable WHERE id = ? and authentication_details_d1 < ?",
                array($oUser->getId(), $dNoLaterThan),
            );

            $iRes = DBUtil::getOneResultKey($aSql, 'id');
        
            if (!empty($iRes)) {
                $_SESSION['mustChangePassword'] = true;
            }
        }

        if ($oUser->getAuthenticationDetailsBool1()) {
            $_SESSION['mustChangePassword'] = true;
        }
    }

    function verify($oUser) {
        if (isset($_SESSION['mustChangePassword'])) {
            $url = generateControllerUrl('login', 'action=providerVerify&type=1');
            $this->addErrorMessage(_kt('Your password has expired'));
            redirect($url);
            exit(0);
        }
    }

    function do_providerVerify() {
        $this->redispatch('subaction', 'providerVerify');
        exit(0);
    }

    function providerVerify_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/authentication/force_change_password');
        $edit_fields = array();
        $edit_fields[] = new KTPasswordWidget(_kt('Password'), _kt('Enter a new password for the account.'), 'password', null, $this->oPage, true);
        $edit_fields[] = new KTPasswordWidget(_kt('Confirm Password'), _kt('Confirm the password specified above.'), 'confirm_password', null, $this->oPage, true);

        $aTemplateData = array(
            'user' => $this->oUser,
            'edit_fields' => $edit_fields,
        );
        return $oTemplate->render($aTemplateData);
    }

    function providerVerify_return() {
        $url = KTUtil::arrayGet($_SESSION, 'providerVerifyReturnUrl');
        if (empty($url)) {
            $url = generateControllerUrl('login');
        }
        redirect($url);
        exit(0);
    }

    function providerVerify_updatePassword() {
        $aErrorOptions = array(
            'redirect_to' => array('providerVerify'),
        );
        $sPassword = $this->oValidator->validatePasswordMatch($_REQUEST['password'], $_REQUEST['confirm_password'], $aErrorOptions);

        $KTConfig =& KTConfig::getSingleton();
        $minLength = (int) $KTConfig->get('user_prefs/passwordLength', 6);

        if (strlen($sPassword) < $minLength) {
            $this->errorRedirectTo('providerVerify', sprintf(_kt('The password must be at least %d characters long.'), $minLength));
        }

        $sNewMD5 = md5($sPassword);
        $sOldMD5 = $this->oUser->getPassword();
        if ($sNewMD5 == $sOldMD5) {
            $this->errorRedirectTo('providerVerify', _kt('Can not use the same password as before.'));
        }

        // FIXME more validation would be useful.
        // validated and ready..
        $this->startTransaction();
        $this->oUser->setPassword($sNewMD5);
        $this->oUser->setAuthenticationDetailsDate1(formatDateTime(time()));
        $this->oUser->setAuthenticationDetailsBool1(false);

        $res = $this->oUser->update();
        $aErrorOptions = array(
            'redirect_to' => array('providerVerify'),
        );
        $this->oValidator->notErrorFalse($res, $aErrorOptions);

        $this->commitTransaction();
        unset($_SESSION['mustChangePassword']);
        $this->successRedirectTo('providerVerify', _kt('Password changed'), 'subaction=return');
    }
}

class BuiltinAuthenticator extends Authenticator {
    /**
     * Checks the user's password against the database
     *
     * @param string the name of the user to check
     * @param string the password to check
     * @return boolean true if the password is correct, else false
     */
    function checkPassword($oUser, $password) {
        global $default;

        $userName = $oUser->getUserName();
        $sTable = KTUtil::getTableName('users');
        $sQuery = "SELECT count(*) AS match_count FROM $sTable WHERE username = ? AND password = ?";
        $aParams = array($userName, md5($password));
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'match_count');
        if (PEAR::isError($res)) { return false; }
        else {
            return ($res == 1);
        }
        
    }

    /**
     * Searches the directory for a specific user
     *
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */
    function getUser($sUserName, $aAttributes) {
        $sTable = KTUtil::getTableName('users'); 
        $sQuery = 'SELECT ';/*ok*/
        $sQuery .= implode(', ', $aAttributes);
        $sQuery .= " FROM $sTable WHERE username = ?";
        $aParams = array($sUserName);
        $res = DBUtil::getResultArray(array($sQuery, $aParams));
        if (PEAR::isError($res)) { 
            return false; 
        }
        
        $aUserResults = array();        
        foreach ($res as $aRow) {
            foreach ($aAttributes as $sAttrName) {
                $aUserResults[$sUserName][$sAttrName] = $aRow[$sAttrName];
            }
        } 
        return $aUserResults;
        
    }

    /**
     * Searches the user store for users matching the supplied search string.
     *
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */
    function searchUsers($sUserNameSearch, $aAttributes) {
        $sTable = KTUtil::getTableName('users');
        $sQuery = 'SELECT '; /*ok*/
        $sQuery .= implode(', ', $aAttributes); 
        $sQuery .= " FROM $sTable where username like '%" . DBUtil::escapeSimple($sUserNameSearch) . "%'";

        $res = DBUtil::getResultArray(array($sQuery, array()));
        if (PEAR::isError($res)) {
            return false; // return $res;
        }
        
        $aUserResults = array();
        foreach ($res as $aRow) {    
            $sUserName = $aRow['username'];
            foreach ($aAttributes as $sAttrName) {
                $aUserResults[$sUserName][$sAttrName] = $aRow[$sAttrName];
            }
        }
        return $aUserResults;
    }
}

