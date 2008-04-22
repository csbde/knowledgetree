<?php

/**
 * $Id: $
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
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

		if (is_dir($path) && ($dh = opendir($path)))
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
					'filesize'=>KTUtil::filesizeToString($temp['filesize']/1024),
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

		$config = KTConfig::getSingleton();
		$rootUrl = $config->get('KnowledgeTree/rootUrl');

		$dispatcherURL = $oPlugin->getURLPath('HouseKeeperDispatcher.php');
		if (!empty($rootUrl)) $dispatcherURL .= $rootUrl . $dispatcherURL;
        if ( substr( $dispatcherURL, 0,1 ) == '/' || substr( $dispatcherURL, 0,1 ) == '\\')
		{
			$dispatcherURL = substr($dispatcherURL,1);
		}
        $dispatcherURL = str_replace( '\\', '/', $dispatcherURL);
		
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
