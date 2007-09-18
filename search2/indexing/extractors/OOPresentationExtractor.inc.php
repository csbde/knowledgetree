<?php

require_once('OOPDFTextExtractor.inc.php');

class OOPresentationExtractor extends OOPDFTextExtractor
{
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

?>