<?php

session_start();

require_once("../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");

class HouseKeeperDispatcher extends KTStandardDispatcher
{
    function cleanDirectory($path, $pattern)
	{
		if (!is_readable($path))
		{
			return;
		}
		if ($dh = opendir($path))
		{
			while (($file = readdir($dh)) !== false)
			{
				if (substr($file,0,1) == '.')
				{
					continue;
				}

				$full = $path . '/' . $file;
				if (is_dir($full))
				{
					 $this->cleanDirectory($full,$pattern);
					 if (is_writable($full))
					 {
					 	@rmdir($full);
					 }
					continue;
				}

				if (!empty($pattern) && !preg_match('/' . $pattern . '/', $file))
				{
					continue;
				}

				if (is_writable($full))
				{
					@unlink($full);
				}

			}
			closedir($dh);
		}

	}

	function do_cleanup()
	{
		$folder = KTUtil::arrayGet($_REQUEST, 'folder');
		if (is_null($folder))
		{
			exit(redirect(generateControllerLink('dashboard')));
		}

		$oRegistry =& KTPluginRegistry::getSingleton();
		$oPlugin =& $oRegistry->getPlugin('ktcore.housekeeper.plugin');

        // we must avoid doing anything to the documents folder at all costs!
        $folder = $oPlugin->getDirectory($folder);
        if (is_null($folder) || !$folder['canClean'])
        {
        	exit(redirect(generateControllerLink('dashboard')));
        }

		$this->cleanDirectory($folder['folder'], $folder['pattern']);

		$this->do_refreshFolderUsage();
	}

	function do_refreshDiskUsage()
	{
		session_unregister('DiskUsage');
		exit(redirect(generateControllerLink('dashboard')));
	}

	function do_refreshFolderUsage()
	{
		session_unregister('SystemFolderUsage');
		exit(redirect(generateControllerLink('dashboard')));
	}
}
$oDispatcher = new HouseKeeperDispatcher();
$oDispatcher->dispatch();

?>