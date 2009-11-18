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


require_once(KT_DIR . '/plugins/ktstandard/contents/BaseIndexer.php');

class KTExcelIndexerTrigger extends KTBaseIndexerTrigger {

    var $mimetypes = array(
       'application/vnd.ms-excel' => true,
    );
    var $command = 'xls2csv';          // could be any application.
    var $commandconfig = 'indexer/xls2csv';          // could be any application.
    var $args = array("-d", "UTF-8", "-q", "0", "-c", " ");
    var $use_pipes = true;
    
    // see BaseIndexer for how the extraction works.
    //
    function extract_contents($sFilename, $sTempFilename) {
    	if (!OS_WINDOWS) {
    		putenv('LANG=en_US.UTF-8');
    		$res = parent::extract_contents($sFilename, $sTempFilename);
    		if(strstr($this->aCommandOutput[0], "encrypted")) {
    			return "";
    		}
    		if (!empty($res)) {
    			return $res;
    		}
    	}

    	return $this->_fallbackExcelReader($sFilename, $sTempFilename);
    }

    function _fallbackExcelReader($sFilename, $sTempFilename) {
        require_once(KT_DIR . '/thirdparty/excelreader/Excel/reader.php');
        $reader = new Spreadsheet_Excel_Reader();
        $reader->setOutputEncoding('UTF-8');
        $reader->read($sFilename);
        
        $t = fopen($sTempFilename, "w");
        foreach ($reader->sheets as $aSheet) {
        	for ($i = 1; $i <= $aSheet['numRows'] && $i <= 1000; $i++) {
        		for ($j = 1; $j <= $aSheet['numCols'] && $j <= 1000; $j++) {
        			fwrite($t, $aSheet['cells'][$i][$j] . " ");
        		}
        		fwrite($t, "\n");
        	}
        	fwrite($t, "\n\n\n");
        }
        fclose($t);
        return file_get_contents($sTempFilename);
    }
}

?>
