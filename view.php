<?php

/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
 * Contributor(s): ______________________________________
 */

// boilerplate.
require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once(KT_LIB_DIR . '/users/userutil.inc.php');
require_once(KT_LIB_DIR . '/database/dbutil.inc');
require_once(KT_LIB_DIR . '/util/sanitize.inc');

// document related includes
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentFieldLink.inc');
require_once(KT_LIB_DIR . '/documentmanagement/documentmetadataversion.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentcontentversion.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');
require_once(KT_LIB_DIR . '/security/Permission.inc');

// widget includes.
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldsetDisplay.inc.php');
require_once(KT_LIB_DIR . '/widgets/FieldsetDisplayRegistry.inc.php');
require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

// views includes
require_once(KT_LIB_DIR . '/views/viewdocument.php');
require_once(KT_LIB_DIR . '/views/sharedviewdocument.php');

/**
 * Utility class to switch between user specific document views
 *
 */
class ViewUtil {

    static function getView()
    {
        $user = User::get($_SESSION['userID']);
        $userType = (PEAR::isError($user)) ? 0 : $user->getDisabled();

        switch ($userType) {
            case 0:
                return new ViewDocumentDispatcher();
                break;
            case 4:
                return new SharedViewDocumentDispatcher();
                break;
            default:
                return new ViewDocumentDispatcher();
                break;
        }
    }

}

$dispatcher = ViewUtil::getView();
$dispatcher->dispatch();

?>
