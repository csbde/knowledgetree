function kt_add_document_addMessage(message) {
	my_div = getElement('kt-add-document-target');
	appendChildNodes(my_div, DIV(null, message));
}

function kt_add_document_newFile(filename) {
	my_div = getElement('kt-add-document-target');
	H2 = createDOMFunc('h2');
	appendChildNodes(my_div, H2(null, filename));
}

function kt_add_document_redirectToDocument(id) {
	base = getElement('kt-core-baseurl').value;
        document.location.href = base + "/control.php?action=viewDocument&fDocumentId=" + id;
}

function kt_add_document_redirectToFolder(id) {
	base = getElement('kt-core-baseurl').value;
        document.location.href = base + "/control.php?action=browse&fFolderId=" + id;
}

function kt_add_document_redirectTo(url) {
        document.location.href = url;
}
