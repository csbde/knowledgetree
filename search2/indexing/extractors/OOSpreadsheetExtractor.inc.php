<?php

require_once('OOTextExtractor.inc.php');

class OOSpreadsheetExtractor extends OOTextExtractor
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