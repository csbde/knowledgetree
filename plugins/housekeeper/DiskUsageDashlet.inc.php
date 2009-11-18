<?php

/**
 * $Id: $
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
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

class DiskUsageDashlet extends KTBaseDashlet
{
    private $usage;

    function DiskUsageDashlet()
    {
        $this->sTitle = _kt('Storage Utilization');
        $this->sClass = "ktInfo";
    }

    function is_active($oUser)
    {
        if (OS_WINDOWS && ((float) php_uname('r') >= 6)) return false;

        $usage = KTUtil::getSystemSetting('DiskUsage');
        if (empty($usage)) return false;
        $usage = unserialize($usage);
        $this->usage = $usage;
        return Permission::userIsSystemAdministrator();
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
        if (!empty($rootUrl)) $dispatcherURL = $rootUrl . $dispatcherURL;
        $dispatcherURL = str_replace( '\\', '/', $dispatcherURL);
        if ( substr( $dispatcherURL, 0,1 ) != '/')
        {
            $dispatcherURL = '/'.$dispatcherURL;
        }

        $warningPercent = $config->get('DiskUsage/warningThreshold', 15);
        $urgentPercent = $config->get('DiskUsage/urgentThreshold', 5);

        $aTemplateData = array(
        'context' => $this,
        'usages'=> $this->usage,
        'warnPercent'=>$warningPercent,
        'urgentPercent'=>$urgentPercent,
        'dispatcherURL'=>$dispatcherURL
        );

        return $oTemplate->render($aTemplateData);
    }
}

?>
