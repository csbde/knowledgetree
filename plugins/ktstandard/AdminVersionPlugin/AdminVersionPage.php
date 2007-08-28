<?php
/*
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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