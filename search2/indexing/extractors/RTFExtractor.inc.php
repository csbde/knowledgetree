<?php

require_once('OOTextExtractor.inc.php');

class RTFExtractor extends OOTextExtractor
{
	public function getDisplayName()
	{
		return _kt('RTF Extractor');
	}

	public function getSupportedMimeTypes()
	{
		return array(
       		'text/rtf'
		);
	}
}


?>