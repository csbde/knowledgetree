<?

/**
 *
 * Demonstrates using an active session and getting document info and metadata.
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

// lets ge the document based on id.

$document = $ktapi->get_document_by_id(50);
if (PEAR::isError($document))
{
	print $document->getMessage();
	exit;
}

// lets get the document metadata

$metadata = $document->get_metadata();
if (PEAR::isError($metadata))
{
	print $metadata->getMessage();
	exit;
}
var_dump($metadata);

?>
