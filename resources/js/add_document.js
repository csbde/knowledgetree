function kt_add_document_addMessage(message) {
    var my_div = getElement('kt-add-document-target');
    appendChildNodes(my_div, DIV(null, message));
}

function kt_add_document_newFile(filename) {
    var my_div = getElement('kt-add-document-target');
    H2 = createDOMFunc('h2');
    appendChildNodes(my_div, H2(null, filename));
}

function kt_add_document_redirectToDocument(id) {
    var base = getElement('kt-core-baseurl').value;
    var href = base + "/control.php?action=viewDocument&fDocumentId=" + id;
    document.location.href = href;
}

function kt_add_document_redirectToFolder(id) {
    var base = getElement('kt-core-baseurl').value;
    document.location.href = base + "/control.php?action=browse&fFolderId=" + id;
}

function kt_add_document_redirectTo(url) {
    document.location.href = url;
}
