<?php

/**
* Class DocumentManager
*
* Contains all functions required for document management,
* such as the creation/deletion/removal of document types.
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 8 January 2003
*
* @todo Complete updateDocumentType() function
* 
*/

require_once ("$default->owl_root_url/lib/owl.lib.php");
require_once("./phpmailer/class.phpmailer.php"); // for emailing

class DocumentManager {
	
	/** 
	* Create a new document type
	* 
	* @param $sName		Name of new document type
	*
	* @return true on successful creation, false otherwise and set $default->errorMessage 
	*/
	function createDocumentType($sName) {
		//escape all the necessary characters that may affect db query
		$sName = addslashes($sName);
		//Get hold of the global error string
		global $default;
		//if the document type doesn't exist
		if (!($this->documentTypeExists($sName))) {
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO  " . $default->owl_document_types_table . "  (name) values ('" . $sName . "')");
			if (!$result) {
				$default->errorMessage = "Database Error. Failed to insert document type " . $sName;
				return false;
			}
			return true;
		}
		$default->errorMessage = "A document type with this name already exists";
		return false;
	}
	
	/**
	* Delete an existing document type
	*
	* @param $sName		Name of document type to delete
	* 
	* @return true on successful removal, false otherwise and set $default->errorMessage
	*/
	function deleteDocumentType($sName) {
		//escape all the necessary characters that may affect db query
		$sName = addslashes($sName);
		//Get hold of the global error string
		global $default;
		//only remove the document type if it exists
		if ($this->documentTypeExists($sName)) {
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM  " . $default->owl_document_types_table . "  WHERE name = '" . $sName . "'");
			if (!$result) {
				$default->errorMessage = "Database Error.  Failed to delete document type " . $sName;
				return false;
			}			
			return true;
		}
		$default->errorMessage = "There is no document type with this name";
		return false;		
	}

	/**
	* Update a document type
	* 
	* @param $iDocumentTypeID	Primary key of document type to updatee
	* @param $sName				New document type name
	*
	* @return true on successful update, false otherwise and set $default->errorMessage
	*/
	function updateDocumentType($iDocumentTypeID, $sName) {
		
	}
	
	
	/**
	* Get the primary key for a document type
	*
	* @param $sName		Name of document type to get ID for
	*
	* @return int document type id if the document type exists, false otherwise
	*/
	function & getDocumentTypeID($sName) {
		global $lang_err_document_type_field_does_not_exist, $default;
		//escape special characters that may interfere with the db query
		$sName = addslashes($sName);
		
		if ($this->documentTypeExists($sName)) {
			$sql = new Owl_DB();
			$sql->query("SELECT ID FROM " .  $default->owl_document_types_table . " WHERE name = '" . $sName . "'");
			$sql->next_record();
			return $sql->f("ID");
		}
		$default->errorMessage = $lang_err_document_type_does_not_exist . $sName;
		return false;
	}
	
	/**
	* Checks to see if a document type with a given name
	* already exists
	*
	* @param $sName		Name of document type to check
	*
	* @return true if the document type exists, false otherwise
	*/
	function documentTypeExists($sName) {
		//escape all the necessary characters that may affect db query
		$sName = addslashes($sName);
		//Get hold of the global error string
		global $default;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_document_types_table . "  WHERE Name = '" . $sName . "'");		
		return $sql->next_record();
	}
	
	/**
	* Link a document type field with a specific document type
	*
	* @param $iDocumentTypeID		Primary key of document type
	* @param $iDocumentTypeFieldID	Primary key of document field type
	* @param $bIsMandatory			Whether or not the field is mandatory
	*
	* @return true on successful creation, false otherwise and set $default->errorMessage
	*/
	function createDocumentTypeFieldLink($iDocumentTypeID, $iDocumentTypeFieldID, $bIsMandatory) {
		//Get hold of the global error string
		global $default;
		//if the document field type is not associated with the document
		if (!($this->documentTypeFieldExistsForDocumentType($iDocumentTypeID, $iDocumentTypeFieldID))) {
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO  " . $default->owl_document_type_fields_table . "  (document_type_id, field_id, is_mandatory) VALUES (" . $iDocumentTypeID . ", " . $iDocumentTypeFieldID . ", " . $bIsMandatory . ")");
			if (!$result) {
				$default->errorMessage =  "Database Error.  Failed to insert document field type with ID " . $iDocumentTypeFieldID;
				return false;
			}
			return true;
		}
		$default->errorMessage = "This field type is already linked to this document type";
		return false;
	}
	
	/**
	* Delete the link between a document type field and a specific document type
	*
	* @param $iDocumentTypeID		Primary key of document type
	* @param $iDocumentTypeFieldID	Primary key of document type field
	*/
	function deleteDocumentTypeFieldLink($iDocumentTypeID, $iDocumentTypeFieldID) {
		global $default;
		//Get hold of the global error string
		global $default;
		if ($this->documentTypeFieldExistsForDocumentType($iDocumentTypeID, $iDocumentTypeFieldID)) {
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM  " . $default->owl_document_type_fields_table . "  where document_type_id = " . $iDocumentTypeID . " AND field_id = " . $iDocumentTypeFieldID);
			if (!result) {
				$default->errorMessage = "Database Error. Failed to deleted document type field with document_type_id " . $iDocumentTypeID . " and field_id " . $iDocumentTypeFieldID;
				return false;
			}
			return true;
		}
		$default->errorMessage = "A dcoument field type with document_type_id " . $iDocumentTypeID . " and a document_id " . $iDocumentTypeID . " does not exist";
		return false;		
	}
	
	
	/**
	* Checks to see if the given document type field is already linked to the given
	* document type.
	*
	* @param $iDocumentTypeID		Primary key of document type
	* @param $iDocumentTypeFieldID	Primary key of document field type
	*
	* @return true is the document field type is linked to the document type, false otherwise
	*/
	function documentTypeFieldExistsForDocumentType($iDocumentTypeID, $iDocumentTypeFieldID) {
		global $default;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM  " . $default->owl_document_type_fields_table . "  WHERE document_type_id = " . $iDocumentTypeID . " AND field_id = " . $iDocumentTypeFieldID);
		return $sql->next_record();
	}
	
	/**
	* Creates a document type field
	*
	* @param $sName		Document type field name
	* @param $sDataType	Field data type
	*
	* @return true on successful insertion, false otherwise and sets $default->errorMessage
	*/
	function createDocumentTypeField($sName, $sDataType) {
		//escape all the necessary characters that may affect db query
		$sName = addslashes($sName);		
		$sDataType = addslashes($sDataType);
		//Get hold of the global error string
		global $default;
		if (!$this->documentTypeFieldExists($sName)) {
			$sql = new Owl_DB();
			$result = $sql->query("INSERT INTO " . $default->owl_fields_table . " (name, data_type) VALUES ('" . $sName . "', '" . $sDataType . "')");			
			if (!$result) {
				$default->errorMessage = "Database Error.  Could not insert document field " . $sName . " with data type " . $sDataType . " into table " . $default->owl_fields_table;
				return false;
			}
			return true;
		}
		$default->errorMessage = "A document type field with this name already exists";
		return false;
	}
	
	/**
	* Deletes a document type field
	*
	* @param $sName		Name of document field type to delete
	*
	* @return true on successful deletion, false otherwise and set $default->errorMessage
	*/
	function deleteDocumentTypeField($sName) {
		//escape any characters that may affect db query
		$sName = addslashes($sName);
		//Get hold of the global error string
		global $default;
		if ($this->documentTypeFieldExists($sName)) {
			$sql = new Owl_DB();
			$result = $sql->query("DELETE FROM " . $default->owl_fields_table . " WHERE Name = '" . $sName . "'");
			if (!$result) {
				$default->errorMessage = "Database Error.  Could not delete document type field " . $sName . " from table " . $default->owl_field_table;
				return false;
			}
			return true;
		}
		$default->errorMessage = "A document type field with the name " . $sName . " does not exist";
		return false;
	}
	
	/**
	* Get the primary key for a document type field
	*
	* @param $sName		Document type field name to get primary key for
	*
	* @return ID if document type field exists, false otherwise and sets $default->errorMessage
	*/
	function & getDocumentTypeFieldID($sName) {
		global $lang_err_document_type_field_does_not_exist, $default;
		$sName = addslashes($sName);
		if ($this->documentTypeFieldExists($sName)) {
			$sql = new Owl_DB();
			$sql->query("SELECT id FROM " . $default->owl_fields_table . " WHERE name = '" . $sName . "'");
			$sql->next_record();
			return $sql->f("id");
		}
		$default->errorMessage = $lang_err_document_type_field_does_not_exist . $sName;
		return false;
	}
	
	/**
	* Checks whether a document type field exists
	*
	* @param $sName		Document type field name to check
	*
	* @return true if document type field exists, false otherwise
	*/
	function documentTypeFieldExists($sName) {
		global $default;
		$sql = new Owl_DB();
		$sql->query("SELECT * FROM " . $default->owl_fields_table . " WHERE name = '" . $sName . "'");
		return $sql->next_record();
	}
	
//------------------------------------------------------------        
/**
 * Function viewDocumentHistory($DocumentID)
 *
 * This function gets all revision history for a particular document
 *
 * @param $DocumentID
 *	The id of the document whose history we wish to retrieve
 * 
*/
//------------------------------------------------------------
function viewDocumentHistory($DocumentID)
{
	// we need to first connect to the db
	// then we run the query...getting all info for specific document id
	// we then return the array???? or print out the info
				
	global $default;
	$db = new Owl_DB();
		
	$Sql = "SELECT * FROM  " . $default->owl_document_transactions_table . " WHERE document_id = $DocumentID";
		
	$result = $db->query($Sql);
	$rows = $db->num_rows($result);
		
	
	// return the result set..
	return $result;

}	
	
	
//------------------------------------------------------------        
/**
 * Function sendHyperLink($FromEmail, $FromName, $ToEmail, $Subject, $EmailBody, $hyperlink)
 *
 * Sends an email containing a hyperlink to a specified recipient
 *
 * @param $FromEmail
 *	The sender's email address
 * @param $FromName
 *	The sender's Name
 * @param $ToEmail
 *	The recipients email address
 * @param $Subject
 *	The subject heading for the email
 * @param $EmailBody
 *	The Body of the email
 * @param $hyperlink
 *	The hyperlink that should be sent
 * 
 * @Todo ...take out the error handling or modify it 
 	 Also check for special characters
*/
//------------------------------------------------------------
function sendHyperLink($FromEmail, $FromName, $ToEmail, $Subject, $EmailBody, $hyperlink)
{
	global $default;
		
	// create a new phpmailer object.
	$mail = new phpmailer();
	
	//set up info
	$mail->IsSMTP(); // telling the class to use SMTP
	$mail->Host = $default->owl_mail_server; // SMTP server
	
	//get info from relevant fields.
	$mail->From = $FromEmail;
	$mail->FromName = $FromName;
	$mail->AddAddress($ToEmail);
	$mail->Subject = $Subj;
	$mail->Body = $EmailBody  . ' ' . $hyperlink;
	$mail->WordWrap = 100;
	$mail->IsHTML(true);
	   
	//send the email       
	if(!$mail->Send())
	{
	   echo "Message was not sent";
	   echo "Mailer Error: " . $mail->ErrorInfo;
	}
	else
	{
	   echo "Message has been sent";
	}
	
}


//------------------------------------------------------------        
/**
 * Function publishDocument($DocumentID,$WebsiteID, $UnitID, $DTime)
 *
 * This function sets up a document for publication by 
 * inserting the various parameters into the web_documents table
 * and then sets the status id corresponding to the 'Pending' Status
 *
 * @param $DocumentID
 *	The id of the document that we wish to publish
 * @param $WebsiteID
 *	The id of the website 
 * @param $UnitID
 *	The id of the unit the document belongs to
 * @param $DTime
 *	The Date and Time that the document was uploaded for publication 
 *	approval...should be 'Now' where now is the D & T it was uploaded
 */
//------------------------------------------------------------
function publishDocument($DocumentID,$WebsiteID, $UnitID, $DTime)
{
	// First need to get all info..ie docid, websiteid, unitid,
	// then set the status to pending..awaiting final approval from 
	// whoever needs to publish it.
	// then store it in the web_documents 

	global $default;
	$db = new Owl_DB();
			
	$Sql = "SELECT * FROM  " . $default->owl_web_documents_table . " WHERE document_id = $DocumentID";
		
	$result = $db->query($Sql);
	$row = $db->num_rows($result);
	
	//get the id when status set to pending
	$Sql2 = "SELECT id FROM " . $default->owl_web_documents_status_table . " WHERE name = 'Pending' ";
	$pending = $db->query($Sql2);
	$db->next_record();
	$PendingID = $db->f("id");
	
	// make sure document does'nt exist in the db already
	if($row == 1)
	{
		printf("Document already awaiting approval");
		return false;
	}
	Else
	{	// insert new entry
		$Sql = "INSERT INTO  " . $default->owl_web_documents_table . " (document_id,web_site_id,unit_id,status_id, datetime) VALUES ($DocumentID,$WebsiteID,$UnitID, $PendingID, '$DTime')";
		$result = $db->query($Sql) ;
		return true;
	
	}
	
}

//------------------------------------------------------------        
/**
 * Function publishDocumentToWeb($DocumentID,$DTime)
 *
 * This function changes the publication status of a document to the WEB..ie it 
 * has received the required approval so its status has to change
 * The web_documents table is update with The status id corresponding to the 'Published' 
 * Status  and the DateTime is updated as well
 *
 * @param $DocumentID
 *	The id of the document that we wish to publish
 * @param $DTime
 *	The Date and Time that the document was uploaded for publication
 *	or approval was given
 * 
*/
//------------------------------------------------------------
function publishDocumentToWeb($DocumentID,$DTime)
{

	global $default;
	$db = new Owl_DB();			
	
	// need to determine if a document has been published to the web already
	$Sql = "SELECT status_id FROM " . $default->owl_web_documents_table . " WHERE document_id = $DocumentID";
	$result = $db->query($Sql);
	$row = $db->num_rows($result);
	$db->next_record();
	$isAlreadyPublishedID = $db->f("status_id");
	
	// get id where name is Published (to prevent errors from change of id)
	$Sql2 = "SELECT id FROM " . $default->owl_web_documents_status_table . " WHERE name = 'Published'";
	$published = $db->query($Sql2);
	$db->next_record();
	$publishID = $db->f("id");
	
	// make sure entry exists and then check status
	if($row == 1)
	{
		if($isAlreadyPublishedID == $publishID)
		{
			
			printf("\nDocument already published");
			return false;
		}
	
		Else
		{	//status is pending..therefore update it to published
			$Sql = "UPDATE " . $default->owl_web_documents_table . " SET status_id = $publishID, datetime = '$DTime' WHERE document_id = $DocumentID";
			$result = $db->query($Sql) ;
			return true;
		}
	}
	Else
	{
		printf("Document is not yet been sent for Publication approval");
	}
}




	
}
?>
