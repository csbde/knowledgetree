<?php

//require_once('../../../../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/groups/GroupUtil.php');
require_once(KT_LIB_DIR . '/groups/Group.inc');

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

class KTUserAdminDispatcher extends KTAdminDispatcher {
    // Breadcrumbs base - added to in methods
    var $aBreadcrumbs = array(
    array('action' => 'administration', 'name' => 'Administration'),
    );

    function do_main() {
        $this->aBreadcrumbs[] = array('action' => 'userManagement', 'name' => 'User Management');
        $this->oPage->setBreadcrumbDetails('select a user');
        $this->oPage->setTitle("User Management");
        
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        $show_all = KTUtil::arrayGet($_REQUEST, 'show_all', false);
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
    
        
        $search_fields = array();
        $search_fields[] =  new KTStringWidget('Username','Enter part of the person\'s username.  e.g. <strong>ra</strong> will match <strong>brad</strong>.', 'name', $name, $this->oPage, true);
        
        // FIXME handle group search stuff.
        $search_results = null;
        if (!empty($name)) {
            $search_results =& User::getList('WHERE username LIKE "%' . DBUtil::escapeSimple($name) . '%"');
        } else if ($show_all !== false) {
            $search_results =& User::getList();
        }
        
        $oTemplating = new KTTemplating;        
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/useradmin");
        $aTemplateData = array(
            "context" => $this,
            "search_fields" => $search_fields,
            "search_results" => $search_results,
        );
        return $oTemplate->render($aTemplateData);
    }


    function do_addUser() {
        $this->aBreadcrumbs[] = array('action' => 'userManagement', 'name' => 'User Management');
        $this->oPage->setBreadcrumbDetails('add a new user');
        $this->oPage->setTitle("Modify User Details");
        
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        $show_all = KTUtil::arrayGet($_REQUEST, 'show_all', false);
        $add_user = KTUtil::arrayGet($_REQUEST, 'add_user', false);
        if ($add_user !== false) { $add_user = true; }
        $edit_user = KTUtil::arrayGet($_REQUEST, 'edit_user', false);
    
        
        $add_fields = array();
        $add_fields[] =  new KTStringWidget('Username','The username the user will enter to gain access to the KnowledgeTree.  e.g. <strong>jsmith</strong>', 'username', null, $this->oPage, true);
        $add_fields[] =  new KTStringWidget('Name','The full name of the user.  This is shown in reports and listings.  e.g. <strong>John Smith</strong>', 'name', null, $this->oPage, true);        
        $add_fields[] =  new KTStringWidget('Email Address','The email address of the user.  Notifications and alerts are mailed to this address if <strong>email notifications</strong> is set below. e.g. <strong>jsmith@acme.com</strong>', 'email_address', null, $this->oPage, false);        
        $add_fields[] =  new KTCheckboxWidget('Email Notifications','If this is specified then the user will have notifications sent to the email address entered above.  If it isn\'t set, then the user will only see notifications on the <strong>Dashboard</strong>', 'email_notifications', true, $this->oPage, false);        
        $add_fields[] =  new KTPasswordWidget('Password','Specify an initial password for the user.', 'password', null, $this->oPage, true);        
        $add_fields[] =  new KTPasswordWidget('Confirm Password','Confirm the password specified above.', 'confirm_password', null, $this->oPage, true);        
        // nice, easy bits.
        $add_fields[] =  new KTStringWidget('Mobile Number','The mobile phone number of the user.  If the system is configured to send notifications to cellphones, then this number will be SMS\'d with notifications.  e.g. <strong>999 9999 999</strong>', 'mobile_number', null, $this->oPage, false);        
        $add_fields[] =  new KTStringWidget('Maximum Sessions','As a safety precaution, it is useful to limit the number of times a given account can log in, before logging out.  This prevents a single account being used by many different people.', 'max_sessions', '3', $this->oPage, true);        
        // FIXME handle group search stuff.
        $search_results = null;
        if (!empty($name)) {
            $search_results =& User::getList('WHERE username LIKE "%' . DBUtil::escapeSimple($name) . '%"');
        } else if ($show_all !== false) {
            $search_results =& User::getList();
        }
        
        $oTemplating = new KTTemplating;        
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/adduser");
        $aTemplateData = array(
            "context" => $this,
            "add_fields" => $add_fields,
        );
        return $oTemplate->render($aTemplateData);
    }    
    


    function do_editUser() {
    $this->aBreadcrumbs[] = array('action' => 'userManagement', 'name' => 'User Management');
    $this->oPage->setBreadcrumbDetails('modify user details');
    $this->oPage->setTitle("Modify User Details");
    
    $name = KTUtil::arrayGet($_REQUEST, 'name');
    $show_all = KTUtil::arrayGet($_REQUEST, 'show_all', false);
    $add_user = KTUtil::arrayGet($_REQUEST, 'add_user', false);
    if ($add_user !== false) { $add_user = true; }
    $edit_user = KTUtil::arrayGet($_REQUEST, 'edit_user', false);
    $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
    
    $oUser =& User::get($user_id);
    
    if (PEAR::isError($oUser) || $oUser == false) {
        $this->errorRedirectToMain('Please select a user first.');
        exit(0);
    }
    
    $this->aBreadcrumbs[] = array('name' => $oUser->getName());
    
    $edit_fields = array();
    $edit_fields[] =  new KTStringWidget('Username','The username the user will enter to gain access to the KnowledgeTree.  e.g. <strong>jsmith</strong>', 'username', $oUser->getUsername(), $this->oPage, true);
    $edit_fields[] =  new KTStringWidget('Name','The full name of the user.  This is shown in reports and listings.  e.g. <strong>John Smith</strong>', 'name', $oUser->getName(), $this->oPage, true);        
    $edit_fields[] =  new KTStringWidget('Email Address','The email address of the user.  Notifications and alerts are mailed to this address if <strong>email notifications</strong> is set below. e.g. <strong>jsmith@acme.com</strong>', 'email_address', $oUser->getEmail(), $this->oPage, false);        
    $edit_fields[] =  new KTCheckboxWidget('Email Notifications','If this is specified then the user will have notifications sent to the email address entered above.  If it isn\'t set, then the user will only see notifications on the <strong>Dashboard</strong>', 'email_notifications', $oUser->getEmailNotification(), $this->oPage, false);        
    $edit_fields[] =  new KTStringWidget('Mobile Number','The mobile phone number of the user.  If the system is configured to send notifications to cellphones, then this number will be SMS\'d with notifications.  e.g. <strong>999 9999 999</strong>', 'mobile_number', $oUser->getMobile(), $this->oPage, false);        
    $edit_fields[] =  new KTStringWidget('Maximum Sessions','As a safety precaution, it is useful to limit the number of times a given account can log in, before logging out.  This prevents a single account being used by many different people.', 'max_sessions', $oUser->getMaxSessions(), $this->oPage, true);        
    
    // FIXME handle group search stuff.
    $search_results = null;
    if (!empty($name)) {
        $search_results =& User::getList('WHERE username LIKE "%' . DBUtil::escapeSimple($name) . '%"');
    } else if ($show_all !== false) {
        $search_results =& User::getList();
    }
    
    $oTemplating = new KTTemplating;        
    $oTemplate = $oTemplating->loadTemplate("ktcore/principals/edituser");
    $aTemplateData = array(
        "context" => $this,
        "edit_fields" => $edit_fields,
        "edit_user" => $oUser,
    );
    return $oTemplate->render($aTemplateData);
    }        
    
    function do_editgroups() {
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser = User::get($user_id);
        if ((PEAR::isError($oUser)) || ($oUser === false)) {
            $this->errorRedirectToMain('No such user.');
        }
        
        $this->aBreadcrumbs[] = array('name' => $oUser->getName());
        $this->oPage->setBreadcrumbDetails('edit groups');
        $this->oPage->setTitle('Edit ' . $oUser->getName() . '\'s groups');
        // generate a list of groups this user is authorised to assign.
        
        /* FIXME there is a nasty side-effect:  if a user cannot assign a group
        * to a user, and that user _had_ that group pre-edit, 
        * then their privilidges are revoked.
        * is there _any_ way to fix that?
        */
        
        // FIXME move this to a transfer widget
        // FIXME replace OptionTransfer.js.  me no-likey.
        
        // FIXME this is hideous.  refactor the transfer list stuff completely.
        $initJS = 'var optGroup = new OptionTransfer("groupSelect[]","chosenGroups[]"); ' .
        'function startTrans() { var f = getElement("usergroupform"); ' .
        ' optGroup.saveAddedRightOptions("groupAdded"); ' .
        ' optGroup.saveRemovedRightOptions("groupRemoved"); ' .
        ' optGroup.init(f); }; ' .
        ' addLoadEvent(startTrans); '; 
        $this->oPage->requireJSStandalone($initJS);
        
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
        
        $oTemplating = new KTTemplating;        
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/usergroups");
        $aTemplateData = array(
            "context" => $this,
            "unused_groups" => $aFreeGroups,
            "user_groups" => $aUserGroups,
            "edit_user" => $oUser,
        );
        return $oTemplate->render($aTemplateData);        
    }    
    
    function do_saveUser() {
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        $username = KTUtil::arrayGet($_REQUEST, 'username');
        $email_address = KTUtil::arrayGet($_REQUEST, 'email_address');
        $email_notifications = KTUtil::arrayGet($_REQUEST, 'email_notifications', false);
        if ($email_notifications !== false) $email_notifications = true;
        $mobile_number = KTUtil::arrayGet($_REQUEST, 'mobile_number');
        $max_sessions = KTUtil::arrayGet($_REQUEST, 'max_sessions', '3');
        // FIXME more validation would be useful.
        // validated and ready..
        $this->startTransaction();
        
        $oUser =& User::get($user_id);
        if (PEAR::isError($oUser) || $oUser == false) {
            $this->errorRedirectToMain("Please select a user to modify first.");
        }
        
        $oUser->setName($name);
        $oUser->setUsername($username);  // ?
        $oUser->setEmail($email_address);
        $oUser->setEmailNotification($email_notifications);
        $oUser->setMobile($mobile_number);
        $oUser->setMaxSessions($max_sessions);
        
        $res = $oUser->update(); // FIXME res?
        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectoToMain('Failed to update user.');
        }
        
        $this->commitTransaction();
        $this->successRedirectToMain('User information updated.');
        
    }
    
    function do_createUser() {
        // FIXME generate and pass the error stack to adduser.
        
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        if (empty($name)) { $this->errorRedirectToMain('You must specify a name for the user.'); }
        $username = KTUtil::arrayGet($_REQUEST, 'username');
        if (empty($name)) { $this->errorRedirectToMain('You must specify a new username..'); }
        // FIXME check for non-clashing usernames.
        
        $email_address = KTUtil::arrayGet($_REQUEST, 'email_address');
        $email_notifications = KTUtil::arrayGet($_REQUEST, 'email_notifications', false);
        if ($email_notifications !== false) $email_notifications = true;
        $mobile_number = KTUtil::arrayGet($_REQUEST, 'mobile_number');
        $max_sessions = KTUtil::arrayGet($_REQUEST, 'max_sessions', '3');
        // FIXME check for numeric max_sessions... db-error else?
        $password = KTUtil::arrayGet($_REQUEST, 'password');
        $confirm_password = KTUtil::arrayGet($_REQUEST, 'confirm_password');        
        
        if (empty($password)) { 
            $this->errorRedirectToMain("You must specify a password for the user.");
        } else if ($password !== $confirm_password) {
            $this->errorRedirectToMain("The passwords you specified do not match.");
        }
        
        $oUser =& User::createFromArray(array(
            "sUsername" => $username,
            "sName" => $name,
            "sPassword" => $password,
            "iQuotaMax" => 0,
            "iQuotaCurrent" => 0,
            "sEmail" => $email_address,
            "bEmailNotification" => $email_notifications,
            "bSmsNotification" => false,   // FIXME do we auto-act if the user has a mobile?
            //"ldap_dn" => '', // FIXME re-enable LDAP.
            "iMaxSessions" => $max_sessions,
            //"language_id" => -1, // FIXME language id?
        ));
        
        if (PEAR::isError($oUser) || ($oUser == false)) {
            $this->errorRedirectToMain("failed to create user.");
            exit(0);
        }
        
        $oUser->create();
        
        $this->successRedirectToMain('Create new user "' . $oUser->getUsername() . '"');
    }
    
    function do_deleteUser() {
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser = User::get($user_id);
        if ((PEAR::isError($oUser)) || ($oUser === false)) {
            $this->errorRedirectToMain('Please select a user first.');
        }
        $oUser->delete();
        
        $this->successRedirectToMain($oUser->getName() . ' deleted.');
    }
    
    function do_updateGroups() {
        $user_id = KTUtil::arrayGet($_REQUEST, 'user_id');
        $oUser = User::get($user_id);
        if ((PEAR::isError($oUser)) || ($oUser === false)) {
            $this->errorRedirectToMain('Please select a user first.');
        }	
        $groupAdded = KTUtil::arrayGet($_REQUEST, 'groupAdded','');
        $groupRemoved = KTUtil::arrayGet($_REQUEST, 'groupRemoved','');
        
        
        $aGroupToAddIDs = explode(",", $groupAdded);
        $aGroupToRemoveIDs = explode(",", $groupRemoved);
        
        // FIXME we need to ensure that only groups which are allocatable by the admin are added here.
        
        // FIXME what groups are _allocatable_?
        
        $this->startTransaction();
        $groupsAdded = array();
        $groupsRemoved = array();
        
        foreach ($aGroupToAddIDs as $iGroupID ) {
            if ($iGroupID > 0) {
                $oGroup = Group::get($iGroupID);
                $res = $oGroup->addMember($oUser);
                if (PEAR::isError($res) || $res == false) {
                    $this->errorRedirectToMain('Unable to add user to "' . $oGroup->getName() . '"');
                } else { $groupsAdded[] = $oGroup->getName(); }
            }
        }
    
        // Remove groups
        foreach ($aGroupToRemoveIDs as $iGroupID ) {
            if ($iGroupID > 0) {
                $oGroup = Group::get($iGroupID);
                $res = $oGroup->removeMember($oUser);
                if (PEAR::isError($res) || $res == false) {
                    $this->errorRedirectToMain('Unable to remove user from "' . $oGroup->getName() . '"');			
                } else { $groupsRemoved[] = $oGroup->getName(); }
            }
        }        
        
        $msg = '';
        if (!empty($groupsAdded)) { $msg .= ' Added to ' . join(', ', $groupsAdded) . ', <br />'; }
        if (!empty($groupsRemoved)) { $msg .= ' Removed from ' . join(', ',$groupsRemoved) . '.'; }
        
        $this->commitTransaction();
        $this->successRedirectToMain($msg);
    }

}

//$oDispatcher = new KTUserAdminDispatcher ();
//$oDispatcher->dispatch();

?>
