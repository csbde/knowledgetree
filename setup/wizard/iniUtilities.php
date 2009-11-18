<?php
/**
 * $Id:$
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

class iniUtilities {

    private $cleanArray = array();
    private $iniFile = '';
    private $lineNum = 0;
    private $exists = '';

   	function load($iniFile) {
       $this->cleanArray = array();
       $this->iniFile = $iniFile;
       $this->lineNum = 0;
       $this->exists = '';
       $this->backupIni($iniFile);
       $this->read($iniFile);
    }
    
    /**
     * Create a backup with the date as an extension in the same location as the original config.ini
     *
     * @param string $iniFile
     * @return boolean
     */
    function backupIni($iniFile)
    {
    	$content = file_get_contents($iniFile);
    	if (!$content === false)
    	{
	    	$date = date('YmdHis');
	
	    	$backupFile = $iniFile . '.' .$date;
	        if (is_writeable($backupFile)) {
	    	    file_put_contents($backupFile, $content);
	        }    		
    	} 
    	return false;
    }

    function read($iniFile) {
        $iniArray = file($iniFile);
        $section = '';
        foreach($iniArray as $iniLine) {
            ++$this->lineNum;
            $iniLine = trim($iniLine);
            $firstChar = substr($iniLine, 0, 1);
            if($firstChar == ';') {
                if($section == ''){
                    $this->cleanArray['_comment_'.$this->lineNum]=$iniLine;
                }else {
                    $this->cleanArray[$section]['_comment_'.$this->lineNum]=$iniLine;
                }
                continue;
            }
            if($iniLine == '') {
                if($section == ''){
                    $this->cleanArray['_blankline_'.$this->lineNum]='';
                }else {
                    $this->cleanArray[$section]['_blankline_'.$this->lineNum]='';
                }
                continue;
            }

            if ($firstChar == '[' && substr($iniLine, -1, 1) == ']') {
                $section = substr($iniLine, 1, -1);
                $this->sections[] = $section;
            } else {
                $equalsPos = strpos($iniLine, '=');
                if ($equalsPos > 0 && $equalsPos != sizeof($iniLine)) {
                    $key = trim(substr($iniLine, 0, $equalsPos));
                    $value = trim(substr($iniLine, $equalsPos+1));
                    if (substr($value, 1, 1) == '"' && substr( $value, -1, 1) == '"') {
                        $value = substr($value, 1, -1);
                    }
                    $this->cleanArray[$section][$key] = stripcslashes($value);
                } else {
                    $this->cleanArray[$section][trim($iniLine)]='';
                }
            }
        }

        return $this->cleanArray;
    }

    function write($iniFile = "") {
        if(empty($iniFile)) {
            $iniFile = $this->iniFile;
        }
        if (!is_writeable($iniFile)) {
            return;
        }
        $fileHandle = fopen($iniFile, 'wb');
        foreach ($this->cleanArray as $section => $items) {
            if (substr($section, 0, strlen('_blankline_')) === '_blankline_' ) {
                fwrite ($fileHandle, "\r\n");
                continue;
            }
            if (substr($section, 0, strlen('_comment_')) === '_comment_' ) {
                fwrite ($fileHandle, "$items\r\n");
                continue;
            }
            fwrite ($fileHandle, "[".$section."]\r\n");
            foreach ($items as $key => $value) {
                if (substr($key, 0, strlen('_blankline_')) === '_blankline_' ) {
                    fwrite ($fileHandle, "\r\n");
                    continue;
                }
                if (substr($key, 0, strlen('_comment_')) === '_comment_' ) {
                    fwrite ($fileHandle, "$value\r\n");
                    continue;
                }

                $value = addcslashes($value,'');
                fwrite ($fileHandle, $key.' = '.$value."\r\n");
            }
        }
        fclose($fileHandle);
    }

    function itemExists($checkSection, $checkItem) {
        $this->exists = '';
        foreach($this->cleanArray as $section => $items) {
            if($section == $checkSection) {
                $this->exists = 'section';
                $items = array_flip($items);
                foreach ($items as $key) {
                    if($key == $checkItem) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    function addItem($addSection, $addItem, $value, $itemComment = '', $sectionComment = '') {
        if($this->itemExists($addSection, $addItem)) {
            $this->delItem($addSection, $addItem);
        }

        if($this->exists != 'section') {
            $this->cleanArray['_blankline_'.$this->lineNum++]='';
            if(!empty($sectionComment)) $this->cleanArray['_comment_'.$this->lineNum++] = '; '.$sectionComment;
        }
        if(!empty($itemComment)) {
            $this->cleanArray[$addSection]['_comment_'.$this->lineNum++] = '; '.$itemComment;
        }
        $this->cleanArray[$addSection][$addItem] = stripcslashes($value);
        return true;
    }

    function updateItem($addSection, $addItem, $value) {
		if(WINDOWS_OS) {
        	$this->cleanArray[$addSection][$addItem] = $value;
		} else {
        	$this->cleanArray[$addSection][$addItem] = stripcslashes($value);
		}
        return true;
    }

    function delItem($delSection, $delItem) {

        if(!$this->itemExists($delSection, $delItem)) return false;

        unset($this->cleanArray[$delSection][$delItem]);
        return true;
    }

    function delSection($delSection) {

        unset($this->cleanArray[$delSection]);
        return true;
    }
    
    // Return file line by line
    public function getFileByLine() {
        $data = $this->read($this->iniFile);
        return $data[''];
    }
    
    public function getSection($section) {
    	if (isset($this->cleanArray[$section])) {
    		return $this->cleanArray[$section];
    	}
    	
    	return false;
    }
}
?>