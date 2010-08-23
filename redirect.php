<?php

/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
class Redirector
{
    
	/**
	 * Constructor
	 */
	public function __construct($uri)
    {
        $this->uri = $this->cleanUri($uri);
		
		$this->foundDestination = FALSE;
    }
    
    function run()
    {
		// First check for some special cases
		switch($this->uri)
        {
            case 'dashboard': $this->finalizeRun('dashboard.php'); break;
            case 'admin': $this->finalizeRun('admin.php'); break;
            case 'preferences': $this->finalizeRun('preferences.php'); break;
        }
		
		if (!$this->foundDestination) {
			
			// Only proceed if it is a document or a folder
			if ($this->isDocumentOrFolder($this->uri)) {
				
				
				// Needs further work if catering for actions
				// See discussion doc
				
				// If Folder
				if (substr($this->uri, 0, 2) == '00') {
					$_REQUEST['fFolderId'] = base_convert(substr($this->uri, 2), 36, 10);
					$this->finalizeRun('browse.php');
					
				// Else Document
				} else {
					$_REQUEST['fDocumentId'] = base_convert(substr($this->uri, 2), 36, 10);
					$this->finalizeRun('view.php');
				}
			}
		}
		
		
		if (!$this->foundDestination) {
			header("HTTP/1.0 404 Not Found");
			$this->finalizeRun('dashboard.php');
		}
    }
	
	/**
	 * Method to check if the URL points to a folder or document
	 * @param string $uri URI
	 * @return boolean
	 */
	private function isDocumentOrFolder($uri)
	{
		$firstPart = substr($uri, 0, 2);
		
		if ($firstPart == '00' || $firstPart == '01') {
			return TRUE;
		} else {
			return FALSE;
		}
	}
    
	/**
	 * Method to perform some cleanup URI
	 * @param string $uri URI
	 * @return string
	 */
    private function cleanUri($uri)
	{
		// This is a check that no pages end with a slash at end - for SEO
		if (substr($uri, -1, 1) == '/') {
			if ($uri == '/') {
				// Do nothing
			} else {
				// Check that there is no question mark
				if (strpos($uri, '?') === FALSE) {
					// Redirect to location minus last slash
					header('Location:'.substr($uri, 0, -1));
				}
			}
		}
		
		// Remove Query String
		$uri = preg_replace('/(\?.*)/i', '', $uri);
		
		// Remove the first slash
		$uri = substr($uri, 1);
		
		return $uri;
	}
	
	/**
	 * Method to finish up the redirector
	 * Loads the appropriate file, and sets the flag to TRUE
	 * 
	 * @param string $uri URI
	 */
	private function finalizeRun($file)
	{
		// Adjust Current Server Variables to reflect new path
		
		$_SERVER['SCRIPT_NAME'] = $file;
		$_SERVER['REQUEST_URI'] = $file;
		$_SERVER['PHP_SELF'] = $file;
		
		$this->foundDestination = TRUE;
		
		require_once($file);
	}
}


$redirector = new Redirector($_SERVER['REQUEST_URI']);
$redirector->run();


