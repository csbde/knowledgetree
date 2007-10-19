<?php

class PSExtractor extends ApplicationExtractor
{
	public function __construct()
	{
		parent::__construct('externalBinary','pstotext','pstotext',_kt('PostScript Text Extractor'),'-nopgbrk -enc UTF-8 {source} {target}');
	}

	public function getSupportedMimeTypes()
	{
		if (OS_WINDOWS)
		{
			return array();
		}
		return array('application/postscript');
	}

	public function diagnose()
	{
		if (OS_WINDOWS)
		{
			return null;
		}
		return parent::diagnose();
	}

}

?>