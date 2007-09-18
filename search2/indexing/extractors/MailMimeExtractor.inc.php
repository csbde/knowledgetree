<?php

class MailMimeExtractor extends TextExtractor
{
	public function getDisplayName()
	{
		return _kt('Mail Mime Extractor');
	}

	public function getSupportedMimeTypes()
	{
		return array('text/msg');
	}

}

?>