<?php

/**
 * $Id: config.inc.php 5758 2006-07-27 10:17:43Z bshuttle $
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

class KTStopwords {
    var $words = array();

    var $conf = array();
    var $aSectionFile;
    var $aFileRoot;
    var $flat = array();
    var $flatns = array();

    function loadCache($filename) {
        $cache_str = file_get_contents($filename);
	$this->words = unserialize($cache_str);
        return true;
    }
    
    function createCache($filename) {
        file_put_contents($filename, serialize($this->words));
    }

    function loadFile($filename) {
	$this->words = array();
	foreach(file($filename) as $line) {
	    $this->words[] = trim($line);
	}
    }

    function isStopword($sWord) {
	return in_array($sWord, $this->words);
    }

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'KTStopwords')) {
            $GLOBALS['KTStopwords'] =& new KTStopwords;
	    $oConfig =& KTConfig::getSingleton();
	    $GLOBALS['KTStopwords']->loadFile($oConfig->get('urls/stopwordsFile'));
        }
        return $GLOBALS['KTStopwords'];
    }
}


?>
