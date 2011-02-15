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

require_once(KT_LIB_DIR . "/plugins/plugin.inc.php");
require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php");

class LdapPlugin extends KTPlugin
{
    public $autoRegister = true;
    public $sNamespace = 'ldap.plugin';

    public function LdapPlugin($filename = null)
    {
        $res = parent::KTPlugin($filename);
        $this->sFriendlyName = _kt('LDAP Authentication Plugin');
        return $res;
    }

    /**
    * Setup the plugin
    *
    * @access public
    */
    public function setup()
    {
        $this->registerAuthenticationProvider(_kt('LDAP Authentication 2'), 'LdapAuthProvider', 'ldap.auth.provider', 'ldapAuthProvider.php');

        //$this->registerAdminPage('ldapauth', 'LdapConfigurationAdminPage', 'userSetup', _kt('LDAP Authentication Configuration'), _kt('Configure the connection to an external LDAP server for authentication of users.'), 'ldapConfigAdminPage.php');

        $templating = KTTemplating::getSingleton();
        $templating->addLocation('ldap auth', '/plugins/ldap/templates');
    }
}

$pluginRegistry =& KTPluginRegistry::getSingleton();
$pluginRegistry->registerPlugin('LdapPlugin', 'ldap.plugin', __FILE__);
?>