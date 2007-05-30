<?php

/**
 * $Id$
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
            for ($i = 1; $i <= $aSheet['numRows']; $i++) {
                for ($j = 1; $j <= $aSheet['numCols']; $j++) {
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
