<?php
require('timezones.inc');
$time = '2010-12-03 00:00:00';
echo 'Defined time ';
echo $time . '.';
echo "\n";
$timeZoneConverter = new TimezoneConversion();
$timeZoneConverter->setProperty('Datetime', $time);
$timezones = $timeZoneConverter->getPopularZones();
$utcDateTimeZone = new DateTimeZone('UTC');
$utcDateTime = new DateTime("now", $utcDateTimeZone);
foreach ($timezones as $region=>$atz)
{
	if(isset($atz['daylight'])) { echo '( Daylight ) '; }
	echo $timeZoneConverter->setProperty('Timezone', $atz['timezone']) . ' ' . $atz['offset'];
	echo " ";
	echo $timeZoneConverter->convertDateTime();
	echo "\n";
}

?>