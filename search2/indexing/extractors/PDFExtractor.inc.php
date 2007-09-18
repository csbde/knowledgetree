<?php

class PDFExtractor extends ApplicationExtractor
{
	public function __construct()
	{
		parent::__construct('extractors','pdftotext','pdftotext','PDF Text Extractor','-nopgbrk -enc UTF-8 {source} {target}');
	}

	public function getSupportedMimeTypes()
	{
		return array('application/pdf');
	}
}

?>