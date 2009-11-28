<?php

require_once('../../config/dmsDefaults.php');

// Check the session, ensure the user is logged in
$session = new Session();
$sessionStatus = $session->verify();

if(PEAR::isError($sessionStatus)){
    echo $sessionStatus->getMessage();
    exit;
}

if(!$sessionStatus){
    exit;
}

// Get the document
$documentId = $_GET['documentId'];
$oDocument = Document::get($documentId);

if (PEAR::isError($oDocument)) {
    exit;
}

// Check the document is available and the user has permission to view it
if ($oDocument->getStatusID() == ARCHIVED) {
    exit;
} else if ($oDocument->getStatusID() == DELETED) {
    exit;
}else if (!Permission::userHasDocumentReadPermission($oDocument)) {
    exit;
}

// Get and render the thumbnail
// Check for the thumbnail
$varDir = $default->varDirectory;
$thumbnailCheck = $varDir . '/thumbnails/'.$documentId.'.jpg';

if(!file_exists($thumbnailCheck)){
    exit;
}

// Use correct slashes for windows
if (strpos(PHP_OS, 'WIN') !== false) {
	$thumbnailCheck = str_replace('/', '\\', $thumbnailCheck);
}

$fileSize = filesize($thumbnailCheck);

header("Content-Type: image/jpeg");
header("Content-Length: {$fileSize}");

echo readfile($thumbnailCheck);
exit;
?>