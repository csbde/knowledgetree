<?php

class XMLExtractor extends TextExtractor
{
	public function getDisplayName()
	{
		return _kt('XML Text Extractor');
	}

	public function getSupportedMimeTypes()
	{
		return array('text/xml','application/xml','text/html');
	}

	protected function filter($text)
	{
		 return preg_replace ("@(</?[^>]*>)+@", " ", $text);
	}
}

?>