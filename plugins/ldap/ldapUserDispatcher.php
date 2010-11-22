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

require_once('ldapUserManager.php');

class ldapUserDispatcher extends KTStandardDispatcher {

    private $source;

    public function __construct()
    {
        $this->source = KTAuthenticationSource::get($_REQUEST['source_id']);
        parent::KTStandardDispatcher();
    }

    public function do_addUserFromSource()
    {
        $searchResults = '';
        $fields = array();
        // Get source id
        $sourceId = KTUtil::arrayGet($_REQUEST, 'source_id', false);
        // Get the search query
        $name = KTUtil::arrayGet($_REQUEST, 'ldap_name');
        $source = KTAuthenticationSource::get($sourceId);

        if(!is_null($name)) {
            $manager = new LdapUserManager($this->source);
            $searchResults = $manager->search($name);
        }

        $fields[] = new KTStringWidget(_kt("User's name"), _kt("The user's name, or part thereof, to find the user that you wish to add"), 'ldap_name', '', $this->oPage, true);
        $fields[] = new KTCheckboxWidget(_kt('Mass import'),
        _kt('Allow for multiple users to be selected to be added (will not get to manually verify the details if selected)').'.<br>'.
        _kt('The list may be long and take some time to load if the search is not filtered and there are a number of users in the system.')
        , 'massimport', $isMassImport, $this->oPage, true);

        $templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ldap_search_user');
        $templateData = array(
        'context' => $this,
        'fields' => $fields,
        'source' => $this->source,
        'search_results' => $searchResults,
        );

        return  $template->render($templateData);

    }

}
?>