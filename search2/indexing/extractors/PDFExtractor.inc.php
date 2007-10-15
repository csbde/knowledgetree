<?php

class PDFExtractor extends ApplicationExtractor
{
	public function __construct()
	{
		parent::__construct('externalBinary','pdftotext','pdftotext',_kt('PDF Text Extractor'),'-nopgbrk -enc UTF-8 {source} {target}');
	}

	public function getSupportedMimeTypes()
	{
		return array('application/pdf');
	}
}

?>