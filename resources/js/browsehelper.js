/* simple collections helper */

function document_collection_setbatching(size, oldurl) {
    var newurl = oldurl + '&page_size=' + size;
    window.location = newurl;
}
