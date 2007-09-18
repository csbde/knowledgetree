<?php

require_once('OOPDFTextExtractor.inc.php');

class OOSpreadsheetExtractor extends OOPDFTextExtractor
{
	public function getDisplayName()
	{
		return _kt('OpenOffice Spreadsheet Extractor');
	}

	public function getSupportedMimeTypes()
	{
		return array(
       		'application/vnd.ms-excel',
       		'application/vnd.oasis.opendocument.spreadsheet',
       		'application/vnd.oasis.opendocument.spreadsheet-template',
       		'application/vnd.sun.xml.calc',
       		'application/vnd.sun.xml.calc.template'
		);
	}
}


?>