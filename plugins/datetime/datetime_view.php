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

class datetime_view extends KTDispatcher 
{
	/**
	 * Renders a list of standard timezone options to be used in a dropdown
	 *
	 * @return string
	 */
	static public function renderCountries($value)
	{
		$tzc = new TimezoneConversion();
		$ddoptions = '';
		$aValue = explode('/', $value);
		$currentCountry = isset($aValue[0]) ? $aValue[0] : 'Other';		
		foreach ($tzc->getPhpCountries() as $country)
		{
			$selected = ($country == $currentCountry) ? 'selected' : '';
			$ddoptions .= '<option onclick="javascript:{alert(\'a\');}" value="' . $country . '" ' . $selected . '> ' . $country . '</option>';
		}
		
		return $ddoptions;
	}
	
	/**
	 * Renders a list of standard timezone options to be used in a dropdown
	 *
	 * @return string
	 */
	static public function renderTimezones($value)
	{
		$tzc = new TimezoneConversion();
		$ddoptions = '';
		
		foreach ($tzc->getPhpTimezones() as $standardZone)
		{
			$selected = ($standardZone == $value) ? 'selected' : '';
			$ddoptions .= '<option value="' . $standardZone . '" ' . $selected . '> ' . $standardZone . '</option>';
		}
		
		return $ddoptions;
	}
	
	static public function renderCountryLabel()
	{
		return "<label for='country'>Select Region</label>&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	
	static public function renderTimezoneLabel()
	{
		return "<label for='timezone'>Select Location</label>&nbsp;&nbsp;";
	}
}