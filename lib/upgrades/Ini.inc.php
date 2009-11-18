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

/*
 * TODO: Add more functionality when needed like delete and modify
 */

class Ini {

    var $cleanArray = array();
    var $iniFile = '';
    var $lineNum = 0;
    var $exists = '';

    function Ini($iniFile = '../../config.ini') {
       $this->iniFile = $iniFile;
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
    	if ($content === false)
    	{
    		return false;
    	}
    	$date = date('YmdHis');

    	$backupFile = $iniFile . '.' .$date;
        if (is_writeable($backupFile)) {
    	    file_put_contents($backupFile, $content);
        }
    }

    function read($iniFile) {

        $iniArray = file($iniFile);
        $section = '';
        foreach($iniArray as $iniLine) {
            $this->lineNum++;
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
                //fwrite ($fileHandle, $key.' = "'.$value."\"\r\n");
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
                foreach ($items as $key => $value) {
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

        $this->cleanArray[$addSection][$addItem] = stripcslashes($value);
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

}
/*
// USAGE EXAMPLE

if(file_exists('../../config.ini')) {

    $ini = new Ini();
    $ini->addItem('Section1', 'NewItem1', 'Some Text1', 'Item1 Comment', 'Section1 Comment');
    $ini->addItem('Section1', 'NewItem1.2', 'Some Text1.2', 'Item1.2 Comment');
    $ini->addItem('Section1', 'NewItem1.3', 'Some Text1.3', 'Item1.3 Comment');
    $ini->addItem('Section1', 'NewItem1.4', 'Some Text1.4', 'Item1.4 Comment');
    $ini->addItem('Section2', 'NewItem2', 'Some Text2', 'Item2 Comment');
    $ini->addItem('Section2', 'NewItem2.1', 'Some Text2.1');
    $ini->addItem('Section3', 'NewItem3', 'Some Text3', 'Item3 Comment', 'Section3 Comment');
    $ini->addItem('Section4', 'NewItem4', 'Some Text4', 'Item4 Comment');
    $ini->write();

}
*/
?>
