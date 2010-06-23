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

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once("comments.php");

class AjaxCommentsDispatcher extends KTStandardDispatcher {
    var $bAutomaticTransaction = true;
    function do_main() {
        return "Ajax Error.";
    }

    // a lot simpler than the standard dispatcher, this DOESN'T include a large amount of "other" stuff ... we are _just_ called to handle
    // input/output of simple HTML components.
    function handleOutput($data) {
        print $data;
    }

    // do you want the fieldset_id here?
    function do_postComment() {
        global $default;
        $default->log->error(http_build_query($_REQUEST));

        $comment = KTUtil::arrayGet($_REQUEST, 'comment');
        $documentId = KTUtil::arrayGet($_REQUEST, 'documentId');

        Comments::add_comment($documentId, $comment);

        sleep(5);

        // Ensure comment is sanitised to prevent xss attacks
        $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8', FALSE);

        echo '<div class="item newcomment">
	            <div class="icon"><img src="http://www.gravatar.com/avatar/'.md5($this->getEmailForId($_SESSION['userID'])).'?s=48" alt="'.$this->getUserForId($_SESSION['userID']).'" title="'.$this->getUserForId($_SESSION['userID']).'" /></div>
	            <div class="activityfeed_content">
	                <span class="date">'._kt('1 second ago').'</span>
	                <span class="user">'.$this->getUserForId($_SESSION['userID']).'</span>

	                <span class="description">'.nl2br($comment).'</span>
	            </div>
	        </div>';
    }

    function getUserForId($iUserId) {
        $u = User::get($iUserId);
        if (PEAR::isError($u) || ($u == false)) { return _kt('User no longer exists'); }
        return $u->getName();
    }

	function getEmailForId($iUserId) {
        $u = User::get($iUserId);

        if (PEAR::isError($u) || ($u == false)) { return _kt('User no longer exists'); }
        return $u->getEmail();
    }
}

$oDispatcher = new AjaxCommentsDispatcher();
$oDispatcher->dispatch();
?>