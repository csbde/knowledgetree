<?php
require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
/**
 * $Id$
 *
 * Represents a dashboard news item.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.dashboard 
 */
class DashboardNews extends KTEntity {

	/**
	 * The primary key of the news item
	 */
	var $iId;
	/**
	 * A synopsis of the news item
	 */
	var $sSynopsis;
	/**
	 * The new item content
	 */ 
	var $sBody;
	/**
	 * The rank of the news item
	 */
	var $iRank;
	/**
	 * An accompanying image
	 */
	var $sImage;
	/**
	 * The size of the image
	 */
	var $iImageSize;
	/**
	 * The mime type id of the image
	 */
	var $iImageMimeTypeID;	
	/**
	 * The maximum allowable width of an image
	 */
	var $iMaxImageWidth = 80;
	/**
	 * The maximum allowable height of an image
	 */
	var $iMaxImageHeight = 40;
	/**
	 * Is this news item active
	 */
	var $bActive = true;
	
	/**
	 * Constructs a news item
	 *
	 * @param string the synopsis
	 * @param string the body
	 * @param integer the rank
	 */
	function DashboardNews($sNewSynopsis, $sNewBody, $iNewRank, $mImage = "") {
		global $default;
		
		// primary key not set as document is not stored yet
		$this->iId = -1;
		$this->setSynopsis($sNewSynopsis);
		$this->setBody($sNewBody);
		$this->setRank($iNewRank);
		// if we've been passed an array
		if (is_array($mImage)) {
			// then the image details are in it
			$this->setImage($mImage["image"]);
			$this->setImageSize($mImage["filesize"]);
			$this->setImageMimeTypeID($mImage["mimetypeid"]);
		} else if (strlen($mImage) > 0){	
			if (file_exists($mImage)) {
				// we've been passed a file, so read it in
				$this->setImageFile($mImage);
			}
		} else {
			// initialise
			$this->setImage("");
			$this->setImageSize(0);
			$this->setImageMimeTypeID(0);
		}
	}

	/**
	 * Gets the new item primary key
	 */
	function getID(){
		return $this->iId;
	}

	/**
	 * Gets the synopsis
	 */
	function getSynopsis(){
		return $this->sSynopsis;
	}

	/**
	 * Sets the synopsis
	 *
	 * @param string the new synopsis
	 */
	function setSynopsis($sNewSynopsis){
		$this->sSynopsis = $sNewSynopsis;
	}

	/**
	 * Gets the body
	 */
	function getBody(){
		return $this->sBody;
	}

	/**
	 * Returns a fragment of the body
	 */
	function getBodyFragment() {
		return substr($this->sBody, 0, 50);
	}

	/**
	 * Sets the body
	 * 
	 * @param string the new news body
	 */ 
	function setBody($sNewBody){
		$this->sBody = $sNewBody;
	}

	/**
	 * Gets the rank
	 */
	function getRank(){
		return $this->iRank;
	}

	/**
	 * Sets the rank
	 *
	 * @param integer the new news item rank
	 */
	function setRank($iNewRank){
		$this->iRank = $iNewRank;
	}

	/**
	 * Retrieves the image text
	 *
	 */
	function getImage() {
		return $this->sImage;
	}
	
	/**
	 * Sets the image text
	 *
	 * @param string the new image text	 
	 */
	function setImage($sNewImageText) {
		$this->sImage = $sNewImageText;
	}
		 	 
	/**
	 * Retrieve the image size
	 */
	function getImageSize() {
		return $this->iImageSize;
	}
	 
	/**
	 * Set the image size
	 *
	 * @param integer the image size	 
	 */
	function setImageSize($iNewImageSize) {
		$this->iImageSize = $iNewImageSize;
	}
	 
	/**
	 * Retrieve the image mime type
	 */
	function getImageMimeTypeID() {
		return $this->iImageMimeTypeID;
	}

	/**
	 * Set the image mime type
	 *
	 * @param integer the image mime type	 
	 */
	function setImageMimeTypeID($iNewMimeTypeID) {
		$this->iImageMimeTypeID = $iNewMimeTypeID;
	}
	
	/**
	 * Retrieve the active status
	 */
	function getActive() {
		return $this->bActive;
	}

	/**
	 * Set the active status
	 *
	 * @param boolean new active status	 
	 */
	function setActive($bNewActive) {
		$this->bActive = KTUtil::anyToBool($bNewActive);
	}
		
	/**
	 * Retrieve the maximum image width
	 */
	function getMaxImageWidth() {
		return $this->iMaxImageWidth;
	}

	/**
	 * Set the maximum image width
	 *
	 * @param integer the maximum image width	 
	 */
	function setMaxImageWidth($iNewMaxImageWidth) {
		$this->iMaxImageWidth = $iNewMaxImageWidth;
	} 
	
	/**
	 * Retrieve the maximum image height
	 */
	function getMaxImageHeight() {
		return $this->iMaxImageHeight;
	}

	/**
	 * Set the maximum image height
	 *
	 * @param integer the maximum image height	 
	 */
	function setMaxImageHeight($iNewMaxImageHeight) {
		$this->iMaxImageHeight = $iNewMaxImageHeight;
	}

	/**
	 * Returns the maximum dimensions as a string
	 *
	 * @param string the maximum image dimensions
	 */
	function getMaxImageDimensions() {
		return $this->iMaxImageWidth . "x" . $this->iMaxImageHeight;
	} 
	
	/**
	 * Displays the news item image
	 */
	function displayImage(){
        header("Content-Type: " . PhysicalDocumentManager::getMimeTypeName($this->iImageMimeTypeID));
		header("Content-Length: " . $this->iImageSize);		
		echo $this->sImage;
	}

	/**
	 * Sets the image information from a file
	 *
	 * @param string path to the image on the filesystem
	 */
	function setImageFile($sPathToImage){
		global $default;
		$default->log->info("set image file called with $sPathToImage");
		if (file_exists($sPathToImage)) {
			$aImage = $this->readInImage($sPathToImage);
			$this->sImage = $aImage["image"];
			$this->iImageSize = $aImage["filesize"];
			$this->iImageMimeTypeID = $aImage["mimetypeid"];
		}
	}

    function _fieldValues () {
        return array(
            'synopsis' => $this->sSynopsis,
            'body' => $this->sBody,
            'rank' => $this->iRank,
            'image' => $this->sImage,
            'image_size' => $this->iImageSize,
            'image_mime_type_id' => $this->iImageMimeTypeID,
            'active' => $this->bActive,
        );
    }

    function _table () {
        global $default;
        return $default->news_table;
    }

    /**
     * Static function.  Given a news item primary key will create
     * a DashboardNews object and populate it with the corresponding
     * database values
     *
     * @return DashboardNews populated DashboardNews object on success, false otherwise
     */
    function & get($iNewsID) {
        global $default;
        $sql = $default->db;
        $sql->query(array("SELECT * FROM $default->news_table WHERE id = ?", $iNewsID));/*ok*/
        if ($sql->next_record()) {
            $aImage = array( "image" => $sql->f("image"),
            				 "filesize" => $sql->f("image_size"),
            				 "mimetypeid" => $sql->f("image_mime_type_id") );			
            $oDashboardNews = & new DashboardNews($sql->f("synopsis"), $sql->f("body"), $sql->f("rank"), $aImage);
            $oDashboardNews->iId = $iNewsID;
            $oDashboardNews->setActive($sql->f("active"));
            return $oDashboardNews;          
        }
        return false;
    }

    /**
     * Static function
     * Get a list of DashboardNews objects
     *
     * @param  String  Where clause (optional)
     * @return Array array of DashboardNews objects, false otherwise
     */
    function getList($sWhereClause = null) {
        global $default;
        $aDashboardNewsArray = array();
        $sql = $default->db;
        $result = $sql->query("SELECT * FROM " . $default->news_table  . (isset($sWhereClause) ? " WHERE " . $sWhereClause : "") . " ORDER BY rank ASC");/*wc*/
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oDashboardNews = & DashboardNews::get($sql->f("id"));
                $aDashboardNewsArray[$iCount++] = $oDashboardNews;
            }
            return $aDashboardNewsArray;
        }
        return false;
    }
    
    /**
     * Reads in an image file as a string and returns it
     *
     * @param string path to the image file
     * @return string the image as a string
     */
    function readInImage($sImagePath) {
   		// check if the image exists
   		if (file_exists($sImagePath)) {
   			// read in the file
			$fd = fopen ($sImagePath, "rb");
			$sImageString = fread($fd, filesize($sImagePath));
			fclose($fd);
			// return the string
			return array("image" => $sImageString, "filesize" => filesize($sImagePath), "mimetypeid" => PhysicalDocumentManager::getMimeTypeID(null, $sImagePath));   			
   		} else {
   			return false;
   		}   		
    }
    
    /**
     * Evaluates the size of the image and returns false if it is too big
     *
     * @param integer the width of the image
     * @param integer the height of the image
     */
    function checkImageSize($iImageWidth, $iImageHeight) {
    	global $default;
    	if ( ($iImageWidth <= $this->iMaxImageWidth) && ($iImageHeight <= $this->iMaxImageHeight) ) {
    		return true;
    	} else {
    		return false;
    	}
    }
    
    /**
     * Returns the link text for linking to the current news item image
     */
    function getImageLink() {
    	global $default;
    	if ($this->iImageSize > 0) {
    		$sTargetPage = $default->siteMap->getPage("viewNewsImage");
		    $sLink = "http" . ($default->sslEnabled ? "s" : "") . "://" . $default->serverName . 
		             ((substr($sTargetPage, 0, strlen($default->rootUrl)) != $default->rootUrl) ? $default->rootUrl : "") . 
		             $sTargetPage . "?fNewsID=" . $this->getID();    		
    		
			return "<img src=\"$sLink\" border=\"0\">";
    	} else {
    		return "";
    	}   	
    }
}
