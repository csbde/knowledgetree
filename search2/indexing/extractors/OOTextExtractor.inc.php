<?php

class OOTextExtractor extends ExternalDocumentExtractor
{
	protected $python;
	protected $documentConverter;
	protected $ooHost;
	protected $ooPort;
	protected $targetExtension;

	public function __construct($targetExtension='html')
	{
		parent::__construct();
		$this->targetExtension = $targetExtension;
		$config =& KTConfig::getSingleton();

		$this->python = KTUtil::findCommand('externalBinary/python');
		$this->ooHost = $config->get('openoffice/host');
		$this->ooPort = $config->get('openoffice/port');

		$this->documentConverter = KT_DIR . '/bin/openoffice/DocumentConverter.py';
		if (!is_file($this->documentConverter))
		{
			$this->documentConverter = false;
		}
	}

	public function getDisplayName()
	{
		return _kt('OpenOffice Text Extractor');
	}

	public function getSupportedMimeTypes()
	{
		return array(

		);
	}

	public function needsIntermediateSourceFile()
	{
		// we need the intermediate file because it
		// has the correct extension. documentConverter uses the extension to determine mimetype
		return true;
	}

	protected function getCommandLine()
	{
		$sourcefile = escapeshellcmd($this->sourcefile);
		unlink($this->targetfile);
		$this->targetfile .= '.' . $this->targetExtension;
		$targetfile = escapeshellcmd($this->targetfile);

		$escape = OS_WINDOWS?'"':'\'';

		$cmdline = "{$this->python} {$escape}{$this->documentConverter}{$escape} {$escape}{$sourcefile}{$escape} {$escape}{$targetfile}{$escape} {$this->ooHost} {$this->ooPort}";
		return $cmdline;
	}

	protected function filter($text)
	{
		 $text = preg_replace ("@(</?[^>]*>)+@", '', $text);

		 do
		 {
			 $old = $text;

			 $text= preg_replace("@([\r\n])[\s]+@",'\1', $text);

			 $text = preg_replace('@\ \ @',' ', $text);
			 $text = preg_replace("@\n\n@","\n", $text);
		 }
		 while ($old != $text);

		 return $text;
	}

	public function extractTextContent()
	{
		if (false === parent::extractTextContent())
		{
			return false;
		}

		if ($this->targetExtension != 'html')
		{
			return true;
		}
		$content = file_get_contents($this->targetfile);
		return file_put_contents($this->targetfile, $this->filter($content));

	}


	public function diagnose()
	{
		if (false === $this->python)
		{
			return _kt('Cannot locate python');
		}

		if (false === $this->documentConverter)
		{
			return _kt('Cannot locate DocumentConverter.py');
		}

		return SearchHelper::checkOpenOfficeAvailablity();
	}
}

?>