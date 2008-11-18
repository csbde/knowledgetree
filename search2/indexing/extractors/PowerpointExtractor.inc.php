<?php

class PowerpointExtractor extends OOFallbackDocumentExtractor
{
	public function __construct()
	{
		parent::__construct('catppt', '{$escape}{$cmd}{$escape} -d UTF-8 {$escape}{$sourcefile}{$escape} > {$escape}{$targetfile}{$escape}');
	}

	public function getDisplayName()
	{
		return _kt('Office Powerpoint Text Extractor');
	}

	public function getSupportedMimeTypes()
	{
	    return array();
		return array(
			'application/vnd.ms-powerpoint'
		);
	}
}

?>