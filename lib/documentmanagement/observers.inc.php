<?php

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
        $this->context->oPage->requireJSResource('thirdpartyjs/MochiKit/Base.js');
        $this->context->oPage->requireJSResource('thirdpartyjs/MochiKit/Iter.js');
        $this->context->oPage->requireJSResource('thirdpartyjs/MochiKit/DOM.js');
        $this->context->oPage->requireJSResource('resources/js/add_document.js');
        $this->context->oRedirector =& $this;
        $this->context->handleOutput('<div id="kt-add-document-target">&nbsp;</div>');
    }

    function receiveMessage(&$msg) {
        if (is_a($msg, 'KTUploadNewFile')) {
            printf('<script language="javascript">kt_add_document_newFile("%s")</script>', $msg->getString());
            return;
        }
        printf('<script language="javascript">kt_add_document_addMessage("%s")</script>', $msg->getString());
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
