<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */


require_once(KT_DIR . '/plugins/ktstandard/contents/BaseIndexer.php');

class KTExcelIndexerTrigger extends KTBaseIndexerTrigger {

    var $mimetypes = array(
       'application/vnd.ms-excel' => true,
    );
    var $command = 'xls2csv';          // could be any application.
    var $commandconfig = 'indexer/xls2csv';          // could be any application.
    var $args = array("-q", "0", "-c", " ");
    var $use_pipes = true;
    
    // see BaseIndexer for how the extraction works.
    //
    function extract_contents($sFilename, $sTempFilename) {
        $res = parent::extract_contents($sFilename, $sTempFilename);
        if (!empty($res)) {
            return $res;
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
