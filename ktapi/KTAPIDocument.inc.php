<?
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

class KTAPI_Document extends KTAPI_FolderItem 
{
	/**
	 * This is a reference to the internal document object.
	 *
	 * @var Document
	 */
	var $document;	
	/**
	 * This is the id of the document.
	 *
	 * @var int
	 */
	var $documentid;
	/**
	 * This is a reference to the parent folder.
	 *
	 * @var KTAPI_Folder
	 */
	var $ktapi_folder;
	
	function get_documentid()
	{
		return $this->documentid;
	}	
	
	/**
	 * This is used to get a document based on document id.
	 *
	 * @static 
	 * @access public
	 * @param KTAPI $ktapi
	 * @param int $documentid
	 * @return KTAPI_Document
	 */
	function &get(&$ktapi, $documentid)
	{
		assert(!is_null($ktapi));
		assert(is_a($ktapi, 'KTAPI'));
		assert(is_numeric($documentid));
		
		$documentid += 0;
		
		$document = &Document::get($documentid);
		if (is_null($document) || PEAR::isError($document))
		{
			return new KTAPI_Error(KTAPI_ERROR_DOCUMENT_INVALID,$document );
		}
		
		$user = $ktapi->can_user_access_object_requiring_permission($document, KTAPI_PERMISSION_READ);
		
		if (is_null($user) || PEAR::isError($user))
		{
			return $user;
		}	
		
		$folderid = $document->getParentID();

		if (!is_null($folderid))
		{
			$ktapi_folder = &KTAPI_Folder::get($ktapi, $folderid);
		}
		else 
		{
			$ktapi_folder = null;
		}
		// We don't do any checks on this folder as it could possibly be deleted, and is not required right now.

		return new KTAPI_Document($ktapi, $ktapi_folder, $document);
	}	
	
	function is_deleted()
	{
		return ($this->document->getStatusID() == 3);
	}
	
	/**
	 * This is the constructor for the KTAPI_Folder.
	 *
	 * @access private
	 * @param KTAPI $ktapi
	 * @param Document $document
	 * @return KTAPI_Document
	 */	
	function KTAPI_Document(&$ktapi, &$ktapi_folder, &$document)
	{
		assert(is_a($ktapi,'KTAPI'));
		assert(is_null($ktapi_folder) || is_a($ktapi_folder,'KTAPI_Folder'));
		
		$this->ktapi = &$ktapi;
		$this->ktapi_folder = &$ktapi_folder;
		$this->document = &$document;
		$this->documentid = $document->getId();
	}
	
	/**
	 * This checks a document into the repository
	 *
	 * @param string $filename
	 * @param string $reason
	 * @param string $tempfilename
	 * @param bool $major_update
	 */	
	function checkin($filename, $reason, $tempfilename, $major_update=false)
	{	
		if (!is_file($tempfilename))
		{
			return new PEAR_Error('File does not exist.');
		}
		
		$user = $this->can_user_access_object_requiring_permission($this->document, KTAPI_PERMISSION_WRITE);
		
		if (PEAR::isError($user))
		{
			return $user;
		}
		
		if (!$this->document->getIsCheckedOut())
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_NOT_CHECKED_OUT);
		}

		$options = array('major_update'=>$major_update);

		$currentfilename = $this->document->getFileName();
		if ($filename != $currentfilename)
		{
			$options['newfilename'] = $filename;
		}

		DBUtil::startTransaction();
		$result = KTDocumentUtil::checkin($this->document, $tempfilename, $reason, $user, $options);

		if (PEAR::isError($result))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR,$result);
		}
		DBUtil::commit();
		
		$tempfilename=addslashes($tempfilename);
		$sql = "DELETE FROM uploaded_files WHERE tempfilename='$tempfilename'";
		$result = DBUtil::runQuery($sql);		
		if (PEAR::isError($result))
		{
			return $result;
		}		
		
	}
	
	
	function is_checked_out()
	{
		return ($this->document->getIsCheckedOut());	
	}
	
	/**
	 * This reverses the checkout process.
	 *
	 * @param string $reason
	 */
	function undo_checkout($reason)
	{
		$user = $this->can_user_access_object_requiring_permission($this->document, KTAPI_PERMISSION_WRITE);
		
		if (PEAR::isError($user))
		{
			return $user;
		}

		if (!$this->document->getIsCheckedOut())
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_NOT_CHECKED_OUT);
		}

		DBUtil::startTransaction();

		$this->document->setIsCheckedOut(0);
		$this->document->setCheckedOutUserID(-1);
		$res = $this->document->update();
		if (($res === false) || PEAR::isError($res))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR,$res);
		}
		
		$oDocumentTransaction = & new DocumentTransaction($this->document, $reason, 'ktcore.transactions.force_checkin');
				
		$res = $oDocumentTransaction->create();
		if (($res === false) || PEAR::isError($res)) {
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR,$res);
		}
		DBUtil::commit();
	}

	/**
	 * This returns a URL to the file that can be downloaded.
	 *
	 * @param string $reason
	 */
	function checkout($reason)
	{
		$user = $this->can_user_access_object_requiring_permission($this->document, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($user))
		{
			return $user;
		}
 
		if ($this->document->getIsCheckedOut())
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_CHECKED_OUT);
		}

		DBUtil::startTransaction();
		$res = KTDocumentUtil::checkout($this->document, $reason, $user);
		if (PEAR::isError($res))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $res);
		}

		DBUtil::commit();
	}
	
	/**
	 * This deletes a document from the folder.
	 *
	 * @param string $reason
	 */
	function delete($reason)
	{
		$user = $this->can_user_access_object_requiring_permission( $this->document, KTAPI_PERMISSION_DELETE);

		if (PEAR::isError($user))
		{
			return $user;
		}

		if ($this->document->getIsCheckedOut())
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_CHECKED_OUT);
		}

		DBUtil::startTransaction();
		$res = KTDocumentUtil::delete($this->document, $reason);
		if (PEAR::isError($res))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $res);
		}

		DBUtil::commit();
	}
	
	/**
	 * This changes the owner of the file.
	 *
	 * @param string $ktapi_newuser
	 */	
	function change_owner($newusername, $reason='Changing of owner.')
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_CHANGE_OWNERSHIP);

		if (PEAR::isError($user))
		{
			return $user;
		}		
		           
        DBUtil::startTransaction();
        
        $user = &User::getByUserName($newusername);
        if (is_null($user) || PEAR::isError($user))
        {
        	return new KTAPI_Error('User could not be found',$user);
        }
        
        $newuserid = $user->getId();
        
        $this->document->setOwnerID($newuserid);
        
        $res = $this->document->update();
        
        if (PEAR::isError($res)) 
        {
            DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR ,$res );
        }
        
        $res = KTPermissionUtil::updatePermissionLookup($this->document);
        if (PEAR::isError($res)) 
        {
            DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR,$res );
        }
        
		$oDocumentTransaction = & new DocumentTransaction($this->document, $reason, 'ktcore.transactions.permissions_change');
				
		$res = $oDocumentTransaction->create();
		if (($res === false) || PEAR::isError($res)) {
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR,$res );
		}
        
		DBUtil::commit();
	}	
	
	/**
	 * This copies the document to another folder.
	 *
	 * @param KTAPI_Folder $ktapi_target_folder
	 * @param string $reason
	 * @param string $newname
	 * @param string $newfilename
	 */
	function copy(&$ktapi_target_folder, $reason, $newname=null, $newfilename=null)
	{
		assert(!is_null($ktapi_target_folder));
		assert(is_a($ktapi_target_folder,'KTAPI_Folder'));
		
		if (empty($newname))
		{
			$newname=null;
		}
		if (empty($newfilename))
		{
			$newfilename=null;
		}
		
		$user = $this->ktapi->get_user();

		if ($this->document->getIsCheckedOut())
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_CHECKED_OUT);
		}

		$target_folder = &$ktapi_target_folder->get_folder();
		
		$result = $this->can_user_access_object_requiring_permission(  $target_folder, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($result))
		{
			return $result;
		}

		$name = $this->document->getName();
		$clash = KTDocumentUtil::nameExists($target_folder, $name);
        if ($clash && !is_null($newname)) 
        {        
        	$name = $newname;
        	$clash = KTDocumentUtil::nameExists($target_folder, $name);
        }
        if ($clash) 
        {
        	return new PEAR_Error('A document with this title already exists in your chosen folder.  Please choose a different folder, or specify a new title for the copied document.');
        }
        
        $filename=$this->document->getFilename();
        $clash = KTDocumentUtil::fileExists($target_folder, $filename);

        if ($clash && !is_null($newname)) 
        {
			$filename = $newfilename;
            $clash = KTDocumentUtil::fileExists($target_folder, $filename);
        }
        if ($clash) 
        {
        	return new PEAR_Error('A document with this filename already exists in your chosen folder.  Please choose a different folder, or specify a new filename for the copied document.');
        }
        		
		DBUtil::startTransaction();
                 
        $new_document = KTDocumentUtil::copy($this->document, $target_folder, $reason);
        if (PEAR::isError($new_document)) 
        {
            DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR,$new_document );
        }
        
        $new_document->setName($name);
        $new_document->setFilename($filename);
                
        $res = $new_document->update();
        
        if (PEAR::isError($res)) 
        {
            DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR,$res );
        }

        DBUtil::commit();
            
        // FIXME do we need to refactor all trigger usage into the util function?
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('copyDocument', 'postValidate');
        foreach ($aTriggers as $aTrigger) {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'document' => $new_document,
                'old_folder' => $this->folder->get_folder(),
                'new_folder' => $target_folder,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
        }
	}
	
	/**
	 * This moves the document to another folder.
	 *
	 * @param KTAPI_Folder $ktapi_target_folder
	 * @param string $reason
	 * @param string $newname
	 * @param string $newfilename
	 */
	function move(&$ktapi_target_folder, $reason, $newname=null, $newfilename=null)
	{
		assert(!is_null($ktapi_target_folder));
		assert(is_a($ktapi_target_folder,'KTAPI_Folder'));
		
		if (empty($newname))
		{
			$newname=null;
		}
		if (empty($newfilename))
		{
			$newfilename=null;
		}		
		
		$user = $this->can_user_access_object_requiring_permission( $this->document, KTAPI_PERMISSION_DOCUMENT_MOVE);

		if (PEAR::isError($user))
		{
			return $user;
		}

		if ($this->document->getIsCheckedOut())
		{
			return new PEAR_Error(KTAPI_ERROR_DOCUMENT_CHECKED_OUT);
		}

		$target_folder = $ktapi_target_folder->get_folder();
		
		$result=  $this->can_user_access_object_requiring_permission(  $target_folder, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($result))
		{
			return $result;
		}
		
		if (!KTDocumentUtil::canBeMoved($this->document))
		{
			return new PEAR_Error('Document cannot be moved.');
		}

		$name = $this->document->getName();
		$clash = KTDocumentUtil::nameExists($target_folder, $name);
        if ($clash && !is_null($newname)) 
        {        
        	$name = $newname;
        	$clash = KTDocumentUtil::nameExists($target_folder, $name);
        }
        if ($clash) 
        {
        	return new PEAR_Error('A document with this title already exists in your chosen folder.  Please choose a different folder, or specify a new title for the moved document.');
        }
        
        $filename=$this->document->getFilename();
        $clash = KTDocumentUtil::fileExists($target_folder, $filename);

        if ($clash && !is_null($newname)) 
        {
			$filename = $newfilename;
            $clash = KTDocumentUtil::fileExists($target_folder, $filename);
        }
        if ($clash) 
        {
        	return new PEAR_Error('A document with this filename already exists in your chosen folder.  Please choose a different folder, or specify a new filename for the moved document.');
        }
        		
		DBUtil::startTransaction();
                 
        $res = KTDocumentUtil::move($this->document, $target_folder, $user, $reason);
        if (PEAR::isError($res)) 
        {
            DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $res );
        }
        
        $this->document->setName($name);
        $this->document->setFilename($filename);
                
        $res = $this->document->update();
        
        if (PEAR::isError($res)) 
        {
            DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR,$res );
        }

        DBUtil::commit();
	}	
	
	/**
	 * This changes the filename of the document.
	 *
	 * @param string $newname
	 */
	function renameFile($newname)
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($user))
		{
			return $user;
		}
		
		DBUtil::startTransaction();
		$res = KTDocumentUtil::rename($this->document, $newname, $user);
		if (PEAR::isError($res)) 
        {
            DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR,$res );
        }
        DBUtil::commit();
	}
	
	/**
	 * This changes the document type of the document.
	 *
	 * @param string $newname
	 */
	function change_document_type($documenttype)
	{
		$user = $this->can_user_access_object_requiring_permission( $this->document, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($user))
		{
			return $user;
		}
		
		$doctypeid = KTAPI::get_documenttypeid($documenttype);		 
		 
		if ($this->document->getDocumentTypeId() != $doctypeid)
		{
			DBUtil::startTransaction();
			$this->document->setDocumentTypeId($doctypeid);
			$res = $this->document->update();

			if (PEAR::isError($res))
			{
				DBUtil::rollback();
				return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR,$res );
			}
			DBUtil::commit();
		}
	}	
		
	/**
	 * This changes the title of the document.
	 *
	 * @param string $newname
	 */
	function rename($newname)
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($user))
		{
			return $user;
		}
		
		if ($this->document->getName() != $newname)
		{

			DBUtil::startTransaction();
			$this->document->setName($newname);
			$res = $this->document->update();

			if (PEAR::isError($res))
			{
				DBUtil::rollback();
				return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $res);
			}
			DBUtil::commit();
		}
	}
	
	/**
	 * This flags the document as 'archived'.
	 *
	 * @param string $reason
	 */
	function archive($reason)
	{
		$user = $this->can_user_access_object_requiring_permission( $this->document, KTAPI_PERMISSION_WRITE);

		if (PEAR::isError($user))
		{
			return $user;
		}

		list($permission, $user) = $perm_and_user;	
		
		DBUtil::startTransaction();
		$this->document->setStatusID(ARCHIVED);
        $res = $this->document->update();
        if (($res === false) || PEAR::isError($res)) {
           DBUtil::rollback();
           return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $res);
        }
        
        $oDocumentTransaction = & new DocumentTransaction($this->document, sprintf(_kt('Document archived: %s'), $reason), 'ktcore.transactions.update');
        $oDocumentTransaction->create();
        
        DBUtil::commit();

        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('archive', 'postValidate');
        foreach ($aTriggers as $aTrigger) 
        {
            $sTrigger = $aTrigger[0];
            $oTrigger = new $sTrigger;
            $aInfo = array(
                'document' => $this->document,
            );
            $oTrigger->setInfo($aInfo);
            $ret = $oTrigger->postValidate();
        }	
	}	
	
	/**
	 * This starts a workflow on a document.
	 *
	 * @param string $workflow
	 */
	function start_workflow($workflow)
	{
		$user = $this->can_user_access_object_requiring_permission( $this->document, KTAPI_PERMISSION_WORKFLOW);

		if (PEAR::isError($user))
		{
			return $user;
		}
		
		$workflowid = $this->document->getWorkflowId();
		
		if (!empty($workflowid))
		{
			return new PEAR_Error('A workflow is already defined.');
		}
		
		$workflow = KTWorkflow::getByName($workflow);
		if (is_null($workflow) || PEAR::isError($workflow))
		{
			return new KTAPI_Error(KTAPI_ERROR_WORKFLOW_INVALID, $workflow);
		}
		
		DBUtil::startTransaction();
		$result = KTWorkflowUtil::startWorkflowOnDocument($workflow, $this->document);
		if (is_null($result) || PEAR::isError($result))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_WORKFLOW_INVALID, $result);
		}
		DBUtil::commit();
	}
	
	/**
	 * This deletes the workflow on the document.
	 *
	 */
	function delete_workflow()
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_WORKFLOW);

		if (PEAR::isError($user))
		{
			return $user;
		}
				
		$workflowid=$this->document->getWorkflowId();
		if (!empty($workflowid))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_NOT_IN_PROGRESS);
		}
				
		DBUtil::startTransaction();
		$result = KTWorkflowUtil::startWorkflowOnDocument(null, $this->document);
		if (is_null($result) || PEAR::isError($result))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_WORKFLOW_INVALID,$result);
		}
		DBUtil::commit();
	}
	
	/**
	 * This performs a transition on the workflow
	 *
	 * @param string $transition
	 * @param string $reason
	 */
	function perform_workflow_transition($transition, $reason)
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_WORKFLOW);

		if (PEAR::isError($user))
		{
			return $user;
		}
				
		$workflowid=$this->document->getWorkflowId();
		if (empty($workflowid))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_NOT_IN_PROGRESS);
		}	
		
		$transition = &KTWorkflowTransition::getByName($transition);
		if (is_null($transition) || PEAR::isError($transition))
		{
			return new KTAPI_Error(KTAPI_ERROR_WORKFLOW_INVALID, $transition);
		}
				
		DBUtil::startTransaction();
		$result = KTWorkflowUtil::performTransitionOnDocument($transition, $this->document, $user, $reason);
		if (is_null($result) || PEAR::isError($result))
		{
			DBUtil::rollback();
			return new KTAPI_Error(KTAPI_ERROR_WORKFLOW_INVALID, $transition);
		}
		DBUtil::commit();	
	}		
	
	
	
	/**
	 * This returns all metadata for the document.
	 *
	 * @return array
	 */
	function get_metadata()
	{
		 $doctypeid = $this->document->getDocumentTypeID();
		 $fieldsets = (array) KTMetadataUtil::fieldsetsForDocument($this->document, $doctypeid);
		 
		 $results = array();
		 
		 foreach ($fieldsets as $fieldset) 
		 {
		 	if ($fieldset->getIsConditional()) {	/* this is not implemented...*/	continue;	}
		 	
		 	$fields = $fieldset->getFields();
		 	$result = array('fieldset' => $fieldset->getName(),
		 					'description' => $fieldset->getDescription());
		 	
		 	$fieldsresult = array();
		 	 
            foreach ($fields as $field) 
            {                
                $value = 'n/a';
                 
				$fieldvalue = DocumentFieldLink::getByDocumentAndField($this->document, $field);
                if (!is_null($fieldvalue) && (!PEAR::isError($fieldvalue))) 
                {
                	$value = $fieldvalue->getValue();
                }
                
                $controltype = 'string';
                if ($field->getHasLookup()) 
                {
                	$controltype = 'lookup';
                    if ($field->getHasLookupTree())
                    {
                    	$controltype = 'tree';
                    }
                }
                
                switch ($controltype)
                {
                	case 'lookup':
                		$selection = KTAPI::get_metadata_lookup($field->getId());		
                		break;
                	case 'tree':
                		$selection = KTAPI::get_metadata_tree($field->getId());
                		break;
                	default:
                		$selection= array();
                }

               
                $fieldsresult[] = array(
                	'name' => $field->getName(),
                	'required' => $field->getIsMandatory(),
                	'value' => $value,
                    'description' => $field->getDescription(),
                    'control_type' => $controltype,
                    'selection' => $selection
                                  
                );
                
            }
            $result['fields'] = $fieldsresult;
            $results [] = $result;	
		 }		 
		 
		 return $results;
	}
	
	/**
	 * This updates the metadata on the file. This includes the 'title'.
	 *
	 * @param array This is an array containing the metadata to be associated with the file.
	 */
	function update_metadata($metadata)
	{		 
		global $default;
		 $packed = array();
		 
		 foreach($metadata as $fieldset_metadata)
		 {
		 	if (is_array($fieldset_metadata))
		 	{
		 		$fieldsetname=$fieldset_metadata['fieldset'];
		 		$fields=$fieldset_metadata['fields'];
		 	}
		 	elseif (is_a($fieldset_metadata, 'stdClass'))
		 	{
		 		$fieldsetname=$fieldset_metadata->fieldset;
		 		$fields=$fieldset_metadata->fields;
		 	}
		 	else
		 	{
		 		$default->log->debug("unexpected fieldset type");
		 		continue;
		 	}

		 	$fieldset = KTFieldset::getByName($fieldsetname);
		 	if (is_null($fieldset) || PEAR::isError($fieldset))
		 	{
		 		$default->log->debug("could not resolve fieldset: $fieldsetname");
		 		// exit graciously
		 		continue;
		 	}
		 	
		 	foreach($fields as $fieldinfo)
		 	{
		 		if (is_array($fieldinfo))
		 		{
		 			$fieldname = $fieldinfo['name'];
		 			$value = $fieldinfo['value'];
		 		}
		 		elseif (is_a($fieldinfo, 'stdClass'))
		 		{
		 			$fieldname = $fieldinfo->name;
		 			$value = $fieldinfo->value;
		 		}
		 		else
		 		{
		 			$default->log->debug("unexpected fieldinfo type");
		 			continue;
		 		}

		 		$field = DocumentField::getByFieldsetAndName($fieldset, $fieldname);
		 		if (is_null($field) || PEAR::isError($fieldset))
		 		{
		 			$default->log->debug("could not resolve field: $fieldname");
		 			// exit graciously
		 			continue;
		 		}
		 		
		 		$packed[] = array($field, $value);
		 	}		 	
		 }
		 
		 DBUtil::startTransaction();
		 $result = KTDocumentUtil::saveMetadata($this->document, $packed);
        
		 if (is_null($result))
		 {
		 	DBUtil::rollback();
		 	return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR);
		 }
		 if (PEAR::isError($result)) 
		 {
		 	DBUtil::rollback();
		 	return new KTAPI_Error(sprintf(_kt("Unexpected validation failure: %s."), $result->getMessage()));	
		 }
		 DBUtil::commit();
	}
	

	/**
	 * This returns a workflow transition
	 *
	 * @return array
	 */
	function get_workflow_transitions()
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_WORKFLOW);

		if (PEAR::isError($user))
		{
			return $user;
		}

		$workflowid=$this->document->getWorkflowId();
		if (empty($workflowid))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_NOT_IN_PROGRESS);
		}		
				
		$result = array();
		
		$transitions = KTWorkflowUtil::getTransitionsForDocumentUser($this->document, $user);
		if (is_null($transitions) || PEAR::isError($transitions))
		{
			return new KTAPI_Error(KTAPI_ERROR_WORKFLOW_INVALID, $transitions);
		}
		foreach($transitions as $transition)
		{
			$result[] = $transition->getName();
		}
		
		return $result;		 
	}
	
	/**
	 * This returns the current workflow state
	 *
	 * @return string
	 */
	function get_workflow_state()
	{
		$user = $this->can_user_access_object_requiring_permission(  $this->document, KTAPI_PERMISSION_WORKFLOW);

		if (PEAR::isError($user))
		{
			return $user;
		}

		$workflowid=$this->document->getWorkflowId();
		if (empty($workflowid))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_NOT_IN_PROGRESS);
		}		
		
		$result = array();
		
		$state = KTWorkflowUtil::getWorkflowStateForDocument($this->document);
		if (is_null($state) || PEAR::isError($state))
		{
			return new PEAR_Error(KTAPI_ERROR_WORKFLOW_INVALID);
		}
		
		$statename = $state->getName();
		
		return $statename;			
		
	}
	
	/**
	 * This returns detailed information on the document.
	 *
	 * @return array
	 */	
	function get_detail()
	{
		$detail = array();
		$document = $this->document;

		$detail['title'] = $document->getName();

		$documenttypeid=$document->getDocumentTypeID();
		if (is_numeric($documenttypeid))
		{
			$documenttype = DocumentType::get($documenttypeid);

			$documenttype=$documenttype->getName();
		}
		else 
		{
			$documenttype = '* unknown *';
		}
		$detail['document_type'] = $documenttype;
		
		$detail['version'] = $document->getVersion();
		$detail['filename'] = $document->getFilename();

		$detail['created_date'] = $document->getCreatedDateTime();

		$userid = $document->getCreatorID();
		if (is_numeric($userid))
		{
			$user = User::get($userid);
			$username=(is_null($user) || PEAR::isError($user))?'* unknown *':$user->getName();
		}
		else 
		{
			$username='n/a';
		}
		$detail['created_by'] = $username;
		$detail['updated_date'] = $document->getLastModifiedDate();

		$userid = $document->getModifiedUserId();
		if (is_numeric($userid))
		{
			$user = User::get($userid);
			$username=(is_null($user) || PEAR::isError($user))?'* unknown *':$user->getName();			
		}
		else 
		{
			$username='n/a';
		}
		$detail['updated_by'] = $username;
		$detail['document_id'] = (int) $document->getId();
		$detail['folder_id'] = (int) $document->getFolderID();

		$workflowid = $document->getWorkflowId();
		if (is_numeric($workflowid))
		{
			$workflow = KTWorkflow::get($workflowid);
			$workflowname=(is_null($workflow) || PEAR::isError($workflow))?'* unknown *':$workflow->getName();
		}
		else 
		{
			$workflowname='n/a';
		}
		$detail['workflow'] = $workflowname;

		$stateid = $document->getWorkflowStateId();
		if (is_numeric($stateid))
		{
			$state = KTWorkflowState::get($stateid);
			$workflowstate=(is_null($state) || PEAR::isError($state))?'* unknown *':$state->getName();
		}
		else 
		{
			$workflowstate = 'n/a';
		}
		$detail['workflow_state']=$workflowstate;

		$userid = $document->getCheckedOutUserID();
		 
		if (is_numeric($userid))
		{
			$user = User::get($userid);
			$username=(is_null($user) || PEAR::isError($user))?'* unknown *':$user->getName();
		}
		else 
		{
			$username = 'n/a';
		}
		$detail['checkout_by'] = $username;
		
		$detail['full_path'] = $this->ktapi_folder->get_full_path() . '/' . $this->get_title();
		
		return $detail;
	}
	
	function get_title()
	{
		return $this->document->getDescription();
	}
	
	/**
	 * This does a download of a version of the document.
	 *
	 * @param string $version
	 */
	function download($version=null)
	{		
		$storage =& KTStorageManagerUtil::getSingleton();
        $options = array();
		
		
        $oDocumentTransaction = & new DocumentTransaction($this->document, 'Document downloaded', 'ktcore.transactions.download', $aOptions);
        $oDocumentTransaction->create();
	}
	
	/**
	 * This returns the transaction history for the document.
	 *
	 * @return array
	 */
	function get_transaction_history()
	{		
        $sQuery = 'SELECT DTT.name AS transaction_name, U.name AS username, DT.version AS version, DT.comment AS comment, DT.datetime AS datetime ' .
            'FROM ' . KTUtil::getTableName('document_transactions') . ' AS DT INNER JOIN ' . KTUtil::getTableName('users') . ' AS U ON DT.user_id = U.id ' .
            'INNER JOIN ' . KTUtil::getTableName('transaction_types') . ' AS DTT ON DTT.namespace = DT.transaction_namespace ' .
            'WHERE DT.document_id = ? ORDER BY DT.datetime DESC';
        $aParams = array($this->documentid);

        $transactions = DBUtil::getResultArray(array($sQuery, $aParams));
        if (is_null($transactions) || PEAR::isError($transactions)) 
        {
        	return new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $transactions  );
        }

        return $transactions;
	}
	
	/**
	 * This returns the version history on the document.
	 *
	 * @return array
	 */
	function get_version_history()
	{
		$metadata_versions = KTDocumentMetadataVersion::getByDocument($this->document);
		
        $versions = array();
        foreach ($metadata_versions as $version) 
        {
        	$document = &Document::get($this->documentid, $version->getId());
        	
        	$version = array();
        	
        	$userid = $document->getModifiedUserId();			 
			$user = User::get($userid);		 
        	
        	$version['user'] = $user->getName();
        	$version['metadata_version'] = $document->getMetadataVersion();
        	$version['content_version'] = $document->getVersion();
        	
            $versions[] = $version;
        }
        return $versions;
	}

	/**
	 * This expunges a document from the system.
	 *
	 * @access public
	 */
	function expunge()
	{
		if ($this->document->getStatusID() != 3)
		{
			return new PEAR_Error('You should not purge this');
		}
		DBUtil::startTransaction();
		
		$transaction = & new DocumentTransaction($this->document, "Document expunged", 'ktcore.transactions.expunge');
		
        $transaction->create();
        
        $this->document->delete();
        
        $this->document->cleanupDocumentData($this->documentid);	
		
		$storage =& KTStorageManagerUtil::getSingleton();
		 
		$result= $storage->expunge($this->document);

		DBUtil::commit();
	}
	
	/**
	 * This expunges a document from the system.
	 *
	 * @access public
	 */
	function restore()
	{
		DBUtil::startTransaction();
		
		$storage =& KTStorageManagerUtil::getSingleton();
		
		$folder = Folder::get($this->document->getRestoreFolderId());
		if (PEAR::isError($folder)) 
		{
			$this->document->setFolderId(1);
			$folder = Folder::get(1);
		}
		else 
		{
			$this->document->setFolderId($this->document->getRestoreFolderId());
		}

		$storage->restore($this->document);
		 
		$this->document->setStatusId(LIVE);
		$this->document->setPermissionObjectId($folder->getPermissionObjectId());
		$res = $this->document->update();

		$res = KTPermissionUtil::updatePermissionLookup($this->document);
		
		$user = $this->ktapi->get_user();

		$oTransaction = new DocumentTransaction($this->document, 'Restored from deleted state by ' . $user->getName(), 'ktcore.transactions.update');
		$oTransaction->create();

		DBUtil::commit();
	}
}
?>