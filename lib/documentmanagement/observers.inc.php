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

class KTMultipartPageObserver {
    function KTMultipartPageObserver() {
        $this->boundary = md5(time());
    }

    function start() {
        ob_implicit_flush();
        header(sprintf("Content-type: multipart/mixed; boundary=%s", $this->boundary));
    }

    function receiveMessage(&$msg) {
        printf("\n--%s\n", $this->boundary);
        echo "Content-Type: text/html\n\n";

        print $msg->getString();
        print "\n";
    }

    function redirect($location) {
        printf("\n--%s\n", $this->boundary);
        echo "Content-Type: text/html\n\n";
        printf("Location: %s\n", $location);
    }

    function end() {
        printf("\n--%s--\n", $this->boundary);
    }
}

class JavascriptObserver {
    function JavascriptObserver(&$context) {
        $this->context =& $context;
    }

    function start() {
        $this->context->oPage->requireJSResource('resources/js/add_document.js');
        $this->context->oRedirector =& $this;
        $this->context->handleOutput('<div id="kt-add-document-target">&nbsp;</div>');
    }

    function receiveMessage(&$msg) {
        if (is_a($msg, 'KTUploadNewFile')) {
            printf('<script language="javascript">kt_add_document_newFile("%s")</script>', $msg->getString());
            return;
        }
        if (mb_detect_encoding($msg->getString()) != 'UTF-8')
		{
			$message = htmlentities($msg->getString(),ENT_QUOTES,'UTF-8');
		}
		else
		{
			$message = $msg->getString();
		}
        $message = str_replace('&amp;', '&', $message);

        printf('<script language="javascript">kt_add_document_addMessage("%s")</script>', $message);
    }

    function redirectToDocument($id) {
        printf('<script language="javascript">kt_add_document_redirectToDocument("%d")</script>', $id);
    }

    function redirectToFolder($id) {
        printf('<script language="javascript">kt_add_document_redirectToFolder("%d")</script>', $id);
    }

    function redirect($url) {
        printf('<script language="javascript">kt_add_document_redirectTo("%s")</script>', $url);
    }


    function end() {
        printf("\n--%s--\n", $this->boundary);
    }
}

class KTSinglePageObserver {
    function KTSinglePageObserver(&$context) {
        $this->context =& $context;
    }

    function start() {
        $this->context->oPage->template = 'kt3/minimal_page';
        $this->context->oRedirector =& $this;
        $this->context->handleOutput("");
    }

    function receiveMessage(&$msg) {
        if (is_a($msg, 'KTUploadNewFile')) {
            print "<h2>" . $msg->getString() . "</h2>";
            return;
        }
        print "<div>" . $msg->getString() . "</div>\n";
    }

    function redirectToDocument($id) {
        $url = generateControllerUrl("viewDocument", sprintf("fDocumentId=%d", $id));
        printf('Go <a href="%s">here</a> to continue', $url);
        printf("</div></div>\n");
    }

    function redirectToFolder($id) {
        $url = generateControllerUrl("browse", sprintf("fFolderId=%d", $id));
        printf('Go <a href="%s">here</a> to continue', $url);
        printf("</div></div>\n");
    }

    function redirect($url) {
        foreach ($_SESSION['KTErrorMessage'] as $sErrorMessage) {
            print '<div class="ktError">' . $sErrorMessage . '</div>' .  "\n";
        }
        printf('Go <a href="%s">here</a> to continue', $url);
        printf("</div></div>\n");
    }

    function end() {
        printf("\n--%s--\n", $this->boundary);
    }
}
