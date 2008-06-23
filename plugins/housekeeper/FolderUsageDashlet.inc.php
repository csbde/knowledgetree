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

    function render()
    {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('FolderUsage');

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

        $usage = unserialize(KTUtil::getSystemSetting('KTUsage','n/a'));

        $aTemplateData = array(
        'context' => $this,
        'usages'=>$usage,
        'dispatcherURL'=>$dispatcherURL
        );

        return $oTemplate->render($aTemplateData);
    }
}


?>
