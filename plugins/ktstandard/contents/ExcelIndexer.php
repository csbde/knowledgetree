<?php


require_once(KT_DIR . '/plugins/ktstandard/contents/BaseIndexer.php');

class KTExcelIndexerTrigger extends KTBaseIndexerTrigger {

    var $mimetypes = array(
       'application/msword' => true,
       'application/vnd.ms-excel' => true,
    );
    var $command = 'xls2csv';          // could be any application.
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
