<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

// main library routines and defaults
require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/util/ktutil.inc');

require_once(KT_LIB_DIR . '/security/Permission.inc');

require_once(KT_LIB_DIR . '/help/helpreplacement.inc.php');
require_once(KT_LIB_DIR . '/help/help.inc.php');

/*
 *  KT3 Help functionality.
 *
 * The KT3 help works slightly differently to the previous versions of KT.
 * This page takes subpath-info, and uses that to resolve the help documentation
 * as appropriate.
 *
 * This documentation should be placed into KT_DIR/kthelp/COMPONENT_NAME/LANG/...
 * Currently images and html files are the ONLY supported types.
 *
 * Within these directories, local addressing is _relative_ as expected.  
 *
 * File names _should not be changed_ from their reference values when translating,
 * since files are referred to WITHOUT the lang-code (e.g. EN, IT).
 */


class HelpDispatcher extends KTStandardDispatcher {

    var $sSection = 'dashboard';
    var $bIsReplacement = false;

    function HelpDispatcher() {
        $this->aBreadcrumbs[] = array('action' => 'dashboard', 'name' => _kt('Dashboard'));
        $this->aBreadcrumbs[] = array('name' => _kt('Help'));
        parent::KTStandardDispatcher();
    }

    function is_replacement() { return $this->bIsReplacement; }

    function do_main() {        
        // store referer
        $sBackKey = KTUtil::arrayGet($_REQUEST, 'back_key', false);
        $sSubPath = KTUtil::arrayGet($_SERVER, 'PATH_INFO');
        
        // we want to be able to say "i left the system at point x.  go back there"
        if(!$sBackKey) {
            $sReferer = KTUtil::arrayGet($_SERVER ,'HTTP_REFERER');
            $sBackKey = KTUtil::randomString();            
            $_SESSION[$sBackKey] = $sReferer;
        } 
        
        // no path specified
        if (empty($sSubPath)) {
            $this->oPage->setTitle(_kt('No help page specified.'));
            $this->oPage->addError(_kt('No help page specified.'));
            return '&nbsp;';
        }
        
        // simple test to see if this user is active.
        $bCanEdit = Permission::userIsSystemAdministrator($_SESSION['userID']);
        
        global $default;
        $sLangCode = $default->defaultLanguage; 
        /* 
          now we need to know a few things.  
             1. can we find this help file?
             2. if we can, display it
                2.1 images directly
                2.2 html wrapped.
             3. if now, fail out.
             
          this is essentially handled by asking help.inc.php for the 
          subpath we've been given, PLUS THE LANGUAGE, and checking for 
          a PEAR::raiseError.
          
          The "Correct" response we care about is a dictionary:
          
             {
                 'is_image': string
                 'title': string
                 'body': string
             }
        */

        $aHelpData = KTHelp::getHelpInfo($sSubPath);

        if (PEAR::isError($aHelpData)) {
            $this->oPage->setTitle($aHelpData->getMessage());
            $this->oPage->addError($aHelpData->getMessage());
            return '&nbsp';            
        }
        
        $aLocInfo = KTHelp::_getLocationInfo($sSubPath);
        
        if ($aHelpData['is_image']) {
            KTHelp::outputHelpImage($sSubPath);
            exit(0); // done.
        } else {
            $this->oPage->setTitle($aHelpData['title']);
            $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => $aHelpData['title']);
            $oTemplating =& KTTemplating::getSingleton();
            $oTemplate = $oTemplating->loadTemplate("ktcore/help_with_edit");
            $aTemplateData = array(
                  'context' => $this,
                  'help_body' => $aHelpData['body'],
				  'help_title' => $aHelpData['title'],
				  'target_name' => KTUtil::arrayGet($aLocInfo, 'subpath'),
                  'back_key' => $sBackKey,
                  'can_edit' => $bCanEdit,
            );
            return $oTemplate->render($aTemplateData);
        }
        /*
        $help_path = KTHelp::getHelpSubPath($pathinfo);

        if ($help_path == false) {
            $this->oPage->setTitle(_kt('Invalid help location specified.'));
            $this->oPage->addError(_kt('Invalid help location specified.'));
            return '&nbsp';
        }
        
        // We now check for substitute help files.  try to generate an error.
        $oReplacementHelp = KTHelpReplacement::getByName($help_path);

        if (KTHelp::isImageFile($help_path)) {
            KTHelp::outputHelpImage($help_path);
        } else {
            // not an image, so:
            $aHelpInfo = KTHelp::getHelpFromFile($pathinfo)
        }
        
        
        // NORMAL users never see edit-option.
        if (!$can_edit) {
            if (!PEAR::isError($oReplacementHelp)) {
                $this->oPage->setTitle($oReplacementHelp->getTitle());
                //return $oReplacementHelp->getDescription();
            } elseif ($aHelpInfo != false) {
                $this->oPage->setTitle($aHelpInfo['title']);
                //return $aHelpInfo['body'];
            } else {
                $this->oPage->setTitle(_kt('Invalid help location specified.'));
                $this->oPage->addError(_kt('Invalid help location specified.'));
                return '&nbsp';
            }
        } 
        
        if (!PEAR::isError($oReplacementHelp)) {
            $aHelpInfo['title'] = $oReplacementHelp->getTitle();
            $aHelpInfo['body'] = $oReplacementHelp->getDescription();
        }
        // we now _can_ edit.

        
        $this->oPage->setTitle($aHelpInfo['title']);
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => $aHelpInfo['title']);
        
        */
    }

    function do_go_back() {        
        // get referer
        $sBackKey = KTUtil::arrayGet($_REQUEST, 'back_key', false);        
        if($sBackKey) {
            $sReferer = $_SESSION[$sBackKey];
            redirect($sReferer);
            exit(0);
        } else {
            $this->errorRedirectToMain(_kt('Invalid return key from help system.'));
        }        
    }
}

$oDispatcher = new HelpDispatcher();
$oDispatcher->dispatch();

?>

