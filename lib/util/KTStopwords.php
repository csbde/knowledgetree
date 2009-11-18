<?php

/**
 * $Id$
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

    static function &getSingleton() {
    	static $singleton = null;
    	if (is_null($singleton))
    	{
    		$singleton = new KTStopwords;
    		$oConfig =& KTConfig::getSingleton();
	    	$singleton->loadFile($oConfig->get('urls/stopwordsFile'));
    	}

        return $singleton;
    }
}


?>
