<?
/**
 * $Id$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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

require_once(dirname(__FILE__) . '/../test.php');
require_once(KT_DIR .  '/ktapi/ktapi.inc.php');

class APIFolderTestCase extends KTUnitTestCase 
{
	/**
	 * @var KTAPI
	 */
	var $ktapi;
	var $session;
	
	 function setUp() 
	 {
	 	$this->ktapi = new KTAPI();
	 	$this->session = $this->ktapi->start_system_session();
	 }
	
	 function tearDown() 
	 {
	 	$this->session->logout();
	 }
	 
	 function testCreateDuplicate()
	 {
	 	$root=$this->ktapi->get_root_folder();
	 	$this->assertTrue(is_a($root,'KTAPI_Folder'));
	 	
	 	$folder = $root->add_folder('temp1');
	 	$this->assertTrue(is_a($folder,'KTAPI_Folder'));
	 	
	 	$folder2 = $root->add_folder('temp1');
	 	$this->assertFalse(is_a($folder2,'KTAPI_Folder'));
	 	
	 	$folder->delete('because');
	 	if (is_a($folder2,'KTAPI_Folder'))
	 	{
	 		$folder2->delete('because');
	 	}
	 	
	 }
	 
	 function testCreateFolders()
	 {
	 	$root=$this->ktapi->get_root_folder();
	 	$this->assertTrue(is_a($root,'KTAPI_Folder'));
	 	
	 	$folder = $root->add_folder('temp1');
	 	$this->assertTrue(is_a($folder,'KTAPI_Folder'));
	 	
	 	$folder2 = $folder->add_folder('temp2');
	 	$this->assertTrue(is_a($folder,'KTAPI_Folder'));
	 	
	 	$folder3 = $root->add_folder('temp3');
	 	$this->assertTrue(is_a($folder,'KTAPI_Folder'));

	 	$folder4 = $folder3->add_folder('temp4');
	 	$this->assertTrue(is_a($folder,'KTAPI_Folder'));
	 	
	 	$folderids = array(
	 		'temp1'=>$folder->get_folderid(),
	 		'temp2'=>$folder2->get_folderid(),
	 		'temp3'=>$folder3->get_folderid(),
	 		'temp4'=>$folder4->get_folderid()	 	
	 	);
	 	
	 	unset($folder);	unset($folder2); unset($folder3); unset($folder4);
	 	
	 	$paths = array(
	 		'temp1'=>'/temp1',
	 		'temp2'=>'/temp1/temp2',
	 		'temp3'=>'/temp3',
	 		'temp4'=>'/temp3/temp4',
	 	
	 	);
	 	
	 	// test reference by name	 	
	 	foreach($paths as $key=>$path)
	 	{
	 		$folder = $root->get_folder_by_name($path);
	 		$this->assertTrue(is_a($folder,'KTAPI_Folder'));
	 		if (!is_a($folder, 'KTAPI_Folder'))
	 			continue;
	 		
	 		$this->assertTrue($folder->get_folderid() == $folderids[$key]);
	 		$this->assertTrue($folder->get_full_path() == 'Root Folder' . $path);
	 	}

	 	// lets clean up
	 	foreach($paths as $key=>$path)
	 	{
	 		$folder = $root->get_folder_by_name($path);
	 		if (is_a($folder,'KTAPI_Folder'))
	 		{
	 			$folder->delete('because ' . $path);
	 		}
	 		$folder = $root->get_folder_by_name($path);
	 		$this->assertTrue(is_a($folder,'PEAR_Error'));
	 		
	 	}
	 }
	 
	 function testRename()
	 {
		$root=$this->ktapi->get_root_folder();
	 	$this->assertTrue(is_a($root,'KTAPI_Folder'));
	 	
	 	// add a sample folder
	 	$folder = $root->add_folder('newFolder');
	 	$this->assertTrue(is_a($folder,'KTAPI_Folder'));
	 	
	 	$folderid = $folder->get_folderid();
	 	
	 	// rename the folder
	 	$response = $folder->rename('renamedFolder');
	 	$this->assertTrue(!is_a($response,'PEAR_Error'));
	 	
	 	// get the folder by id
	 	$folder=$this->ktapi->get_folder_by_id($folderid);
	 	$this->assertTrue(is_a($folder,'KTAPI_Folder'));
	 	
	 	$this->assertTrue($folder->get_folder_name() == 'renamedFolder');

	 	$folder->delete('cleanup');
	 	
	 }
	 
	 
	 function getSystemListing()
	 {
	 	// TODO .. can do anything as admin...
	 }

	 function getAnonymousListing()
	 {
	 	// TODO
		// probably won't be able to do unless the api caters for setting up anonymous...	 	
	 }

	 function getUserListing()
	 {
	 	// TODO
	 	
	 }
	 
	 
	 
	 function copy()
	 {
	 	// TODO
	 }
	 
	 function move()
	 {
	 	// TODO
	 	
	 }
}

?>