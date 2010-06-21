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

/**
 * Returns the current date time
 *
 * @return string the current date time (Y-m-d H:i:s)
 */
function getCurrentDateTime() {
    return date("Y-m-d H:i:s", time());
}

/**
 * Returns the specified date time, formatted as Y-m-d H:i:s
 *
 * @param int the date time to format
 * @return string the formatted date time
 */
function formatDateTime($dateTime) {
    return date("Y-m-d H:i:s", $dateTime);
}


/**
* Method to find the difference between two dates
*
* @param string $date1 First Date
* @param string $date1 Second Date
* @return array Array containing difference in terms of days, hours, minutes and seconds
*/
function dateDifference($date1, $date2) {
    $s = strtotime($date2)-strtotime($date1);
    $d = intval($s/86400);
    $s -= $d*86400;
    $h = intval($s/3600);
    $s -= $h*3600;
    $m = intval($s/60);
    $s -= $m*60;
    return array("d"=>$d,"h"=>$h,"m"=>$m,"s"=>$s);
}

/**
* Method to get the difference between two dates translated same as above but the result is without the html attached
* @param string $date1 First Date
* @return string Difference translated and expressed in a human readable format
*/
function getDateTimeDifference($date1)
{
    // Set 2nd Date to current date and time
    $date2 = date('Y-m-d H:i:s');
    
    // Swap around if first date is bigger than second date
    if ($date1 > $date2) {
        $temp = $date2;
        $date2 = $date1;
        $date1 = $temp;
    }
    
    // Get the difference as an array
    $difference = dateDifference($date1, $date2);
    
    // If more than one days difference
    if ($difference['d'] > 1) {
        return $difference['d'].' '._kt('days ago');
    } else if ($difference['d'] > 0){ // If previous day
        return 24+$difference['h'].' '._kt('hours ago');
    } else { // If today
        if ($difference['h'] > 1) { // If more than one hour
            $returnString = $difference['h'].' '._kt('hours').', ';
        } else if ($difference['h'] > 0) {
            $returnString = $difference['h'].' '._kt('hour').', ';
        } else {
            $returnString = '';
        }
        
        if ($difference['m'] > 1) { // if more than one minute
            $returnString .= $difference['m'].' '._kt('minutes').' ';
        } else if ($difference['m'] > 0) {
            $returnString = $difference['h'].' '._kt('minute').' ';
        }        
        
        if ($difference['h'] == 0 && $difference['m'] == 0) {
            return $difference['s'].' '._kt('seconds ago');
            return $difference['s'].' '._kt('seconds ago');
        } else {
           return $returnString.' '._kt('ago');
        }
        
        return $returnString;
    }
}


?>
