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

class DiskUsageDashlet extends KTBaseDashlet
{
	private $dfCmd;
	private $usage;
	private $warningPercent;
	private $urgentPercent;

	function DiskUsageDashlet()
	{
		$this->sTitle = _kt('Disk Usage');
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

		$this->getUsage();

		return Permission::userIsSystemAdministrator();
	}

	function getUsage($refresh=false)
	{
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
			$result = ktutil::pexec($this->dfCmd);

			$result = $result['out'];
			unset($result[0]);

			$usage=array();
			foreach($result as $line)
			{
				preg_match('/(.*)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\%\s+(.*)/', $line, $matches);
				list($line, $filesystem, $size, $used, $avail, $usedp, $mount) = $matches;

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
	}

	function render()
	{
		$oTemplating =& KTTemplating::getSingleton();
       	$oTemplate = $oTemplating->loadTemplate('DiskUsage');

		$oRegistry =& KTPluginRegistry::getSingleton();
		$oPlugin =& $oRegistry->getPlugin('ktcore.housekeeper.plugin');

		$dispatcherURL = $oPlugin->getURLPath('HouseKeeperDispatcher.php');


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
