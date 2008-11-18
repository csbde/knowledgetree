<?php

class WordExtractor extends OOFallbackDocumentExtractor
{
	public function __construct()
	{
		parent::__construct('catdoc', '{$escape}{$cmd}{$escape} -w -d UTF-8 {$escape}{$sourcefile}{$escape} > {$escape}{$targetfile}{$escape}');
	}

	public function getDisplayName()
	{
		return _kt('Word Extractor');
	}

	public function getSupportedMimeTypes()
	{
	    return array();
		return array(
			'application/msword',
			'text/rtf'
		);
	}
}

?>