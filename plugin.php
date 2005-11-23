<?php

require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/plugins/pageregistry.inc.php');

$oRegistry =& KTPageRegistry::getSingleton();

$sPath = KTUtil::arrayGet($_SERVER, 'PATH_INFO');
if (empty($sPath)) {
    print "Nothing there...";
    exit(1);
}

$sPath = trim($sPath, '/ ');
$oPage = $oRegistry->getPage($sPath);
if (empty($oPage)) {
    print "Accessing unregistered resource";
    exit(1);
}

$oPage->dispatch();
