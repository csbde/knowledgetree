<?php

/**
 * $Id: $
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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

class DiskUsageDashlet extends KTBaseDashlet
{
	private $dfCmd;
	private $usage;
	private $warningPercent;
	private $urgentPercent;

	function DiskUsageDashlet()
	{
		$this->sTitle = _kt('Storage Utilization');
		$this->sClass = "ktInfo";
	}

	function is_active($oUser)
	{
		$dfCmd = KTUtil::findCommand('externalBinary/df','df');
		if ($dfCmd === false)
		{
			return false;
		}
		$this->dfCmd = $dfCmd;

		$config = KTConfig::getSingleton();
		$this->warningPercent = $config->get('DiskUsage/warningThreshold', 15);
		$this->urgentPercent = $config->get('DiskUsage/urgentThreshold', 5);

		$got_usage = $this->getUsage();

		if ($got_usage == false)
		{
			return false;
		}

		return Permission::userIsSystemAdministrator();
	}

	function getUsage($refresh=false)
	{
		if (isset($_SESSION['DiskUsage']['problem']))
		{
			return false;
		}

    	$check = true;
    	// check if we have a cached result
		if (isset($_SESSION['DiskUsage']))
		{
			// we will only do the check every 5 minutes
			if (time() - $_SESSION['DiskUsage']['time'] < 5 * 60)
			{
				$check = false;
				$this->usage = $_SESSION['DiskUsage']['usage'];
			}
		}

		// we will only check if the result is not cached, or after 5 minutes
		if ($check)
		{
			$cmd = $this->dfCmd;

			if (OS_WINDOWS)
			{
				$cmd = str_replace( '/','\\',$cmd);
				$res = KTUtil::pexec("\"$cmd\" -B 1 2>&1");
				$result = implode("\r\n",$res['out']);
			}
			else
			{
				$result = shell_exec($cmd." -B 1 2>&1");
			}

			if (strpos($result, 'cannot read table of mounted file systems') !== false)
			{
				$_SESSION['DiskUsage']['problem'] = true;
				return false;
			}


			$result = explode("\n", $result);

			unset($result[0]); // gets rid of headings

			$usage=array();
			foreach($result as $line)
			{
				if (empty($line)) continue;
				preg_match('/(.*)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\%\s+(.*)/', $line, $matches);
				list($line, $filesystem, $size, $used, $avail, $usedp, $mount) = $matches;

				if ($size === 0) continue;

				$colour = '';
				if ($usedp >= 100 - $this->urgentPercent)
				{
					$colour = 'red';
				}
				elseif ($usedp >= 100 - $this->warningPercent)
				{
					$colour = 'orange';
				}

				$usage[] = array(
						'filesystem'=>$filesystem,
						'size'=>KTUtil::filesizeToString($size),
						'used'=>KTUtil::filesizeToString($used),
						'available'=>KTUtil::filesizeToString($avail),
						'usage'=>$usedp . '%',
						'mounted'=>$mount,
						'colour'=>$colour
					);
			}

			$this->usage = $usage;

    		$_SESSION['DiskUsage']['time'] = time();
    		$_SESSION['DiskUsage']['usage'] = $this->usage;
		}

		return true;
	}

	function render()
	{
		$oTemplating =& KTTemplating::getSingleton();
       	$oTemplate = $oTemplating->loadTemplate('DiskUsage');

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


		$aTemplateData = array(
			'context' => $this,
			'usages'=>$this->usage,
			'warnPercent'=>$this->warningPercent,
			'urgentPercent'=>$this->urgentPercent,
			'dispatcherURL'=>$dispatcherURL
		);

        return $oTemplate->render($aTemplateData);
    }
}


?>
