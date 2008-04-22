<?php
/*
 * $Id:$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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


require_once(KT_LIB_DIR . "/plugins/plugin.inc.php");
require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php"); 
require_once(KT_LIB_DIR . "/dashboard/dashlet.inc.php");

define('KT_VERSION_URL', 'http://version.knowledgetree.com/kt_versions');

class AdminVersionPage extends KTStandardDispatcher {
    
    function _checkCache() {
        global $default;
        $iLastCheck = KTUtil::getSystemSetting('ktadminversion_lastcheck');
        if (empty($iLastCheck)) {
            return;
        }
        $sLastValue = KTUtil::getSystemSetting('ktadminversion_lastvalue');
        if (empty($sLastValue)) {
            $now = time();
            $diff = $now - $iLastCheck;
            if ($diff > (24*60*60)) {
                return;
            }
        }
        $now = time();
        $diff = $now - $iLastCheck;
        if ($diff > (24*60*60)) {
            return;
        }
        return $sLastValue;
    }

    function do_main() {
       session_write_close();

       $sCache = $this->_checkCache();
       if (!is_null($sCache)) {
	        $sCachedVersion =  $sCache;
	        
	        $sVName = "";
	        $sVNum = "";
	        
	       	$sTrimmer = str_replace('{', '', str_replace('}', '', str_replace('\'', '', $sCachedVersion)));
	        $aCachedVersionsTemp = explode(',',$sTrimmer);
	        
	        for($i=0;$i<count($aCachedVersionsTemp);$i++){
	        	$aCachedVersionsTemp[$i] = explode(':', $aCachedVersionsTemp[$i]);
	        }
	        for($i=0;$i<count($aCachedVersionsTemp);$i++){
	        	$key = trim($aCachedVersionsTemp[$i][0]);
	        	$value = trim($aCachedVersionsTemp[$i][1]);
	        	$aCachedVersions[$key] = $value;
	        }
	        
	        $aVersions = KTUtil::getKTVersions();
	        
/*        
	        echo "<pre>";
        	print_r($aCachedVersions);
        	echo "</pre>";
        	echo "<pre>";
        	print_r($aVersions);
        	echo "</pre>";
        	exit;
*/     

	        foreach ($aVersions as $k => $v) {
	        	foreach($aCachedVersions as $j => $l) {
	        		if (($k == $j) && (version_compare($aVersions[$k], $aCachedVersions[$j]) == -1))
	        		{
	        			//save new name and version
	        			$sVName = $j;
	        			$sVNum = $l;
	        		}
	        	}//end foreach
        	
       		}//end foreach
       		
       		if ($sVName != "")
        	{
        		return "<div id=\"AdminVersion\"><a href=\"http://www.knowledgetree.com/products/whats-new\" target=\"_blank\">".$sVName." version ".$sVNum."</a></div><br>";
        	}
        	else
        	{
        		return "";
        	}
        }
		
        $sUrl = KT_VERSION_URL;
        $aVersions = KTUtil::getKTVersions();

        foreach ($aVersions as $k => $v) {
            $sUrl .=  '?' . sprintf("%s=%s", $k, $v);
        }
        $sIdentifier = KTUtil::getSystemIdentifier();
        $sUrl .= '&' . sprintf("system_identifier=%s", $sIdentifier);

        if (!function_exists('curl_init')) {
            if (OS_WINDOWS) {
                return "";
            }
            
            $stuff = @file_get_contents($sUrl);
            if ($stuff === false) {
                $stuff = "";
            }
        } else {
            
            $ch = @curl_init($sUrl);
            if (!$ch) {
                return "";
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $stuff = curl_exec($ch);
            curl_close($ch);
            if (!$stuff) {
                $stuff = "";
            }
        }
        KTUtil::setSystemSetting('ktadminversion_lastcheck', time());
        KTUtil::setSystemSetting('ktadminversion_lastvalue', (string)$stuff);
                        
        $sVName = "";
	    $sVNum = "";

        $trim_stuff = str_replace('{', '', str_replace('}', '', str_replace('\'', '', $stuff)));
        $aRemoteVersionstemp = explode(',',$trim_stuff);
        
        for($i=0;$i<count($aRemoteVersionstemp);$i++){
        	$aRemoteVersionstemp[$i] = explode(':', $aRemoteVersionstemp[$i]);
        }
        for($i=0;$i<count($aRemoteVersionstemp);$i++){
        	$key = trim($aRemoteVersionstemp[$i][0]);
        	$value = trim($aRemoteVersionstemp[$i][1]);
        	$aRemoteVersions[$key] = $value;
        }
/*
        echo "<pre>";
        	print_r($aRemoteVersions);
        echo "</pre>";
        echo "<pre>";
        	print_r($aVersions);
        echo "</pre>";
        exit;
*/        
        foreach ($aVersions as $k => $v) {
        	foreach($aRemoteVersions as $j => $l) {
        		if (($k == $j) && (version_compare($aVersions[$k], $aRemoteVersions[$j]) == -1))
        		{
        			//save new name and version
        			$sVName = $j;
	        		$sVNum = $l;
        		}
          	}
        }
        
        if ($sVName != "")
        	{
        		return "<div id=\"AdminVersion\"><a href=\"http://www.knowledgetree.com/products/whats-new\" target=\"_blank\">".$sVName." version ".$sVNum."</a></div><br>";
        	}
        	else
        	{
        		return "";
        	}
    }

    function handleOutput($sOutput) {
        print $sOutput;
    }
}

?>