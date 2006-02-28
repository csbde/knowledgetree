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

// main library routines and defaults
require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");

require_once(KT_LIB_DIR . "/security/Permission.inc");

require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");
require_once(KT_LIB_DIR . "/help/help.inc.php");

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

    var $sSection = "dashboard";
    var $bIsReplacement = false;

    function HelpDispatcher() {
        $this->aBreadcrumbs[] = array('action' => 'dashboard', 'name' => _('Dashboard'));
        $this->aBreadcrumbs[] = array('name' => _('Help'));
        parent::KTStandardDispatcher();
    }

    function is_replacement() { return $this->bIsReplacement; }

    function do_main() {        
        // store referer
        $sBackKey = KTUtil::arrayGet($_REQUEST, 'back_key', false);
        if(!$sBackKey) {
            $sReferer = KTUtil::arrayGet($_SERVER ,'HTTP_REFERER');
            $sBackKey = KTUtil::randomString();            
            $_SESSION[$sBackKey] = $sReferer;
        } 
        
        $pathinfo = KTUtil::arrayGet($_SERVER, 'PATH_INFO');
        if (empty($pathinfo)) {
            $this->oPage->setTitle(_('No help page specified.'));
            $this->oPage->addError(_('No help page specified.'));
            return '&nbsp;';
        }
        
        $can_edit = Permission::userIsSystemAdministrator($_SESSION['userID']);
               
        $help_path = KTHelp::getHelpSubPath($pathinfo);
        if ($help_path == false) {
            $this->oPage->setTitle(_('Invalid help location specified.'));
            $this->oPage->addError(_('Invalid help location specified.'));
            return '&nbsp';
        }
        
        // We now check for substitute help files.  try to generate an error.
        $oReplacementHelp = KTHelpReplacement::getByName($help_path);
        
        if (KTHelp::isImageFile($help_path)) {
            KTHelp::outputHelpImage($help_path);
        } else {
            // not an image, so:
            $aHelpInfo = KTHelp::getHelpFromFile($pathinfo);
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
                $this->oPage->setTitle(_('Invalid help location specified.'));
                $this->oPage->addError(_('Invalid help location specified.'));
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
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/help_with_edit");
        $aTemplateData = array(
              "context" => $this,
              "help_body" => $aHelpInfo['body'],
              "target_name" => $_SERVER['PATH_INFO'],
              "back_key" => $sBackKey,
              'can_edit' => $can_edit,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_go_back() {        
        // get referer
        $sBackKey = KTUtil::arrayGet($_REQUEST, 'back_key', false);        
        if($sBackKey) {
            $sReferer = $_SESSION[$sBackKey];
            redirect($sReferer);
            exit(0);
        } else {
            $this->errorRedirectToMain(_("Invalid return key from help system."));
        }        
    }
}

$oDispatcher = new HelpDispatcher();
$oDispatcher->dispatch();

?>

