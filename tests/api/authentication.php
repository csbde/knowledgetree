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

require_once(dirname(__FILE__) . '/../test.php');
require_once(KT_DIR .  '/ktapi/ktapi.inc.php');


class APIAuthenticationTestCase extends KTUnitTestCase 
{
	function testAdmin()
	{
		$ktapi = new KTAPI();
		
		$session = $ktapi->start_session('admin','admin');
		$this->assertTrue(is_a($session,'KTAPI_UserSession'));
		$this->assertTrue($session->is_active());
		
		$ktapi = new KTAPI();
		$session = $ktapi->get_active_session($session->session);
		$this->assertTrue(is_a($session,'KTAPI_UserSession'));
		
		
		$session->logout();
		$this->assertFalse($session->is_active());
	}
	
	function testSystemLogin()
	{
		$ktapi = new KTAPI();
		
		$session = $ktapi->start_system_session();
		$this->assertTrue(is_a($session,'KTAPI_SystemSession'));
		$this->assertTrue($session->is_active());
				
		$session->logout();
		$this->assertFalse($session->is_active());
	}
	
	function testAnonymousLogin()
	{
		$ktapi = new KTAPI();
		
		$session = $ktapi->start_anonymous_session();
		$this->assertTrue(is_a($session,'KTAPI_AnonymousSession'));
		$this->assertTrue($session->is_active());
		
		$ktapi = new KTAPI();
		$session = $ktapi->get_active_session($session->session);
		$this->assertTrue(is_a($session,'KTAPI_AnonymousSession'));

		
		$session->logout();
		$this->assertFalse($session->is_active());
	}
	
}
?>