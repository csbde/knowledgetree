<?php

class PlainTextExtractor extends TextExtractor
{
	public function getDisplayName()
	{
		return _kt('Plain Text Extractor');
	}

	public function getSupportedMimeTypes()
	{
		return array('text/plain','text/csv');
	}

}

?>