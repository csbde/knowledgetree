<?php
/**
 * $Id:$
 *
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

/*
 * TODO: Add more functionality when needed like delete and modify
 */

class Ini {

    var $cleanArray = array();
    var $iniFile = '';
    var $lineNum = 0;
    var $exists = '';

    function Ini($iniFile = '../../../config.ini') {
       $this->iniFile = $iniFile;
       $this->read($iniFile); 
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

        if($this->itemExists($addSection, $addItem)) return false;

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

}
/*
// USAGE EXAMPLE

if(file_exists('../../../config.ini')) {

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
