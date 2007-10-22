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
