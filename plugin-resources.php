<?php

require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');

$sPath = KTUtil::arrayGet($_SERVER, 'PATH_INFO');
if (empty($sPath)) {
    print "Nothing there...";
    exit(1);
}

if (!KTPluginUtil::resourceIsRegistered($sPath)) {
    print "Accessing unregistered resource";
    exit(1);
}

KTPluginUtil::readResource($sPath);
exit(0);

?>
