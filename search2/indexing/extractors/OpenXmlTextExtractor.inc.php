<?php

/**
 * $Id:$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 *
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_DIR.'/thirdparty/peclzip/pclzip.lib.php');

class OpenXmlTextExtractor extends ExternalDocumentExtractor
{
	public function __construct()
	{
		$config = KTConfig::getSingleton();

		/* ** Using peclzip instead of the unzip binary **
		$this->unzip = KTUtil::findCommand("import/unzip", 'unzip');
		$this->unzip = str_replace('\\','/',$this->unzip);
		$this->unzip_params = $config->get('extractorParameters/unzip', '"{source}" "{part}" -d "{target_dir}"');
		*/
		parent::__construct();
	}


	/**
	 * Basic function setting the display name
	 *
	 * @return string
	 */
	public function getDisplayName()
	{
		return _kt('Open Xml Text Extractor');
	}

	public function needsIntermediateSourceFile()
	{
		return true;
	}

	/**
	 * Return a list of all Office 2007 document types that are supported
	 *
	 * @return array
	 */
	public function getSupportedMimeTypes()
	{
		return array(
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'application/vnd.openxmlformats-officedocument.presentationml.template',
			'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.template'
		);
	}

	/**
	 * Trivial function to resolve if the document is word, excel, or power point
	 *
	 * @return array
	 */

	private function detectDocumentType()
	{
		$types = array(
			'docx' => array(
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
					'application/vnd.openxmlformats-officedocument.wordprocessingml.template'
				),
			'pptx' => array(
					'application/vnd.openxmlformats-officedocument.presentationml.template',
					'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
					'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
			'xlsx' => array(
					'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					'application/vnd.openxmlformats-officedocument.spreadsheetml.template'),

		);
		foreach($types as $key=>$types)
		{
			if (in_array($this->mimetype, $types))
			{
				return $key;
			}
		}
	}

	/**
	 * The open xml file comprises various file with different content. This function identifies
	 * which of those content types are worth indexing.
	 *
	 * @param string $openxml_type
	 * @param string $mime_type
	 * @return boolean
	 */
	private function interestingParts($openxml_type, $mime_type)
	{
		$interest = array(
			'docx'=> array(
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml',
					'application/vnd.openxmlformats-officedocument.wordprocessingml.endnotes+xml',
			 		'application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml',
			 		'application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml'),

			 'pptx' => array('application/vnd.openxmlformats-officedocument.presentationml.slide+xml'),
			 'xlsx' => array(
					'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml',
			 		'application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml',
			 		'application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml',
			 		'application/vnd.openxmlformats-package.core-properties+xml'));
		return in_array($mime_type, $interest[$openxml_type]);
	}

	/**
	 * Returns a list of tokens that were identified by the [Content_Types].xml file. This file lists links to all parts of the document.
	 * We use interestingParts() above to identify which of these parts are interesting from a content perspective.
	 *
	 * @return array
	 */
	private function getOpenXmlContentTypes()
	{
		$config = KTConfig::getSingleton();
		$temp_dir = $config->get('urls/tmpDirectory');

		$docid = $this->document->getId();
		$time = 'ktindexer_openxml_'. time() . '-' . $docid;
		$this->openxml_dir = $temp_dir . '/' . $time;

		$this->sourcefile = str_replace('\\','/',$this->sourcefile);
		$this->openxml_dir = str_replace('\\','/',$this->openxml_dir);

    	$archive = new PclZip($this->sourcefile);

    	if ($archive->extract(PCLZIP_OPT_PATH, $this->openxml_dir) == 0){
    		$this->output = _kt('Failed to extract content');
			return false;
    	}

    	/* *** Original code using the unzip binary ***
		$cmd = '"' . $this->unzip . '"' . ' ' . str_replace(
			array('{source}','{part}', '{target_dir}'),
			array($this->sourcefile, '*Content_Types*.xml',$this->openxml_dir), $this->unzip_params);

		$cmd = str_replace('\\','/', $cmd);

 		if (!$this->exec($cmd))
		{
			$this->output = _kt('Failed to execute command: ') . $cmd;
			return false;
		}
		*** End unzip code *** */

		$filename = $this->openxml_dir . '/[Content_Types].xml';
		if (!file_exists($filename))
		{
			$this->output = _kt('Failed to find file: ') . $filename;
			return false;
		}

		$xml_content = file_get_contents($filename);

		// once we have the content, we can cleanup!
		@unlink($filename);

		// parse the file
		$parser = xml_parser_create();
		xml_parse_into_struct($parser, $xml_content, $vals, $index);
		xml_parser_free($parser);

		return $vals;
	}

	/**
	 * Extract the text from a file within the archive for a specific file.
	 *
	 * @param string $filename
	 * @return string
	 */
	private function getContent($filename)
	{
		$config = KTConfig::getSingleton();

		if (substr($filename,0,1) == '/')
		{
			$filename = substr($filename,1);
		}
		$filename = str_replace('\\','/',$filename);

		/*
		// Removing the unzip command as the whole document gets unzipped at the start

		$cmd = '"' .$this->unzip . '"' . ' ' . str_replace(
			array('{source}','{part}', '{target_dir}'),
			array($this->sourcefile, $filename,$this->openxml_dir), $this->unzip_params);

		if (!$this->exec($cmd))
		{
			$this->output = _kt('Failed to execute command: ') . $cmd;
			return false;
		}
		*/

		$filename = $this->openxml_dir . "/$filename";
		if (!file_exists($filename))
		{
			$this->output = _kt('Failed to open file: ') . $filename;
			return false;
		}

		$content = file_get_contents($filename);

		// cleanup
		@unlink($filename);

		$content = preg_replace ("@(</?[^>]*>)+@", " ", $content);

		return $content;
	}


	/**
	 * Given the tokens in the [Content_Types].xml, extract the content
	 *
	 * @param array $vals
	 * @return string
	 */
	function getOpenXmlText($vals)
	{
		$openxml_type = $this->detectDocumentType();

		$content = '';

		foreach($vals as $val)
		{
			if ($val['tag'] == 'OVERRIDE' && $val['type'] == 'complete')
			{
				if ($this->interestingParts($openxml_type, $val['attributes']['CONTENTTYPE']))
				{
					$filename = $val['attributes']['PARTNAME'];
					$result = $this->getContent($filename);

					if ($result === false)
					{
						return false;
					}

					$content .= $result;
				}
			}
		}

		return $content;
	}

	/**
	 * The main context extraction function
	 *
	 * @return bool
	 */

	public function extractTextContent()
	{
		$xml_content = $this->getOpenXmlContentTypes();

		if ($xml_content !== false)
		{
			$content = $this->getOpenXmlText($xml_content);

			if ($content !== false)
			{
				$result = file_put_contents($this->targetfile, $this->filter($content));

				if ($result === false)
				{
					$this->output = _kt('Could not save content to file: ') . $this->targetfile;
					KTUtil::deleteDirectory($this->openxml_dir);
					return false;
				}
			}
			KTUtil::deleteDirectory($this->openxml_dir);

			return true;
		}
		KTUtil::deleteDirectory($this->openxml_dir);

		return false;

	}

	/**
	 * Check that unzip is available
	 *
	 * @return boolean
	 */
	public function diagnose()
	{
	    return null;
		if (false === $this->unzip)
		{
			return sprintf(_kt("Cannot locate unzip: %s."), $this->unzip);
		}
		return null;
	}

}

?>
