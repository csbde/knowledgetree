<?php

require_once('../../config/dmsDefaults.php');

function documentNotAccessible($document)
{
    return $document->getStatusID() == ARCHIVED
        || $document->getStatusID() == DELETED
        || !Permission::userHasDocumentReadPermission($document);
}

// Check the session, ensure the user is logged in
$session = new Session();
$sessionStatus = $session->verify();

if (PEAR::isError($sessionStatus)) {
    echo $sessionStatus->getMessage();
    exit;
}

if (!$sessionStatus) {
    exit;
}

// Get the document
$documentId = $_GET['documentId'];
$document = Document::get($documentId);

if (PEAR::isError($document) || documentNotAccessible($document)) {
    exit;
}

$storageManager = KTStorageManagerUtil::getSingleton();
$thumbnailCheck = $default->varDirectory . '/thumbnails/' . $documentId . '.jpg';
if (!$storageManager->file_exists($thumbnailCheck)) {
    exit;
}

if (strpos(PHP_OS, 'WIN') !== false) {
    $thumbnailCheck = str_replace('/', '\\', $thumbnailCheck);
}

$fileSize = $storageManager->fileSize($thumbnailCheck);

header("Content-Type: image/jpeg");
header("Content-Length: {$fileSize}");

echo $storageManager->file_get_contents($thumbnailCheck);
exit;

?>
