<?php
/**
 * $Id: KTCorePlugin.php 7954 2008-01-25 05:56:52Z megan_w $
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
 *
 */

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

class BaseConfigDispatcher extends KTAdminDispatcher
{
	function check() {
        return parent::check();
    }

	function do_main($sQuery)
	{
		if(empty($sQuery))
		{
			$sQuery = '';
		}
		$aResults = DBUtil::getResultArray($sQuery);

        //populating paths correctly
        $oKTConfig =& KTConfig::getSingleton();

        for($i = 0; $i < count($aResults); $i++)
        {
        	if(strstr($aResults[$i]['value'],'$') != false)
        	{
        		$aResults[$i]['value'] = $oKTConfig->get($aResults[$i]['group_name'].'/'.$aResults[$i]['item']);
        	}
        }

        //If template has posted changes for config settings save all values to db.
        if(isset($_POST['configArray']))
        {

        	foreach ($aResults as $values)
        	{

        		//IF current db entries id is in the array sent back by the page AND
        		//the values for the db and the page are different, update the db.
        		if(isset($_POST['configArray'][$values['id']]) && $_POST['configArray'][$values['id']]
        		!= $values['value'])
        		{
        			//update entry
        			$aFields = array();
        			if($values['type'] == 'boolean')
        			{
	        			if($_POST['configArray'][$values['id']] == 'true')
	        			{
		        			$aFields['value'] = true;

	        			}
	        			else
	        			{
	        				$aFields['value'] = false;
	        			}
        			}
        			else
        			{
        				$aFields['value'] =  $_POST['configArray'][$values['id']];
        			}
        			$oUpdateResult = DBUtil::autoUpdate('config_settings', $aFields, $values['id']);
        		}
        	}

        	// Clear the cached settings
        	$oKTConfig->clearCache();
        }

        //Get new results after any db change above
        if(isset($_POST['configArray']))
        {
        	$aResults = DBUtil::getResultArray($sQuery);
        	for($i = 0; $i < count($aResults); $i++)
        	{
	        	if(strstr($aResults[$i]['value'],'$') != false)
	        	{
	        		$aResults[$i]['value'] = $oKTConfig->get($aResults[$i]['group_name'].'/'.$aResults[$i]['item']);
	        	}
        	}
        }

        $oTemplating =& KTTemplating::getSingleton();

        $oTemplate =& $oTemplating->loadTemplate('ktcore/configsettings');

        //set db config data being sent to template
        $oTemplate->setData(array(
            'results' => $aResults

        ));
        return $oTemplate;
	}
}

class UIConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('User Interface Settings'),
        );
        return parent::check();
    }

    function do_main() {

        //get config settings from db
        $sQuery = 'select id, group_name, item, type, value, helptext, default_value from config_settings where group_name = \'ui\'order by group_name';
        return parent::do_main($sQuery);
    }
}

class ClientSettingsConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Client Tools Settings'),
        );
        return parent::check();
    }

    function do_main() {

        //get config settings from db
        $sQuery = 'select id, group_name, item, type, value, helptext, default_value from config_settings where
        		group_name = \'KTWebDAVSettings\' or group_name = \'BaobabSettings\' or
        		group_name = \'webservice\' or group_name = \'clientToolPolicies\' order by group_name';
        return parent::do_main($sQuery);
	}
}

class EmailConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Email Settings'),
        );
        return parent::check();
    }

    function do_main() {

        //get config settings from db
        $sQuery = 'select id, group_name, item, type, value, helptext, default_value from config_settings where group_name = \'email\'order by group_name';
        return parent::do_main($sQuery);
    }
}

class GeneralConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('General Settings'),
        );
        return parent::check();
    }

    function do_main() {

        //get config settings from db
        $sQuery = 'select id, group_name, item, type, value, helptext, default_value from config_settings where
        		item = \'schedulerInterval\' or item = \'fakeMimetype\'
        		or item = \'browseToUnitFolder\' order by group_name';
        return parent::do_main($sQuery);
    }
}

class i18nConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Internationalisation Settings'),
        );
        return parent::check();
    }

    function do_main() {

        //get config settings from db
        $sQuery = 'select id, group_name, item, type, value, helptext, default_value from config_settings where
        		group_name = \'i18n\' order by group_name';
        return parent::do_main($sQuery);
    }
}

class SearchAndIndexingConfigPageDispatcher extends BaseConfigDispatcher
{
    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Search and Indexing Settings'),
        );
        return parent::check();
    }

    function do_main() {

        //get config settings from db
        $sQuery = 'select id, group_name, item, type, value, helptext, default_value from config_settings where
        		group_name = \'search\' or group_name = \'indexer\'order by group_name';
        return parent::do_main($sQuery);
    }
}
?>
