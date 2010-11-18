<?php
/**
 * $Id$
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
 *
 */

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");
require_once('ldapUserManagement.php');
require_once(KT_LIB_DIR . "/authentication/authenticationsource.inc.php");

class LDAPManagementDispatcher extends KTAdminDispatcher {
    
    function do_main() {

        $oTemplating = KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ldap_search");
        $aTemplateData = array(
            "context" => $this,
            "authentication_sources" => KTAuthenticationSource::getList(),
        );
        $ldapsearch = $oTemplate->render($aTemplateData);
        
        $oTemplating = KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ldap_management");
        $aTemplateData = array(
            "context" => $this,
            "ldapsearch" => $ldapsearch,
        );
        
        return $oTemplate->render($aTemplateData);
    }
    
    function do_addUserFromSource() {
    	// Take you too user ldap file
    	
        $oSource = KTAuthenticationSource::get($_REQUEST['source_id']);
        $sProvider = $oSource->getAuthenticationProvider();
        $oRegistry = KTAuthenticationProviderRegistry::getSingleton();
        $oProvider = $oRegistry->getAuthenticationProvider($sProvider);

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('User Management'));
        $this->aBreadcrumbs[] = array('url' => KTUtil::addQueryStringSelf('action=addUser'), 'name' => _kt('add a new user'));
        $oProvider->aBreadcrumbs = $this->aBreadcrumbs;
        $oProvider->oPage->setBreadcrumbDetails($oSource->getName());
        $oProvider->oPage->setTitle(_kt("Add New User"));

        $oProvider->dispatch();
        exit(0);
    }
}
?>