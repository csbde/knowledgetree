<?php

require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");

/**
 * $Id$
 * 
 * Represents a dahsboard news item.
 * 
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.authentication 
 */

class DashboardNews {

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
	 * Constructs a news item
	 *
	 * @param string the synopsis
	 * @param string the body
	 * @param integer the rank
	 */
	function DashboardNews($sNewSynopsis, $sNewBody, $iNewRank, $mImage = "") {
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
			$this->setImageMimeTypeID($mImage["mimetype"]);
		} else {			
			if (file_exists($mImage)) {
				// we've been passed a file, so read it in
				$this->setImageFile($mImage);
			}
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
		if (file_exists($sPathToImage)) {
			$aImage = $this->imageToString($sPathToImage);
			$this->sImage = $aImage["image"];
			$this->iImageSize = $aImage["filesize"];
			$this->iImageMimeTypeID = $aImage["mimetypeid"];
		}
	}

	/**
	 * Inserts the current new item into the database
	 *
	 * @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
	 */
	function create(){
        global $default;
        //if the id >= 0, then the object has already been created
        if ($this->iId < 0) {
            $sql = $default->db;
            $result = $sql->query("INSERT INTO $default->owl_news_table (synopsis, body, rank, image, image_size, image_mime_type_id) " .
                                  "VALUES ('" . addslashes($this->sSynopsis) . "', '" . addslashes($this->sBody) . "', $this->iRank, " .
                                  "'" . addslashes($this->sImage) . "', $this->iImageSize, $this->iImageMimeTypeID)");
            if ($result) {
                //set the current news item primary key
                $this->iId = $sql->insert_id();
                return true;
            }
            return false;
        }
        return false;
	}

    /**
     * Update the documents current values in the database
     *
     * @return boolean true on successful update, false otherwise
     */
	function update(){
        global $default;
        if ($this->iId >= 0) {			
            $sql = $default->db;
			$sQuery = "UPDATE " . $default->owl_news_table . " SET " .
								  "synopsis = '" . addslashes($this->sSynopsis) . "', " .
                                  "body = '" . addslashes($this->sBody) . "', " .
                                  "rank = $this->iRank, " .
                                  "image = '" . addslashes($this->sImage) . "', " .
                                  "image_size = $this->iImageSize " .
                                  ($this->iImageMimeTypeID ? ", image_mime_type_id = $this->iImageMimeTypeID " : "") .                                  
           			 "WHERE id = $this->iId";
            $result = $sql->query($sQuery);
            if ($result) {
                return true;
            }
            return false;
        }
        return false;
	}

    /**
     * Delete the current news item from the database.  Set the primary key to -1 
     * on successful deletion
     *
     * @return boolean true and reset id to -1 on successful deletion, false otherwise
     */
    function delete() {
        global $default;
        if ($this->iId >= 0) {
            $sql = $default->db;
            $result = $sql->query("DELETE FROM  " . $default->owl_news_table . "  WHERE id = $this->iId");
            if ($result) {
                $this->iId = -1;
                return true;
            }
            return false;
        }
        return false;
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
        $sql->query("SELECT * FROM $default->owl_news_table WHERE id = $iNewsID");
        if ($sql->next_record()) {
            $aImage = array( "image" => $sql->f("image"),
            				 "filesize" => $sql->f("image_size"),
            				 "mimetypeid" => $sql->f("image_mime_type_id") );
        				
            $oDashboardNews = & new DashboardNews(stripslashes($sql->f("synopsis")), stripslashes($sql->f("body")), $sql->f("rank"), $aImage);
            $oDashboardNews->iId = $iNewsID;
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
        $result = $sql->query("SELECT * FROM " . $default->owl_news_table  . (isset($sWhereClause) ? " WHERE " . $sWhereClause : ""));
        if ($result) {
            $iCount = 0;
            while ($sql->next_record()) {
                $oDashboardNews = & DashboardNews::get($sql->f("id"));
                $aDashboardNewsArray[$iCount++] = $oDashboardNews;
            }
            return $aDocumentArray;
        }
        return false;
    }
    
    /**
     * Reads in an image file as a string and returns it
     *
     * @param string path to the image file
     * @return string the image as a string
     */
    function imageToString($sImagePath) {
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
}