<?php

/**
 * $Id
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 *
 * The Original Code is: KnowledgeTree Open Source
 *
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 */

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
