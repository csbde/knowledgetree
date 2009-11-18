<?php
/*
 * $Id: loadFeed.inc.php 7481 2007-10-23 09:47:11Z kevin_fourie $
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
 *
 */
 require_once('../../config/dmsDefaults.php');
 require_once(KT_DIR. '/plugins/rssplugin/rss2array.inc.php');
 require_once(KT_DIR. '/plugins/rssplugin/KTrss.inc.php');
 
 $feed = $_GET["feed"];
 $user = $_GET["user"];
 session_start();
 
 $sCache = _checkCache($feed);
 
 if(!is_null($sCache)) {
 	$aRSSArray =  $sCache;
 }else{
 	$aRSSArray = rss2array($feed);
	$_SESSION['kt_dedicated_rss'][$feed]['lastcheck'] = time();
    $_SESSION['kt_dedicated_rss'][$feed] = $aRSSArray;
 }
 // Prepare response data to be passed back to page
 $reposonse = "<h3>".$aRSSArray[channel][title]."</h3>" .
 		"<div class='outerContainer' id='outerContainer'>" .
 		"<table width='90%'>";
		for($i=0;$i<count($aRSSArray[items]);$i++){
			 $reposonse .= "<tr>
				<td colspan='2'><strong><a href='".$aRSSArray[items][$i][link]."' target='_blank'>".$aRSSArray[items][$i][title]."</a><strong></td>
			</tr>
			<tr>
				<td>".$aRSSArray[items][$i][description]."</td>
			</tr>
			<tr><td colspan='2'><br></td></tr>";
		}
 $reposonse .= "</table></div><br>";
	 
 echo  $reposonse;
 
 function _checkCache($feed) {
	session_start();
    $iLastCheck = $_SESSION['kt_dedicated_rss'][$feed]['lastcheck'];

    if (empty($iLastCheck)) {
        return;
    }
    $sStoredFeed = $_SESSION['kt_dedicated_rss'][$feed];
    if (empty($sStoredFeed)) {
        $now = time();
        $diff = $now - $iLastCheck;
        if ($diff > (5*60)) {
            return;
        }
    }
    $now = time();
    $diff = $now - $iLastCheck;
    if ($diff > (5*60)) {
        return;
    }
    return $sStoredFeed;
}
?>
