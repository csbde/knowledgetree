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

require_once('search/SearchCommandParser.php');
require_once('search/SearchCommandLexer.php');
require_once('search/fieldRegistry.inc.php');
require_once('search/expr.inc.php');
require_once(KT_LIB_DIR . '/security/Permission.inc');

// TODO: move standalone functions into a class.... what was I thinking?

function rank_compare($a, $b)
{
	if ($a->Rank == $b->Rank)
	{
		if ($a->Title == $b->Title)
			return 0;
		// we'll show docs in ascending order by name
        // strtolower to avoid case issues in sorting
		return (strtolower($a->Title) < strtolower($b->Title)) ? -1 : 1;
	}
	// we want to be in descending order
	return ($a->Rank > $b->Rank)?-1:1;
}

function search_alias_compare($a, $b)
{
	if ($a['alias'] == $b['alias']) return 0;
	return ($a['alias'] < $b['alias'])?-1:1;
}

function searchfix($str)
{
    return str_replace(array("\n","\r"), array('',''), addslashes($str));
}

// TODO: replace manual json construction with json_encode().

class SearchHelper
{
	public static function correctPath($path)
	{
		if (OS_WINDOWS)
		{
			return str_replace('/','\\', $path);
		}
		else
		{
			return str_replace('\\','/', $path);
		}
	}

	public static function checkOpenOfficeAvailablity()
	{
		$config =& KTConfig::getSingleton();
		$ooHost = $config->get('openoffice/host', 'localhost');
		$ooPort = $config->get('openoffice/port', 8100);

		$connection = @fsockopen($ooHost, $ooPort,$errno, $errstr, 2);
		if (false === $connection)
		{
			return  sprintf(_kt("Cannot connect to Open Office Server on host '%s:%s'."), $ooHost, $ooPort);
		}
		fclose($connection);

		return null;
	}

	public static function getSavedSearchEvents()
	{
		// TODO
		$sql = "";
	}

	public static function getJSdocumentTypesStruct($documenttypes = null)
	{
		if (is_null($documenttypes))
		{
			$documenttypes = SearchHelper::getDocumentTypes();
		}
		$dt=0;
		$documenttypes_str = '[';
		foreach($documenttypes as $user)
		{
			if ($dt++ > 0) $documenttypes_str .= ',';
			$id=$user['id'];
			$name=searchfix($user['name']);

			$documenttypes_str .= "\n\t{id: \"$id\", name: \"$name\"}";
		}
		$documenttypes_str .= ']';
		return $documenttypes_str;

	}

	public static function getJSmimeTypesStruct($mimetypes = null)
	{
		if (is_null($mimetypes))
		{
			$mimetypes = SearchHelper::getMimeTypes();
		}
		$mt=0;
		$mimetypes_str = '[';
		foreach($mimetypes as $user)
		{
			if ($mt++ > 0) $mimetypes_str .= ',';

			$name=$user['name'];

			$mimetypes_str .= "\n\t\"$name\"";
		}
		$mimetypes_str .= ']';

		return $mimetypes_str;
	}

	public static function getJSusersStruct($users = null)
	{
		if (is_null($users))
		{
			$users = SearchHelper::getUsers();
		}

		$uo=0;
		$users_str = '[';
		foreach($users as $user)
		{
			if ($uo++ > 0) $users_str .= ',';
			$id=$user['id'];
			$name=searchfix($user['name']);

			$users_str .= "\n\t{id: \"$id\", name: \"$name\"}";
		}
		$users_str .= ']';

		return $users_str;
	}

	public static function getJSfieldsStruct($fields = null)
	{
		if (is_null($fields))
		{
			$fields = SearchHelper::getSearchFields();
		}
        $fields_str = '[';
		$fo=0;
		foreach($fields as $field)
		{
			if ($fo++ > 0) $fields_str .= ',';
			$alias = searchfix($field['alias']);
			$display = searchfix($field['display']);
			$type = $field['type'];
			$fields_str .= "\n\t{alias: \"$alias\", name: \"$display\", type:\"$type\"}";
		}
		$fields_str .= ']';

		return $fields_str;
	}

	public static function getJSworkflowStruct($workflows = null)
	{
		if (is_null($workflows))
		{
			$workflows = SearchHelper::getWorkflows();
		}

		$workflow_str = '[';
        $wo=0;
        foreach($workflows as $workflow)
        {
        	if ($wo++ > 0) $workflow_str .= ',';
        	$wid = $workflow['id'];
        	$name = searchfix($workflow['name']);

        	$workflow_str .= "\n\t{id:\"$wid\", name: \"$name\", states: [ ";

        	$result['workflows'][$wid] = $workflow;
        	$states = SearchHelper::getWorkflowStates($wid);
        	$result['workflows'][$wid]['states'] = array();
        	$so=0;
        	foreach($states as $state)
        	{
        		if ($so++>0) $workflow_str .= ',';
				$sid = $state['id'];
				$name=searchfix($state['name']);
				$result['workflows'][$wid]['states'][$sid] = $state;
				$workflow_str .= "\n\t\t{id:\"$wid\", name: \"$name\"}";
        	}
        	$workflow_str .= ']}';
        }
        $workflow_str .= ']';

        return $workflow_str;
	}

	public static function getJSfieldsetStruct($fieldsets = null)
	{
		if (is_null($fieldsets))
		{
			$fieldsets = SearchHelper::getFieldsets();
		}

		$fieldset_str = '[';
		$fso=0;
        foreach($fieldsets as $fieldset)
        {
        	$fsid=$fieldset['id'];
        	$name = searchfix($fieldset['name']);
        	$desc = searchfix($fieldset['description']);
        	if ($fso++>0) $fieldset_str .= ',';
        	$fieldset_str .= "\n\t{id:\"$fsid\",name:\"$name\",description:\"$desc\", fields: [";


        	$result['fieldsets'][$fsid] = $fieldset;
        	$fields = SearchHelper::getFields($fsid);
			$result['fieldsets'][$fsid]['fields'] = array();
			$fo=0;
        	foreach($fields as $field)
        	{
        		if ($fo++ >0) $fieldset_str .= ',';
			$fid = $field['id'];
			$name= searchfix($field['name']);
			$desc = searchfix($field['description']);
			$datatype=$field['datatype'];
			$control=$field['control'];
				
			if(KTPluginUtil::pluginIsActive('inet.multiselect.lookupvalue.plugin') && isset($field['inetlookup_type']) && $field['inetlookup_type'] != "") {
				$inetlookup_type=$field['inetlookup_type'];
				$fieldset_str .= "\n\t\t{id:\"$fid\", name:\"$name\", description:\"$desc\", datatype:\"$datatype\", control:\"$control\", inetlookup_type:\"$inetlookup_type\" , options: [";
			} else {
				$fieldset_str .= "\n\t\t{id:\"$fid\", name:\"$name\", description:\"$desc\", datatype:\"$datatype\", control:\"$control\", options: [";		
			}
				
        		$options = $field['options'];
        		$oo = 0;
        		if (!is_array($options))
        		{
        			$options = array();
        		}
        		foreach($options as $option)
        		{
        			if ($oo++ > 0) $fieldset_str .= ',';
        			$oid = $option['id'];
					$name= searchfix($option['name']);
        			$fieldset_str .= "\n\t\t\t{id: \"$oid\", name: \"$name\"}";
        		}
        		$fieldset_str .= ']}';
        		$result['fieldsets'][$fsid]['fields'][$fid] = $field;
        	}
        	$fieldset_str .= ']}';

        }
        $fieldset_str .= ']';

        return $fieldset_str;
	}


	/**
	* This method deletes the saved search based on the saved search id
	*
	* @author KnowledgeTree Team
	* @access public
	* @static
	* @param integer $searchID The id of the saved search
	* @return void
	*/
	public static function deleteSavedSearch($searchID)
	{
		$sysAdmin=Permission::userIsSystemAdministrator();

		$sql = "DELETE FROM search_saved WHERE type='S' AND id=$searchID";
		if (!$sysAdmin)
		{
			$sql .= " AND user_id='".$_SESSION['userID']."'";
		}

		DBUtil::runQuery($sql);
	}

	/**
	* This method returns a list of saved searches based on the name of the search
	*
	* @author KnowledgeTree Tean
	* @access public
	* @static
	* @param string $name The name of the saved search
	* @return array|MIXED $results SUCCESS - The list of searches | FAILURE - a pear error object
	*/
	public static function checkForSavedSearch($name)
	{
		$txtName = sanitizeForSQL($name);
		$sql = "select 1 from search_saved where name='$txtName'";
		$results = DBUtil::getResultArray($sql);

		return $results;
	}

	/**
	* This method gets the saved search based on the id
	*
	* @author KnowledgeTree Team
	* @access public
	* @static
	* @param integer $searchID The saved search id
	* @return array|object $result SUCCESS - The saved search | FAILURE - a pear error object
	*/public static function getSavedSearch($searchID)
	{
		// need to test for broken db configuration so that the queries dont fail
		// and so that we can be redirected to the db error page
		global $default;
		if (is_null($default->_db) || PEAR::isError($default->_db)) return array();

		$sql = "SELECT id, name, expression FROM search_saved WHERE id='$searchID'";

		$result = DBUtil::getResultArray($sql);
		if(is_null($result) || PEAR::isError($result)){
		    $result = new PEAR_Error('Invalid saved search result');
		}
		return $result;
	}

	/**
	* This method saves the search query to the database
	*
	* @author KnowledgeTree Tean
	* @access public
	* @static
	* @param string $name The name of the saved search query
	* @param string $query The query string
	* @param string $userID The id of the user saving the query
	* @return string|object $result SUCCESS - The id of the record inserted | FAILURE - a pear error object
	*/
	public static function saveSavedSearch($name, $query, $userID)
	{
		// autoInsert does escaping...
		$values = array(
		'name'=>$name,
		'expression'=>$query,
		'type'=>'S',
		'shared'=>0,
		'user_id' => $userID
		);

		$result = DBUtil::autoInsert('search_saved', $values);

		return $result;
	}

	public static function getSavedSearches($userID)
	{

		// need to test for broken db configuration so that the queries dont fail
		// and so that we can be redirected to the db error page
		// TODO: maybe best to have a special db error page rather than the default template when logged in

		global $default;
		if (is_null($default->_db) || PEAR::isError($default->_db)) return array();

		$sql = "SELECT id, name FROM search_saved WHERE type='S'";

		// if we are not the system admin, then we get only ours or shared searches
		if (!Permission::userIsSystemAdministrator($userID))
		{
			$sql .= "  and ( user_id=$userID OR shared=1 ) ";
		}

		$rs = DBUtil::getResultArray($sql);
		return $rs;
	}

	public static function getSearchFields()
	{
		$registry = ExprFieldRegistry::getRegistry();

		$fields = $registry->getFields();

		$results = array();
		foreach($fields as $field )
		{
			$type = $field->getInputRequirements();
			$type = $type['value']['type'];
			$results[] = array('alias'=>$field->getAlias(), 'display'=>$field->getDisplay(), 'type'=>$type);
		}
		usort($results, search_alias_compare);
		return $results;
	}

	public static function getFolder($folderID, $userid)
	{
		$folder = Folder::get($folderID + 0);
		if (PEAR::isError($folder))
		{
			return $folder;
		}

		if (!Permission::userHasFolderReadPermission($folder))
		{
			return new PEAR_Error(_kt('no permission to read folder'));
		}

		$sql = "SELECT id, name FROM folders WHERE parent_id=$folderID ORDER BY name";
		$rs = DBUtil::getResultArray($sql);
		if (PEAR::isError($rs))
		{
			return $rs;
		}

		$folders = array();

		foreach($rs as $folder)
		{
			$fobj = Folder::get($folder['id']);

			if (Permission::userHasFolderReadPermission($fobj))
			{
				$folders[] = $folder;
			}
		}
		return $folders;
	}

	public static function getFields($fieldsetID)
	{
		if ($fieldsetID < 0)
		{
			$documentTypeID = sanitizeForSQL(-$fieldsetID);

			if(KTPluginUtil::pluginIsActive('inet.multiselect.lookupvalue.plugin')) {
				$sql = "SELECT
						df.id, df.name, df.data_type, df.has_lookup,df.has_inetlookup, df.inetlookup_type , df.has_lookuptree, df.description
					FROM
						document_type_fields_link dtfl
				        	INNER JOIN  document_fields df on dtfl.field_id=df.id
					WHERE
						dtfl.document_type_id=$documentTypeID
					ORDER BY
						df.name";
			} else {	
					
						$sql = "SELECT
									df.id, df.name, df.data_type, df.has_lookup, df.has_lookuptree, df.description
								FROM
									document_type_fields_link dtfl
									INNER JOIN  document_fields df on dtfl.field_id=df.id
								WHERE
									dtfl.document_type_id=$documentTypeID
								ORDER BY
									df.name";		
					}
		}			
		else
		{
			$fieldsetID = sanitizeForSQL($fieldsetID);
			if(KTPluginUtil::pluginIsActive('inet.multiselect.lookupvalue.plugin'))
			{	
				$sql = "SELECT id, name, data_type, has_lookup,has_inetlookup, inetlookup_type, has_lookuptree, description FROM document_fields WHERE parent_fieldset=$fieldsetID ORDER BY name";
			} else {
				$sql = "SELECT id, name, data_type, has_lookup, has_lookuptree, description FROM document_fields WHERE parent_fieldset=$fieldsetID ORDER BY name";		
			}
			
		}

		$rs = DBUtil::getResultArray($sql);
		if (PEAR::isError($rs))
		{
			return $rs;
		}
		if (count($rs) == 0)
		{
			return new PEAR_Error(_kt('Fieldset was not found'));
		}

		$result=array();
		foreach($rs as $item)
		{
			$fieldid=$item['id'];
			$type='normal';
			$options = array();
			$haslookup =$item['has_lookup'] + 0 > 0;
			$hastree = ($item['has_lookuptree']+0 > 1);
			$hasinetlookup=$item['has_inetlookup'] + 0 > 0;
			
			if ($haslookup || $hastree || $hasinetlookup)
			{
				$type = 'lookup';
				$sql = "select id, name from metadata_lookup where document_field_id=$fieldid";
				$options = DBUtil::getResultArray($sql);

			}
			
			$inetlookup_type = "";
			if($hasinetlookup)
			{
				$type = 'inetlookup';
				$inetlookup_type = $item['inetlookup_type'];
			}
			
			/*if ($hastree)
			{
				$type = 'lookup';
				$sql = "select id, name, metadata_lookup_tree_parent as parent from metadata_lookup_tree where document_field_id=$fieldid";
				$options = DBUtil::getResultArray($sql);
			}*/

			if ($item['data_type'] == 'USERLIST')
			{
				$type = 'lookup';
				$sql = "SELECT id, name from users WHERE disabled=0";
				$options = DBUtil::getResultArray($sql);
			}

			$ritem = array(
				'id'=>$fieldid,
				'name'=>$item['name'],
				'description'=>$item['description'],
				'datatype'=>$item['data_type'],
				'control'=>$type,
				'inetlookup_type' => $inetlookup_type,
				'options'=>$options
			);

			$result[]= $ritem;
		}
		return $result;
	}

	public static function getFieldsets()
	{
		$sql = "SELECT id, name, description FROM fieldsets WHERE disabled=0 ORDER BY name";
		$rs = DBUtil::getResultArray($sql);

		return $rs;
	}

	public static function getDocumentTypeFieldsets($documentTypeID)
	{
		$documentTypeID = sanitizeForSQL($documentTypeID);
		$sql = "SELECT
					fs.id, fs.name, fs.description
				FROM
					fieldsets fs LEFT JOIN document_type_fieldsets_link dtfl ON dtfl.fieldset_id=fs.id
				WHERE
					fs.disabled=0 AND (dtfl.document_type_id=$documentTypeID OR fs.is_generic=1)";
		$rs = DBUtil::getResultArray($sql);

		return $rs;
	}

	public static function getDocumentTypes()
	{
		$sql = "SELECT id, name from document_types_lookup WHERE disabled=0 ORDER BY name";
		$rs = DBUtil::getResultArray($sql);
		return $rs;
	}

	public static function getMimeTypes() {
		$sql = "SELECT DISTINCT mimetypes as name FROM mime_types ORDER BY mimetypes ";
		$rs = DBUtil::getResultArray($sql);
		return $rs;
	}

	public static function getWorkflows()
	{
		$sql = "SELECT id, human_name as name FROM workflows WHERE enabled=1 ORDER BY human_name";
		$rs = DBUtil::getResultArray($sql);
		return $rs;
	}

	public static function getUsers()
	{
		$sql = "SELECT id, name FROM users WHERE disabled=0 ORDER BY name";
		$rs = DBUtil::getResultArray($sql);
		return $rs;
	}

	public static function getWorkflowStates($workflowid)
	{
		$sql = "SELECT id,human_name as name FROM workflow_states WHERE workflow_id=$workflowid ORDER BY id";
		$rs = DBUtil::getResultArray($sql);
		return $rs;
	}

}


function getExpressionLocalityString($expr_str, $locality, $length, $start_offset=10)
{
    if ($locality - $start_offset < 0)
    {
        $locality  = 0;
    }
    else
    {
        $locality -= $start_offset;
    }

    return substr($expr_str, $locality, $length);
}

/**
 * This parses a query.
 *
 * @param OpExpr $expr_str
 * @return array of MatchResult
 */
function parseExpression($expr_str)
{
    $parser = new SearchCommandParser();
    $lexer = new SearchCommandLexer($expr_str);

//    $parser->PrintTrace();
    $use_internal=false;

    try
    {
        while ($lexer->yylex())
        {
            //print "\n" . $lexer->value  . "\n";

            $parser->doParse($lexer->token, $lexer->value);

            if (!$parser->isExprOk())
            {
                $use_internal=true;
                $expr_str=getExpressionLocalityString($expr_str, $lexer->offset, 20);
                throw new Exception(sprintf(_kt("Parsing problem near '%s' in '%s' of expression."),$lexer->value,$expr_str));
            }
        }

        // we are now done
        $parser->doParse(0, 0);

        if (!$parser->isExprOk())
        {
            $use_internal=true;
            $expr_str=getExpressionLocalityString($expr_str, $lexer->offset, 20);
            throw new Exception(sprintf(_kt("There is a problem parsing the expression '%s'"),$expr_str));
        }

    }
    catch(ResolutionException $e)
    {
        throw $e;
    }
    catch(Exception $e)
    {
        if ($use_internal)
        {
            throw $e;
        }
        $expr_str=getExpressionLocalityString($expr_str, $lexer->offset, 20);
        throw new Exception(sprintf(_kt("Parsing problem near '%s' of expression '%s'."), $lexer->value, $expr_str));
    }

    return $parser->getExprResult();
}

function processSearchExpression($query)
{
		try
    	{
    		$expr = parseExpression($query);

    		$rs = $expr->evaluate(ExprContext::DOCUMENT);
    		$rs = $rs['docs'];

    		if(empty($rs)){
    		    return array();
    		}

    		usort($rs, 'rank_compare');

    		$results = array();
    		foreach($rs as $hit)
    		{
    			 $item = array(
						'document_id' => (int) $hit->DocumentID,

						'custom_document_no' => 'n/a',
		                'oem_document_no' => (string) $hit->OemDocumentNo,

						'relevance' => (float) $hit->Rank,
        				'text' => (string)  $noText?'':urlencode($hit->Text),

        				'title' => (string) $hit->Title,
        				'document_type'=> $hit->DocumentType,
        				'fullpath' => (string) $hit->FullPath,
        				'filename' => (string) $hit->Filename,
        				'filesize' => (int) $hit->Filesize,
        				'folder_id' => (int) $hit->FolderId,

						'created_by' => (string) $hit->CreatedBy,
						'created_date' => (string) $hit->DateCreated,

						'modified_by' => (string) $hit->ModifiedBy,
						'modified_date' => (string) $hit->DateModified,

						'checked_out_by' => (string) $hit->CheckedOutUser,
        				'checked_out_date' => (string) $hit->DateCheckedOut,

						'owned_by' => (string) $hit->Owner,

        				'version' => (float) $hit->Version,
        				'is_immutable'=> (bool) $hit->Immutable,
        				'permissions'=> $hit->Permissions,

        				'workflow' => (string) $hit->WorkflowOnly,
        				'workflow_state' => (string) $hit->WorkflowStateOnly,

        				'mime_type' => (string) $hit->MimeType,
        				'mime_icon_path' => (string) $hit->MimeIconPath,
        				'mime_display' => (string) $hit->MimeDisplay,

						'storage_path' => (string) $hit->StoragePath,

						'status' => (string) $hit->Status,

        				'is_available' => (bool) $hit->IsAvailable,

    				);

    				$results[] = $item;

    		}
    		return $results;
    	}
    	catch(Exception $e)
    	{
    		return new PEAR_Error(_kt('Could not process query.')  . $e->getMessage());
    	}
}

function resolveSearchShortcuts($result)
{
    $oPermission =& KTPermission::getByName('ktcore.permissions.read');
    $permId = $oPermission->getID();

    $oUser = User::get($_SESSION['userID']);
    $aPermissionDescriptors = KTPermissionUtil::getPermissionDescriptorsForUser($oUser);
    $sPermissionDescriptors = empty($aPermissionDescriptors)? -1: implode(',', $aPermissionDescriptors);

    $documentIds = implode(',',array_keys($result['docs']));
    $linkedDocuments = array();
    if (!empty($documentIds))
    {
        $sql = "SELECT d.id, d.linked_document_id from documents d ";
        $sql .= 'INNER JOIN permission_lookups AS PL ON d.permission_lookup_id = PL.id '. "\n";
        $sql .= 'INNER JOIN permission_lookup_assignments AS PLA ON PL.id = PLA.permission_lookup_id AND PLA.permission_id = '.$permId. " \n";
        $sql .= " WHERE d.linked_document_id in ($documentIds) AND PLA.permission_descriptor_id IN ($sPermissionDescriptors)";

        $rs = DBUtil::getResultArray($sql);

        foreach($rs as $row)
        {
            $id = $row['id'];
            $linked_id = $row['linked_document_id'];

            $result['shortdocs'][$id] = new DocumentShortcutResultItem($id, $result['docs'][$linked_id]);
        }
    }

    $folderIds = implode(',',array_keys($result['folders']));
    $linkedFolders = array();

    if (!empty($folderIds))
    {

        $sql = "SELECT f.id, f.parent_id, f.linked_folder_id, f.full_path from folders f ";
        $sql .= 'INNER JOIN permission_lookups AS PL ON f.permission_lookup_id = PL.id '. "\n";
        $sql .= 'INNER JOIN permission_lookup_assignments AS PLA ON PL.id = PLA.permission_lookup_id AND PLA.permission_id = '.$permId. " \n";
        $sql .= " WHERE f.linked_folder_id in ($folderIds) AND PLA.permission_descriptor_id IN ($sPermissionDescriptors)";

        $rs = DBUtil::getResultArray($sql);

        foreach($rs as $row)
        {
            $id = $row['id'];
            $linked_id = $row['linked_folder_id'];

            $shortFolder = new FolderShortcutResultItem($id, $result['folders'][$linked_id]);
            $shortFolder->parentId = $row['parent_id'];
            $shortFolder->linkedId = $row['linked_folder_id'];
            $shortFolder->full_path = $row['full_path'];

            $result['shortfolders'][$id] = $shortFolder;
        }
    }
    return $result;
}

?>
