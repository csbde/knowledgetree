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

class PDFExtractor extends ApplicationExtractor
{
	public function __construct()
	{
		$config = KTConfig::getSingleton();
		$params = $config->get('extractorParameters/pdftotext', '-nopgbrk -enc UTF-8 "{source}" "{target}"');

		parent::__construct('externalBinary','pdftotext','pdftotext',_kt('PDF Text Extractor'),$params);
	}

	public function getSupportedMimeTypes()
	{
	    return array();
		return array('application/pdf');
	}

	protected function filter($text)
	{
		return $text;
	}

	protected  function exec($cmd)
	{
	    global $default;
		$res = 	parent::exec($cmd);

		if (false === $res && ((strpos($this->output, 'Copying of text from this document is not allowed') !== false) ||
		                      (strpos($this->output, 'Incorrect password') !== false)))
		{
			$this->output = '';
			file_put_contents($this->targetfile, _kt('Security properties on the PDF document prevent text from being extracted.'));
			$default->log->info('Security properties on the PDF document prevent text from being extracted.');
			return true;
		}

		if (false === $res && (strpos($this->output, 'PDF file is damaged') !== false))
		{
			$this->output = '';
			$default->log->info('PDF file is damaged');
			return true;
		}


		if (false === $res && (strpos($this->output, '(continuing anyway)') !== false))
		{
			$this->output = '';
			return true;
		}

		if (false === $res && (strpos($this->output, 'font') !== false))
		{
			$this->output = '';
			return true;
		}

		if (filesize($this->targetfile) > 0)
		{
			$this->output = '';
			return true;
		}

		return $res;

	}
}

?>
