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
require_once('timezones.inc');

class datetimeutil
{
	/**
	 * Constructor
	 *
	 */
	public function __construct() {}
	
	/**
	 * Returns an offset date formated for display or queries.
	 *
	 * @param string $date - date value
	 * @param string $toTimezone - convert to or from
	 * @return string $date - offset date
	 */
	static public function getLocaleDate($date, $toTimezone = true)
	{
		// Create time conversion object
		$tzc = new TimezoneConversion();
		// Set the date to convert
		$tzc->setProperty('Datetime', $date);
		// Retrieve system timezone
		$oConfig = KTConfig::getSingleton();
		$tzvalue = $oConfig->get('timezone/setTimezone', 'UTC');
		// Check if it is UTC and return
		if($tzvalue == 'UTC') { return $date; }
		// Set the timezone
		$tzc->setProperty('Timezone', $tzvalue);
		// Convert timezone
		return $tzc->convertDateTime($toTimezone);
	}
	
	/**
	 * Convert time to UTC
	 *
	 * @param unknown_type $date
	 * @return unknown
	 */
	static public function convertToUTC($date)
	{
		// Create time conversion object
		$tzc = new TimezoneConversion('Y-m-d H:i:s');
		// Set the date to convert
		$tzc->setProperty('Datetime', $date);
		// set zone to UTC
		$tzc->setProperty('Timezone', 'UTC');
		// Convert timezone
		return $tzc->convertDateTime();
	}

	/**
	 * Return timezone
	 *
	 */
	static public function getTimeZone()
	{
		// Retrieve system timezone
		$oConfig = KTConfig::getSingleton();
		return $oConfig->get('timezone/setTimezone', 'UTC');
	}
}
?>