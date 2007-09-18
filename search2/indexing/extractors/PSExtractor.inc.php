<?php

class PSExtractor extends ApplicationExtractor
{
	public function __construct()
	{
		parent::__construct('extractors','pstotext','pstotext','PostScript Text Extractor','-nopgbrk -enc UTF-8 {source} {target}');
	}

	public function getSupportedMimeTypes()
	{
		return array('application/postscript');
	}
}

?>