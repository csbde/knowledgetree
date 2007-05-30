<?

/**
 *
 * Demonstrates document checkout and checkin
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

require_once('../ktwsapi.inc.php');

$ktapi = new KTWSAPI(KTWebService_WSDL);

// change session to something that is in table 'active_sessions'

$response = $ktapi->active_session('sj5827sohdoj6h3nvifrcsa1f2');
if (PEAR::isError($response))
{
	print $response->getMessage();
	exit;
}

$root = $ktapi->get_root_folder();
if (PEAR::isError($root))
{
	print $root->getMessage();
	exit;
}

$listing = $root->get_listing();
if (PEAR::isError($listing))
{
	print $listing->getMessage();
	exit;
}

// lets display the items

foreach($listing as $folderitem)
{
	$title = $folderitem->title;
	$item_type = $folderitem->item_type;
	$mime_display = $folderitem->mime_display;
	 
	print "$title $item_type $mime_display\n";
}

?>