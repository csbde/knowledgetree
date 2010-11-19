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

require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

require_once(KT_LIB_DIR . '/authentication/authenticationprovider.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationsource.inc.php');
require_once(KT_PLUGIN_DIR . '/ktstandard/ldap/ldapauthenticationprovider.inc.php');

require_once('ldapGroupManager.php');

class ldapGroupDispatcher extends KTStandardDispatcher 
{
    private $source;
	private $manager;
	
	/*private function setManager()
	{
     	$this->manager = new ldapGroupManager($this->source);
	}*/
	
	/*public function __construct()
	{
	    parent::KTStandardDispatcher();
	}*/
	
	// TODO I think this can stand some more refactoring, because it looks like some stuff is done regardless of which action is chosen
    public function do_addGroupFromSource()
    {
        $submit = KTUtil::arrayGet($_REQUEST, 'submit');
        if (!is_array($submit)) {
            $submit = array();
        }
        $this->source = KTAuthenticationSource::get($_REQUEST['source_id']);
        if (KTUtil::arrayGet($submit, 'chosen')) {
            $id = KTUtil::arrayGet($_REQUEST, 'id');
            if (!empty($id)) {
                return $this->_do_editGroupFromSource();
            } else {
                $this->oPage->addError(_kt("No valid LDAP group chosen"));
            }
        }
        
        if (KTUtil::arrayGet($submit, 'create')) {
            return $this->_do_createGroupFromSource();
        }
                
        $template = $this->oValidator->validateTemplate('ktstandard/authentication/ldapsearchgroup');

        $fields = array();
        $fields[] = new KTStringWidget(_kt("Group's name"), _kt("The group's name, or part thereof, to find the group that you wish to add"), 'name', '', $this->oPage, true);

        $name = KTUtil::arrayGet($_REQUEST, 'name');
        if (!empty($name)) {
            $manager = new ldapGroupManager($this->source);
            $searchResults = $manager->searchGroups($name);

            if (PEAR::isError($searchResults)) {
                $this->addErrorMessage($searchResults->getMessage());
                $searchResults = array();
            }
        }

        $templateData = array(
            'context' => &$this,
            'fields' => $fields,
            'source' => $this->source,
            'search_results' => $searchResults,
            'identifier_field' => 'displayName',
        );
        
        return $template->render($templateData);
    }
    
    private function _do_editGroupFromSource()
    {
        $template = $this->oValidator->validateTemplate('ktstandard/authentication/ldapaddgroup');
        $source =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $id = KTUtil::arrayGet($_REQUEST, 'id');

        $aConfig = unserialize($source->getConfig());

        $authenticator = $this->getAuthenticator($source);
        $attributes = $authenticator->getGroup($id);

        $fields = array();
        $fields[] = new KTStaticTextWidget(_kt('LDAP DN'), _kt('The location of the group within the LDAP directory.'), 'dn', $attributes['dn'], $this->oPage);
        $fields[] = new KTStringWidget(_kt('Group Name'), sprintf(_kt('The name the group will enter to gain access to %s.  e.g. <strong>accountants</strong>'), APP_NAME), 'ldap_groupname', $attributes['cn'], $this->oPage, true);
        $fields[] = new KTCheckboxWidget(_kt('Unit Administrators'), _kt('Should all the members of this group be given <strong>unit</strong> administration privileges?'), 'is_unitadmin', false, $this->oPage, false);
        $fields[] = new KTCheckboxWidget(_kt('System Administrators'), _kt('Should all the members of this group be given <strong>system</strong> administration privileges?'), 'is_sysadmin', false, $this->oPage, false);

        $templateData = array(
            'context' => &$this,
            'fields' => $fields,
            'source' => $source,
            'search_results' => $searchResults,
            'dn' => $attributes['dn'],
        );
        
        return $template->render($templateData);
    }
    
    private function _do_createGroupFromSource()
    {
        $source =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $dn = KTUtil::arrayGet($_REQUEST, 'dn');
        $name = KTUtil::arrayGet($_REQUEST, 'ldap_groupname');
        if (empty($name)) { $this->errorRedirectToMain(_kt('You must specify a name for the group.')); }

        $is_unitadmin = KTUtil::arrayGet($_REQUEST, 'is_unitadmin', false);
        $is_sysadmin = KTUtil::arrayGet($_REQUEST, 'is_sysadmin', false);

        $group =& Group::createFromArray(array(
            "name" => $name,
            "isunitadmin" => $is_unitadmin,
            "issysadmin" => $is_sysadmin,
            "authenticationdetails" => $dn,
            "authenticationsourceid" => $source->getId(),
        ));

        if (PEAR::isError($group) || ($group == false)) {
            $this->errorRedirectToMain(_kt("failed to create group."));
            exit(0);
        }

        $authenticator = $this->getAuthenticator($source);
        $authenticator->synchroniseGroup($group);

        $this->successRedirectToMain(_kt('Created new group') . ': ' . $group->getName());
        exit(0);
    }
    
    /*private function getAuthenticator($source) {
        return new KTLDAPAuthenticator($source);
    }*/
    
}
?>