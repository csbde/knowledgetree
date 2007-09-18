<?php

class ScriptExtractor extends TextExtractor
{
	public function getDisplayName()
	{
		return _kt('Script Extractor');
	}

	public function getSupportedMimeTypes()
	{
		return array('application/x-shellscript','application/javascript');
	}

}

?>