<?php

/**
 *
 * Copyright (c) 2007 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.knowledgetree.com/
 */

class FolderUsageDashlet extends KTBaseDashlet
{
	private $usage;

	function FolderUsageDashlet()
	{
		$this->sTitle = _kt('System Folder Usage');
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
