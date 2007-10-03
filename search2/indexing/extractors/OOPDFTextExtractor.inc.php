<?php

require_once('PDFExtractor.inc.php');
require_once('OOTextExtractor.inc.php');

class OOPDFTextExtractor extends CompositeExtractor
{
	public function __construct()
	{
		parent::__construct(new OOTextExtractor('application/pdf'),'pdf','application/pdf',new PDFExtractor(), true);
	}

	public function getSupportedMimeTypes()
	{
		// we provide this so diagnose doesn't fail
		return array();
	}

	public function getDisplayName()
	{
		// we provide this so diagnose doesn't fail
		throw new Exception(_kt('This should be overriden'));
	}

}

/*
class OOPDFTextExtractor extends DocumentExtractor
{

	private $pdf2txt;


	private $text2pdf;

	public function __construct()
	{
		$this->pdf2txt = new PDFExtractor();
		$this->text2pdf = new OOTextExtractor();
	}

	public function needsIntermediateSourceFile()
	{
		// we need the intermediate file because it
		// has the correct extension. jodconverter uses the extension to determine mimetype
		return true;
	}

	public function getDisplayName()
	{
		throw new Exception('This should be overriden');
	}

	public function getSupportedMimeTypes()
	{
		return array();
	}

	public function extractTextContent()
	{
		$pdffile = $this->targetfile . '.pdf';

		$this->text2pdf->setSourceFile($this->sourcefile);
		$this->text2pdf->setTargetFile($pdffile);
		$this->text2pdf->setMimeType($this->mimetype);
		$this->text2pdf->setExtension($this->extension);
		if ($this->extractTextContent())
		{
			return false;
		}

		$this->pdf2txt->setSourceFile($pdffile);
		$this->pdf2txt->setTargetFile($this->targetfile);
		$this->pdf2txt->setMimeType('application/pdf');
		$this->pdf2txt->setExtension('pdf');
		$result = $this->pdf2txt->extractTextContent();

		unlink(@$pdffile);

		return $result;
	}

	public function diagnose()
	{
		$diagnosis = $this->pdf2txt->diagnose();
		if (!empty($diagnosis))
		{
			return $diagnosis;
		}

		$diagnosis = $this->text2pdf->diagnose();
		if (!empty($diagnosis))
		{
			return $diagnosis;
		}

		return null;
	}
} */

?>