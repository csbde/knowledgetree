<?php
/**
 * $Id$
 *
 * Contains datetime functions.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
require_once(KT_LIB_DIR . '/datetime/timezones.inc');

class datetime_view extends KTAdminDispatcher
{
	/**
	 * Load datetime js and css
	 * As this is a view class,
	 * assume we will need them both.
	 *
	 */
	public function __construct()
	{
        global $main;
        $main->requireJSResource('plugins/datetime/resources/js/datetime.js');
        $main->requireCSSResource('plugins/datetime/resources/css/datetime.css');
	}
	
	/**
	 * Renders a list of standard timezone options to be used in a dropdown
	 *
	 * @return string
	 */
	static public function renderRegions($value)
	{
		$tzc = new TimezoneConversion();
		$ddoptions = '';
		$aValue = explode('/', $value);
		$currentRegion = isset($aValue[1]) ? $aValue[0] : 'Other';
		foreach ($tzc->getPhpRegions() as $region)
		{
			$selected = ($region == $currentRegion) ? 'selected' : '';
			$ddoptions .= '<option value="' . $region . '" ' . $selected . '> ' . $region . '</option>';
		}
		
		return $ddoptions;
	}
	
	/**
	 * Renders a list of standard timezone options to be used in a dropdown
	 *
	 * @return string
	 */
	static public function renderTimezones($value, $region = false)
	{
		$tzc = new TimezoneConversion();
		$ddoptions = '';
		$aValue = explode('/', $value);
		$currentRegion = isset($aValue[1]) ? $aValue[0] : 'Other';
		$byCountry = ($region) ? $region : $currentRegion;
		foreach ($tzc->getPhpTimezones($byCountry, true) as $standardZone=>$values)
		{
			$selected = ($standardZone == $value) ? 'selected' : '';
			$offset = ($values['offset'] > 0) ? " (UTC +{$values['offset']})" : " (UTC {$values['offset']})";
			$displayZone = ($zoneRegion == 'Other') ? "Other/$standardZone" : $standardZone . $offset;
			$ddoptions .= '<option value="' . $standardZone . '" ' . $selected . '> ' . $displayZone . '</option>';
		}

		return $ddoptions;
	}
	
	/**
	 * Return a "Region" label
	 *
	 * @return string
	 */
	static public function renderRegionLabel()
	{
		return "<label for='region'>Select Region</label>&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	
	/**
	 * Return a "Location" label
	 *
	 * @return string
	 */
	static public function renderTimezoneLabel()
	{
		return "<label for='timezone'>Select Location</label>&nbsp;&nbsp;";
	}
}
?>