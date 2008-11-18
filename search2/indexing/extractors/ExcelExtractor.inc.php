<?php

class ExcelExtractor extends OOFallbackDocumentExtractor
{
	public function __construct()
	{
		parent::__construct('xls2csv', '{$escape}{$cmd}{$escape} -d UTF-8 -q 0 -c {$escape} {$escape} {$escape}{$sourcefile}{$escape} > {$escape}{$targetfile}{$escape}');
	}

	public function getDisplayName()
	{
		return _kt('Excel Extractor');
	}

	public function getSupportedMimeTypes()
	{
	    return array();
		return array(
			'application/vnd.ms-excel'
		);
	}
}

?>