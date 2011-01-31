<?php
/**
 * $Id$
 *
 * Class to deal with Timezone Conversion
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

  /**
   * Usage Example ::
   *
   ***************** Simple Example ********************
   * Convert local datetime to IST (Indian Standard Time) 
   * require_once('class-timezone-conversion.php');   //Include class                   //
   * $tz = new TimezoneConversion();                  //Create TimezoneConversion Object//
   * $tz->setProperty('DateTime', 'now');             //Set local 'DateTime' to convert //
   * $tz->setProperty('Timezone', 'IST');             //Get IST                         //
   * print "\n IST ::" . $tz->convertDateTime();
   *
   *
   *
   ***************** Advanced Example *******************
   * Convert local 'DateTime' to ISt (Indian Standard Time) and then convert IST to PDT (Pacific Daylight Time)
   * require_once('class-timezone-conversion.php');   // Include class                  //
   * $tz = new TimezoneConversion();                  //Create TimezoneConversion Object//
   * $tz->setProperty('DateTime', 'now');             //Set local 'DateTime' to convert //
   * $tz->setProperty('Timezone', 'IST');             // Get IST                        //
   * $ist_datetime = $tz->convertDateTime(); 
   * print "\n IST ::" . $ist_datetime;
   * ** IST => PDT **
   * $tz->setProperty('DateTime', $ist_datetime);     //Set IST to convert to PDT       //
   * $tz->setProperty('Timezone', 'PDT');             // Get PDT                        //
   * print "\n PDT ::" . $tz->convertDateTime();                      
   *
   *
   *
   **/
class TimezoneConversion 
{
	/** TimeZone 'default' Property */
	private $_timezone = array (
	                          'Datetime'      => 'now',
	                          'Return_Format' => 'Y-m-d H:i:s',
	                          'Timezone'      => 'GMT',
	                          );
  
	/**
	 * Constructor
	 *
	 */
	public function __construct($returnFormat = 'Y-m-d H:i:s') 
	{
		$this->_timezone['Return_Format'] = $returnFormat;
	}
  
	/**
	 * Function to 'SET' property
	 *
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function setProperty($key ='', $value ='') 
	{
		return ( array_key_exists($key, $this->_timezone) ? ($this->_timezone[$key] = $value) : false );
	}

	/**
	 * Function to 'GET' property
	 *
	 * @param string $key
	 * @return string
	 */
	public function getProperty($key = '') 
	{
		return ( array_key_exists($key, $this->_timezone) ? $this->_timezone[$key] : false );
	}

	/**
	 * Function to localtime
	 *
	 * @return unknown
	 */
	public function getLocalDateTime() 
	{
		return date($this->_timezone['Return_Format']);
	}

	/**
	 * Function to convert
	 *
	 * @return string date
	 */
	public function convertDateTime($toTimezone = true) 
	{
		if($toTimezone)
			return $this->convertToTimezone($this->_timezone['Datetime'], $this->getTimeZoneOffset($this->_timezone['Timezone']), $this->_timezone['Return_Format']);
		else
			return $this->convertFromTimezone($this->_timezone['Datetime'], $this->getTimeZoneOffset($this->_timezone['Timezone']), $this->_timezone['Return_Format']);
	}

  
	/**
	 * Check if timezone set is in list of timezones
	 *
	 * @param string $timezone
	 * @return string
	 */
	public function getSelectedZone($timezone)
	{
		foreach ($this->getPopularZones() as $tz)
		{
			if($tz['timezone'] == $timezone)
			{
				return $timezone;
			}
		}
		
		return "UTC";
	}
	
	/**
	 * Timezone List
	 * Array compiled from timezone_identifiers_list() that can be used on
	 * systems that do not support the function but a list of timezones is
	 * needed anyway
	 **/
	public function getPhpRegions()
	{
		return array(
						'Africa',
						'America',
						'Antarctica',
						'Arctic',
						'Asia',
						'Atlantic',
						'Australia',
						'Europe',
						'Indian',
						'Pacific',
						'Other',
		);
	}
	
	/**
	 * Timezone List
	 * Array compiled from timezone_identifiers_list() that can be used on
	 * systems that do not support the function but a list of timezones is
	 * needed anyway
	 **/
	public function getPhpTimezones($currentCountry = false)
	{
		$list_of_zones = array();
		$timezones = DateTimeZone::listAbbreviations();
		foreach ($timezones as $region => $timezone_info)
		{
			foreach ($timezone_info as $azone)
			{
				$timezone = $azone['timezone_id'];
				if($timezone != '')
				{
					if(!in_array($timezone, $list_of_zones))
					{
						if($currentCountry === false)
						{
							$list_of_zones[] = $timezone;
						}
						else
						{
							if($country == $currentCountry)
							{
								$list_of_zones[] = $timezone;
							}
						}
					}
				}
			}
		}
		array_multisort($list_of_zones);
		
		return $list_of_zones;
	}
	
	/**
	 * Function to convert datetime to another timezone
	 *
	 * @param string $datetime_str
	 * @param int $timezone_offset
	 * @param string $return_format
	 * @return string
	*/
  	private function convertToTimezone($datetime_str = 'now', $timezone_offset = 0, $return_format = 'Y-m-d H:i:s') 
  	{
    	return gmdate($return_format, ( $this->convertDateTimeToGMT($datetime_str) + ($timezone_offset*3600) ) );
	}
	
	/**
	 * Function to convert datetime to UTC
	 *
	 * @param string $datetime_str
	 * @param int $timezone_offset
	 * @param string $return_format
	 * @return string
	*/
  	private function convertFromTimezone($datetime_str = 'now', $timezone_offset = 0, $return_format = 'Y-m-d H:i:s') 
  	{
    	return gmdate($return_format, ( $this->convertDateTimeToGMT($datetime_str) - ($timezone_offset*3600) ) );
	}
  
	/**
	 * Function to convert 'datetime_str into GMT timestamp
	 *
	 * @param string $datetime_str
	 * @param string $return_format
	 * @return string
	 */
	private function convertDateTimeToGMT($datetime_str = 'now', $return_format = 'U') 
	{
	
		return gmdate($return_format, strtotime($datetime_str));
	}


  	/**
  	 * Function to get 'Timezone Offset' from Timezone name
  	 *
  	 * @param string $timezone
  	 * @return unknown
  	 */
  	private function getTimeZoneOffset($timezone = 'GMT') 
  	{
	    /** Find Timezone Offset */
	    foreach ($this->getPhpTimezones() as $timezone_name) 
	    {
			if ($timezone_name == $timezone)
			{
				// Create two timezone objects, one for UTC and one for the other user defined
				// Create two DateTime objects that will contain the same Unix timestamp, but
				// have different timezones attached to them.
				// Then get the offset using the users timezone and passing the utc timezone object
				$utcDateTimeZone = new DateTimeZone('UTC');
				try {
					$userDateTimeZone = new DateTimeZone($timezone);
				} catch (Exception $e)
				{
					return 0;
				}
				$utcDateTime = new DateTime("now", $utcDateTimeZone);
				$userDateTime = new DateTime("now", $userDateTimeZone);
				$timeOffset = $userDateTimeZone->getOffset($utcDateTime);
				if($timeOffset != 0)
				{
					$offset = ($timeOffset/3600);
					return $offset;
				}
				else
				{
					return 0;
				}
			}
	    }

	    // TODO : Log unknown timezone.
    	return 0;
	}
}
?>