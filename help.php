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
            $oTemplate = $oTemplating->loadTemplate('ktcore/help_with_edit');
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

