<?php

require_once('OOTextExtractor.inc.php');
require_once('PDFExtractor.inc.php');

class OOPresentationExtractor extends CompositeExtractor
{
	public function __construct()
	{
		$sourceExtractor = new OOPresentationToPDF();
		$targetExtractor = new PDFExtractor();
		parent::__construct($sourceExtractor, 'pdf', 'application/pdf', $targetExtractor, true);
	}

	public function getDisplayName()
	{
		return _kt('OpenOffice Presentation Extractor');
	}

	public function getSupportedMimeTypes()
	{
		return array(
       		'application/vnd.oasis.opendocument.presentation',
       		'application/vnd.oasis.opendocument.presentation-template',
		);
	}
}

class OOPresentationToPDF extends OOTextExtractor
{
	public function __construct()
	{
		parent::__construct('pdf');
		$this->documentConverter = KT_DIR . '/bin/openoffice/pdfgen.py';
		if (!is_file($this->documentConverter))
		{
			$this->documentConverter = false;
		}
	}
}


?>