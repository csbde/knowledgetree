<?php

// main library routines and defaults
require_once("../../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/unitmanagement/Unit.inc");

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');



class PreferencesDispatcher extends KTStandardDispatcher {
    var $sSection = 'preferences';

    function PreferencesDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'preferences', 'name' => _('Preferences')),
        );
        return parent::KTStandardDispatcher();
    }

    function do_main() {
		$this->oPage->setBreadcrumbDetails(_("Your Preferences"));
		$this->oPage->title = _("Dashboard");
		
		
		$oUser =& $this->oUser;
		
		
		$edit_fields = array();
        $edit_fields[] =  new KTStringWidget(_('Name'),_('Your full name.  This is shown in reports and listings.  e.g. <strong>John Smith</strong>'), 'name', $oUser->getName(), $this->oPage, true);        
        $edit_fields[] =  new KTStringWidget(_('Email Address'),_('Your email address.  Notifications and alerts are mailed to this address if <strong>email notifications</strong> is set below. e.g. <strong>jsmith@acme.com</strong>'), 'email_address', $oUser->getEmail(), $this->oPage, false);        
        $edit_fields[] =  new KTCheckboxWidget(_('Email Notifications'),_('If this is specified then the you will receive certain notifications.  If it is not set, then you will only see notifications on the <strong>Dashboard</strong>'), 'email_notifications', $oUser->getEmailNotification(), $this->oPage, false);        
        $edit_fields[] =  new KTStringWidget(_('Mobile Number'), _('Your mobile phone number.  If the system is configured to send notifications to cellphones, then this number will be sent an SMS with notifications.  e.g. <strong>+27 99 999 9999</strong>'), 'mobile_number', $oUser->getMobile(), $this->oPage, false);        
		
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("ktcore/principals/preferences");
		$aTemplateData = array(
              "context" => $this,
			  'edit_fields' => $edit_fields,
		);
		return $oTemplate->render($aTemplateData);    
    }

    function do_setPassword() {
		$this->oPage->setBreadcrumbDetails(_("Your Password"));
		$this->oPage->title = _("Dashboard");
		
		
		$oUser =& $this->oUser;
		
		$edit_fields = array();
        $edit_fields[] =  new KTPasswordWidget(_('Password'), _('Specify your new password.'), 'password', null, $this->oPage, true);        
        $edit_fields[] =  new KTPasswordWidget(_('Confirm Password'), _('Confirm the password specified above.'), 'confirm_password', null, $this->oPage, true);        
		
	
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("ktcore/principals/password");
		$aTemplateData = array(
              "context" => $this,
			  'edit_fields' => $edit_fields,
		);
		return $oTemplate->render($aTemplateData);    
    }



    function do_updatePassword() {
        
        $password = KTUtil::arrayGet($_REQUEST, 'password');
        $confirm_password = KTUtil::arrayGet($_REQUEST, 'confirm_password');        
        
        if (empty($password)) { 
            $this->errorRedirectToMain(_("You must specify a password for the user."));
        } else if ($password !== $confirm_password) {
            $this->errorRedirectToMain(_("The passwords you specified do not match."));
        }
        // FIXME more validation would be useful.
        // validated and ready..
        $this->startTransaction();
        
        $oUser =& $this->oUser;
        
        
        // FIXME this almost certainly has side-effects.  do we _really_ want 
        $oUser->setPassword(md5($password)); // 
        
        $res = $oUser->update(); 
        //$res = $oUser->doLimitedUpdate(); // ignores a fix blacklist of items.
        
        
        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectoToMain(_('Failed to update user.'));
        }
        
        $this->commitTransaction();
        $this->successRedirectToMain(_('Your password has been changed.'));
        
    }


    function do_updatePreferences() {
        $oUser =& $this->oUser;
        
        $name = KTUtil::arrayGet($_REQUEST, 'name');
		if (empty($name)) {
		     $this->errorRedirectToMain(_('You must specify your name.'));
		}
        
        $email_address = KTUtil::arrayGet($_REQUEST, 'email_address');
        $email_notifications = KTUtil::arrayGet($_REQUEST, 'email_notifications', false);
        if ($email_notifications !== false) $email_notifications = true;
        $mobile_number = KTUtil::arrayGet($_REQUEST, 'mobile_number');
        
        
        $this->startTransaction();
        
        $oUser->setName($name);
        $oUser->setEmail($email_address);
        $oUser->setEmailNotification($email_notifications);
        $oUser->setMobile($mobile_number);
        
        
        // old system used the very evil store.php.
        // here we need to _force_ a limited update of the object, via a db statement.
        //
        // $res = $oUser->update(); 
        $res = $oUser->doLimitedUpdate(); // ignores a fix blacklist of items.
        
        if (PEAR::isError($res) || ($res == false)) {
            $this->errorRedirectoToMain(_('Failed to update your details.'));
        }
        
        $this->commitTransaction();
        $this->successRedirectToMain(_('Your details have been updated.'));
        
    }


}

$oDispatcher = new PreferencesDispatcher();
$oDispatcher->dispatch();

?>
