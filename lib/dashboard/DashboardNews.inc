<?php

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
	 * Constructs a news item
	 *
	 * @param string the synopsis
	 * @param string the body
	 * @param integer the rank
	 * @param string the image
	 */
	function DashboardNews($sNewSynopsis, $sNewBody, $iNewRank, $sNewImage) {
		// primary key not set as document is not stored yet
		$this->iId = -1;
		$this->sSynopsis = $sNewSynopsis;
		$this->sBody = $sNewBody;
		$this->iRank = $iNewRank;
		$this->sImage = $sNewImage;
	}

	/**
	 * Gets the id
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
	 * @param    $sNewSynopsis
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
	 * @param    $sNewBody
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
	 * @param    $iNewRank
	 */
	function setRank($iNewRank){
		$this->iRank = $iNewRank;
	}

	/**
	 * Gets the image
	 */
	function getImage(){
		return $this->sImage;
	}

	/**
	 * Sets the image
	 *
	 * @param    $sImage
	 */
	function setImage($sNewImage){
		$this->sImage = $sNewImage;
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
            $result = $sql->query("INSERT INTO $default->owl_news_table (image, synopsis, body, rank) " .
                                  "VALUES ('" . addslashes($this->sImage) . "', '" . addslashes($this->sSynopsis) . "', '" . addslashes($this->sBody) . "', $this->iRank)");
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
                                  "image = '" . addslashes($this->sImage) . "', " .
								  "synopsis = '" . addslashes($this->sSynopsis) . "', " .
                                  "body = '" . addslashes($this->sBody) . "', " .
                                  "rank = $this->iRank " .
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
            $oDashboardNews = & new DashboardNews(stripslashes($sql->f("synopsis")), stripslashes($sql->f("body")), $sql->f("rank"), $sql->f("image"));
            $oDashboardNews->iId = $iDocumentID;
            return $$oDashboardNews;          
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
   		
    }
}