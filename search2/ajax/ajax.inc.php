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

require_once('../../config/dmsDefaults.php');
session_start();

require_once(KT_DIR . '/search2/search/search.inc.php');


class AjaxSearchHelper
{
	const STATUS_SUCCESS = 0;
	const STATUS_MISSING_QUERY = 1;
	const STATUS_SESSION = 3;
	const STATUS_MISSING_WHAT = 4;
	const STATUS_MISSING_FIELDSET = 5;
	const STATUS_INTERNAL = 99;
	const STATUS_PARSE_PROBLEM = 20;
	const STATUS_MISSING_SAVED = 6;
	const STATUS_MISSING_NAME = 7;
	const STATUS_SAVED_SEARCH_EXISTS = 30;
	const STATUS_MISSING_DOCUMENT_TYPE = 8;
	const STATUS_MISSING_FOLDER = 9;


	public static function checkVar($var, $code, $message)
	{
		if (empty($var))
		{
			AjaxSearchHelper::createResponse($code,$message);
		}
		return $var;
	}

	public static function checkPOST($var, $code, $message)
	{
		return AjaxSearchHelper::checkVar($_GET[$var], $code, $message);
	}

	public static function checkSESSION($var, $code, $message)
	{
		return AjaxSearchHelper::checkVar($_SESSION[$var], $code, $message);
	}

	public static function getSessionUser()
	{
		return AjaxSearchHelper::checkSESSION('userID', AjaxSearchHelper::STATUS_SESSION , _kt('Session has expired.'));
	}

	public static function checkGET($var, $code, $message)
	{
		return AjaxSearchHelper::checkVar($_GET[$var], $code, $message);
	}


	public static function createResponse($status, $message=null, $rsName=null,$rs=null)
	{
		$resp = array('status'=>$status);
		if (isset($message))
		{
			$resp['message'] = $message;
		}
		if (isset($rsName))
		{
			$resp[$rsName] = $rs;
		}
		print json_encode($resp);
		exit;
	}

	public static function parseQuery($txtQuery, $exitOnSuccess=true)
	{
		try
		{
			$expr = parseExpression($txtQuery);
			if ($exitOnSuccess)
			{
				AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_SUCCESS );
			}
			return $expr;
		}
		catch(Exception $e)
		{
			AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_PARSE_PROBLEM , $e->getMessage());
		}
	}

	public static function updateQuery($iSavedId,$txtQuery, $userID)
	{
		$txtQuery = sanitizeForSQL($txtQuery);
		$iSavedId = sanitizeForSQL($iSavedId);

		$sql = "UPDATE search_saved SET expression='$txtQuery' WHERE id=$iSavedId";
		if (!Permission::userIsSystemAdministrator($userID))
		{
			$sql .= " AND user_id = $userID";
		}
		$result = DBUtil::runQuery($sql);
		if (PEAR::isError($result))
		{
			AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_INTERNAL );
		}
		AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_SUCCESS );
	}


	/**
	* This method uses ajax to save the users query
	*
	* @author KnowledgeTree TEam
	* @access public
	* @static
	* @param string $txtName The name of the saved search query
	* @param string $txtQuery The search query
	* @param string $userID The id of the user saving the query
	* @return void
	*/
	public static function saveQuery($txtName,$txtQuery, $userID)
	{

/*		$lookup = sanitizeForSQL($txtName);
		$sql = "select 1 from search_saved where name='$lookup'";
		$result = DBUtil::getResultArray($sql);
*/
		$result = SearchHelper::checkForSavedSearch($txtName);
		if (PEAR::isError($result))
		{
			AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_INTERNAL );
		}
		if (count($result) > 0)
		{
			AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_SAVED_SEARCH_EXISTS, _kt('Search with this name already exists') );
		}


/*		// autoInsert does escaping...
		$values = array(
		'name'=>$txtName,
		'expression'=>$txtQuery,
		'type'=>'S',
		'shared'=>0,
		'user_id' => $userID
		);
		$result = DBUtil::autoInsert('search_saved', $values);
*/
        $result = SearchHelper::saveSavedSearch($txtName,$txtQuery, $userID);

		if (PEAR::isError($result))
		{
			AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_INTERNAL );
		}
		AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_SUCCESS );
	}

	public static function getSavedSearches($userID)
	{
		$rs = SearchHelper::getSavedSearches($userID);
		if (PEAR::isError($rs))
		{
			AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_INTERNAL );
		}

		AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_SUCCESS , null, 'searches', $rs);
	}



	public static function getDocumentTypes()
	{
		$rs = SearchHelper::getDocumentTypes();
		if (PEAR::isError($rs))
		{
			AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_INTERNAL, $rs->getMessage() );
		}
		AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_SUCCESS , null, 'documenttypes', $rs);
	}

	public static function getDocumentTypeFieldsets($documentTypeID)
	{
		$rs = SearchHelper::getDocumentTypeFieldsets($documentTypeID);

		if (PEAR::isError($rs))
		{
			AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_INTERNAL, $rs->getMessage() );
		}
		AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_SUCCESS , null, 'fieldsets', $rs);
	}


	public static function getFieldsets()
	{
		$rs = SearchHelper::getFieldsets();
		if (PEAR::isError($rs))
		{
			AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_INTERNAL, $rs->getMessage() );
		}
		AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_SUCCESS , null, 'fieldsets', $rs);
	}

	public static function getFields($fieldsetID)
	{
		$result = SearchHelper::getFields($fieldsetID);

		if (PEAR::isError($result))
		{
			AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_INTERNAL, $result->getMessage() );
		}

		AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_SUCCESS , null, 'fields', $result);
	}


	public static function getFolder($folderID)
	{
		$userid = AjaxSearchHelper::getSessionUser();

		$folders = SearchHelper::getFolder($folderID, $userid);
		if (PEAR::isError($folders))
		{
			AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_MISSING_FOLDER, $folders->getMessage()  );
		}

		AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_SUCCESS , null, 'folders', $folders);

	}

	public static function getSearchFields()
	{
		$results = SearchHelper::getSearchFields();
		AjaxSearchHelper::createResponse(AjaxSearchHelper::STATUS_SUCCESS , null, 'fields', $results);
	}

}

?>
