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

require_once('LdapGroupManager.inc.php');

class ldapGroupDispatcher extends KTAdminDispatcher {

    private $source;

    public function __construct()
    {
        $this->source = KTAuthenticationSource::get($_REQUEST['source_id']);

        $category = KTUtil::arrayGet($_REQUEST, 'fCategory');
        $subsection = KTUtil::arrayGet($_REQUEST, 'subsection');
        $this->setCategoryDetail("$category/$subsection");
        
        parent::KTStandardDispatcher();
    }

    public function do_addGroupFromSource()
    {
        $submit = KTUtil::arrayGet($_REQUEST, 'submit');
        if (!is_array($submit)) {
            $submit = array();
        }

        if (KTUtil::arrayGet($submit, 'chosen')) {
            $id = KTUtil::arrayGet($_REQUEST, 'id');
            if (!empty($id)) {
                return $this->_do_editGroupFromSource();
            }
            else {
                $this->oPage->addError(_kt('No valid LDAP group chosen'));
            }
        }

        if (KTUtil::arrayGet($submit, 'create')) {
            return $this->_do_createGroupFromSource();
        }

        $template = $this->oValidator->validateTemplate('ldap_search_group');

        $fields = array();
        $fields[] = new KTStringWidget(_kt("Group's name"), _kt("The group's name, or part thereof, to find the group that you wish to add"), 'name', '', $this->oPage, true);

        // TODO old ldap authenticator did not appear to validate against existing groups as it did for users;
        //      if we can do it here then it may be worthwhile.
        $groups = array();
        $name = KTUtil::arrayGet($_REQUEST, 'name');
        if (!empty($name)) {
            $manager = new ldapGroupManager($this->source);
            try {
                $searchResults = $manager->searchGroups($name);
                if ($searchResults->count()) {
                    // make sure we start from the beginning
                    $searchResults->rewind();
                    // get group results
                    foreach ($searchResults as $key => $result) {
                        if (is_array($result['cn'])) {
                            $result['cn'] = $result['cn'][0];
                        }
                        $groups[] = $result;
                    }
                }
            }
            catch (Exception $e) {
                $this->addErrorMessage($e->getMessage());
            }
        }

        $templateData = array(
            'context' => &$this,
            'fields' => $fields,
            'source' => $this->source,
            'search_results' => $groups,
            'identifier_field' => 'displayName',
            'section_query_string' => $this->sectionQueryString
        );

        return $template->render($templateData);
    }

    private function _do_createGroupFromSource()
    {
        $dn = KTUtil::arrayGet($_REQUEST, 'dn');
        $name = KTUtil::arrayGet($_REQUEST, 'ldap_groupname');
        if (empty($name)) {
            $this->errorRedirectToMain(_kt('You must specify a name for the group.'));
        }

        $isUnitAdmin = KTUtil::arrayGet($_REQUEST, 'is_unitadmin', false);
        $isSysAdmin = KTUtil::arrayGet($_REQUEST, 'is_sysadmin', false);

        $group = Group::createFromArray(array(
            'name' => $name,
            'isunitadmin' => $isUnitAdmin,
            'issysadmin' => $isSysAdmin,
            'authenticationdetails' => $dn,
            'authenticationsourceid' => $this->source->getId(),
        ));

        if (PEAR::isError($group) || ($group == false)) {
            $this->errorRedirectToMain(_kt('failed to create group.'));
            exit(0);
        }

        $manager = new LdapGroupManager($this->source);
        $manager->synchroniseGroup($group);

        $this->successRedirectToMain(_kt('Created new group') . ': ' . $group->getName());
        exit(0);
    }

    private function _do_editGroupFromSource()
    {
        $template = $this->oValidator->validateTemplate('ldap_add_group');
        $id = KTUtil::arrayGet($_REQUEST, 'id');

        $manager = new LdapGroupManager($this->source);
        try {
            $attributes = $manager->getGroup($id);
        }
        catch (Exception $e) {
            global $default;
            $default->log->error("There was an error getting the group information from the ldap server: {$e->getMessage()}");
        }

        $fields = array();
        $fields[] = new KTStaticTextWidget(_kt('LDAP DN'), _kt('The location of the group within the LDAP directory.'), 'dn', $attributes['dn'], $this->oPage);
        $fields[] = new KTStringWidget(_kt('Group Name'), sprintf(_kt('The name the group will enter to gain access to %s.  e.g. accountants'), APP_NAME), 'ldap_groupname', $attributes['cn'][0], $this->oPage, true);
        $fields[] = new KTCheckboxWidget(_kt('Unit Administrators'), _kt('Should all the members of this group be given unit administration privileges?'), 'is_unitadmin', false, $this->oPage, false);
        $fields[] = new KTCheckboxWidget(_kt('System Administrators'), _kt('Should all the members of this group be given system administration privileges?'), 'is_sysadmin', false, $this->oPage, false);

        $templateData = array(
            'context' => &$this,
            'fields' => $fields,
            'source' => $this->source,
            'search_results' => $searchResults,
            'dn' => $attributes['dn'],
            'section_query_string' => $this->sectionQueryString
        );

        return $template->render($templateData);
    }

}
?>
