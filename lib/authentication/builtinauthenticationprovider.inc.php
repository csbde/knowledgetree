<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

require_once(KT_LIB_DIR . '/authentication/authenticationprovider.inc.php');
require_once(KT_LIB_DIR . '/authentication/Authenticator.inc');
require_once('DBAuthenticator.inc');

class KTBuiltinAuthenticationProvider extends KTAuthenticationProvider {
    var $sName = "Built-in authentication provider";
    var $sNamespace = "ktcore.authentication.builtin";

    function &getAuthenticator($oSource) {
        // $oSource is null, since the built-in authentication provider
        // only has a single, non-registered, instance.
        return new BuiltinAuthenticator;
    }
    
    function showUserSource($oUser, $oSource) {
        $sQuery = sprintf('action=editUserSource&user_id=%d', $oUser->getId());
        $sUrl = KTUtil::addQueryString($_SERVER['PHP_SELF'], $sQuery);
        return '<p class="descriptiveText"><a href="' . $sUrl . '">' . sprintf(_kt("Change %s's password"), $oUser->getName()) . '</a></p>';
    }

    function do_editUserSource() {
        $this->redispatch('subaction', 'editUserSource');
        exit(0);
    }

    function editUserSource_main() {
        $this->oPage->setBreadcrumbDetails(_kt('change user password'));
        $this->oPage->setTitle(_kt("Change User Password"));

        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser =& User::get($user_id);

        if (PEAR::isError($oUser) || $oUser == false) {
            $this->errorRedirectToMain(_kt('Please select a user first.'));
            exit(0);
        }

        $edit_fields = array();
        $edit_fields[] =  new KTPasswordWidget(_kt('Password'), _kt('Specify an initial password for the user.'), 'password', null, $this->oPage, true);         $edit_fields[] =  new KTPasswordWidget(_kt('Confirm Password'), _kt('Confirm the password specified above.'), 'confirm_password', null, $this->oPage, true); 
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/updatepassword");
        $aTemplateData = array(
            "context" => $this,
            "edit_fields" => $edit_fields,
            "edit_user" => $oUser,
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
            $this->errorRedirectToMain(sprintf(_kt("The password must be at least %d characters long."), $minLength));
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
            $url = generateControllerUrl("login", "action=providerVerify&type=1");
            $this->addErrorMessage("Your password has expired");
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
            $url = generateControllerUrl("login");
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
            $this->errorRedirectTo('providerVerify', sprintf(_kt("The password must be at least %d characters long."), $minLength));
        }

        $sNewMD5 = md5($sPassword);
        $sOldMD5 = $this->oUser->getPassword();
        if ($sNewMD5 == $sOldMD5) {
            $this->errorRedirectTo('providerVerify', _kt("Can not use the same password as before."));
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

        $sql = $default->db;
        $userName = $oUser->getUserName();
        $sQuery = "SELECT * FROM $default->users_table WHERE username = ? AND password = ?";/*ok*/
        $aParams = array($userName, md5($password));
        if ($sql->query(array($sQuery, $aParams))) {
            if ($sql->num_rows($sql) == "1") {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
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
        global $default;

        $sql = $default->db;
        $sQuery = "SELECT ";/*ok*/
        // build select
        for ($i=0; $i<count($aAttributes); $i++) {
            $sQuery .= $aAttributes[$i] . (( ($i+1) == count($aAttributes) ) ? "" : ", ");
        }
        $sQuery .= " FROM $default->users_table WHERE username = ?";
        $aParams = array($sUserName);

        if ($sql->query(array($sQuery, $aParams))) {
            $aUserResults = array();
            while ($sql->next_record()) {
                for ($i=0; $i<count($aAttributes); $i++) {
                    $aUserResults["$sUserName"]["$aAttributes[$i]"] = $sql->f($aAttributes[$i]);
                }
            }
            return $aUserResults;
        } else {
            return false;
        }
    }

    /**
     * Searches the user store for users matching the supplied search string.
     *
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */
    function searchUsers($sUserNameSearch, $aAttributes) {
        global $default;

        $sql = $default->db;
        $sQuery = "SELECT ";/*ok*/
        // build select
        for ($i=0; $i<count($aAttributes); $i++) {
            $sQuery .= $aAttributes[$i] . (( ($i+1) == count($aAttributes) ) ? "" : ", ");
        }
        $sQuery .= " FROM $default->users_table where username like '%" . DBUtil::escapeSimple($sUserNameSearch) . "%'";

        if ($sql->query($sQuery)) {
            $aUserResults = array();
            while ($sql->next_record()) {
                $sUserName = $sql->f("username");
                for ($i=0; $i<count($aAttributes); $i++) {
                    $aUserResults["$sUserName"]["$aAttributes[$i]"] = $sql->f($aAttributes[$i]);
                }
            }
            return $aUserResults;
        } else {
            return false;
        }
    }
}

