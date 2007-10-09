<?php

class OOTextExtractor extends ExternalDocumentExtractor
{
	private $converter;
	private $javaPath;
	private $ooHost;
	private $ooPort;
	private $targetMimeType;

	public function __construct($targetMimeType='plain/text')
	{
		parent::__construct();
		$config =& KTConfig::getSingleton();

		$this->converter = KTUtil::findCommand('extractors/jodconverter', 'jodconverter');
		$this->javaPath = KTUtil::findCommand('extractors/java', 'java');
		$this->ooHost = $config->get('openoffice/host', 'localhost');
		$this->ooPort = $config->get('openoffice/port', 8100);
		$this->targetMimeType = $targetMimeType;
	}

	public function getDisplayName()
	{
		return _kt('OpenOffice Text Extractor');
	}

	public function getSupportedMimeTypes()
	{
		return array(
       		'text/rtf',
       		'application/vnd.oasis.opendocument.text',
       		'application/vnd.oasis.opendocument.text-template',
       		'application/vnd.oasis.opendocument.text-web',
       		'application/vnd.oasis.opendocument.text-master',
       		'application/vnd.sun.xml.writer',
       		'application/vnd.sun.xml.writer.template',
       		'application/vnd.sun.xml.writer.global',
		);
	}

	public function needsIntermediateSourceFile()
	{
		// we need the intermediate file because it
		// has the correct extension. jodconverter uses the extension to determine mimetype
		return true;
	}

	protected function getCommandLine()
	{
		$cmdline = "$this->javaPath -jar $this->converter $this->sourcefile $this->mimetype $this->targetfile $this->targetMimeType $this->ooHost $this->ooPort";
		return $cmdline;
	}

	public function diagnose()
	{
		if (false === $this->converter)
		{
			return _kt('Cannot locate jodconverter');
		}

		if (false === $this->javaPath)
		{
			return _kt('Cannot locate java');
		}



		$connection = @fsockopen($this->ooHost, $this->ooPort,$errno, $errstr,5 );
		if (false === $connection)
		{
			return _kt('Cannot connect to openoffice host');
		}
		fclose($connection);


		return null;
	}
}

?>