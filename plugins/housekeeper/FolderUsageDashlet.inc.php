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

class FolderUsageDashlet extends KTBaseDashlet
{
	private $usage;

	function FolderUsageDashlet()
	{
		$this->sTitle = _kt('System Folder Utilization');
		$this->sClass = "ktInfo";
	}

	function is_active($oUser)
	{
		return Permission::userIsSystemAdministrator();
	}

	function scanPath($path,$pattern)
	{
		$files=0;
		$filesize=0;

		if ($dh = opendir($path))
		{
			while (($file = readdir($dh)) !== false)
			{
				if (substr($file,0,1) == '.')
				{
					continue;
				}

				$full = $path . '/' . $file;

				if (!is_readable($full) || !is_writable($full))
				{
					continue;
				}

				if (is_dir($full))
				{
					$result = $this->scanPath($full,$pattern);
					$files += $result['files'];
					$filesize += $result['filesize'];
					continue;
				}
				if ($pattern != '')
				{
					if (preg_match('/' . $pattern . '/', $file) === false)
					{
						continue;
					}
				}

				$files++;
				$filesize += filesize($full);
			}
			closedir($dh);
		}
		return array('files'=>$files,'filesize'=>$filesize,'dir'=>$path);
	}

	function getUsage()
	{
		$check = true;
    	// check if we have a cached result
		if (isset($_SESSION['SystemFolderUsage']))
		{
			// we will only do the check every 5 minutes
			if (time() - $_SESSION['SystemFolderUsage']['time'] < 5 * 60)
			{
				$check = false;
				$this->usage = $_SESSION['SystemFolderUsage']['usage'];
			}
		}

		// we will only check if the result is not cached, or after 5 minutes
		if ($check)
		{
			$usage = array();

			$oRegistry =& KTPluginRegistry::getSingleton();
			$oPlugin =& $oRegistry->getPlugin('ktcore.housekeeper.plugin');

			$folders = $oPlugin->getDirectories();

			foreach($folders as $folder)
			{
				$directory 	= $folder['folder'];
				$pattern 	= $folder['pattern'];
				$canClean 	= $folder['canClean'];
				$name 		= $folder['name'];

				$temp = $this->scanPath($directory,$pattern);

				$usage[] = array(
					'description'=>$name,
					'folder'=>$directory,
					'files'=>number_format($temp['files'],0,'.',','),
					'filesize'=>KTUtil::filesizeToString($temp['filesize']),
					'action'=>$i,
					'canClean'=>$canClean
				);
				$this->usage = $usage;
			}

			$_SESSION['SystemFolderUsage']['time'] = time();
			$_SESSION['SystemFolderUsage']['usage'] = $this->usage;
		}
	}

	function render()
	{
		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('FolderUsage');

		$oRegistry =& KTPluginRegistry::getSingleton();
		$oPlugin =& $oRegistry->getPlugin('ktcore.housekeeper.plugin');

		$dispatcherURL = $oPlugin->getURLPath('HouseKeeperDispatcher.php');

		$this->getUsage();

		$aTemplateData = array(
				'context' => $this,
				'usages'=>$this->usage,
				'dispatcherURL'=>$dispatcherURL
			);

		return $oTemplate->render($aTemplateData);
	}
}


?>
