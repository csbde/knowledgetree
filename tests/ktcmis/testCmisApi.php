<?php

// TODO use CMISUtil::encodeObjectId to create testing ids, as we may change how the encoding works in future

require_once (KT_DIR . '/tests/test.php');
require_once (KT_LIB_DIR . '/api/ktcmis/ktNavigationService.inc.php');
require_once (KT_LIB_DIR . '/api/ktcmis/ktObjectService.inc.php');
require_once (KT_LIB_DIR . '/api/ktcmis/ktRepositoryService.inc.php');
require_once (KT_LIB_DIR . '/api/ktcmis/ktVersioningService.inc.php');

// username and password for authentication
// must be set correctly for all of the tests to pass in all circumstances
define (KT_TEST_USER, 'admin');
define (KT_TEST_PASS, 'admin');

// set to true to print out results
define (DEBUG_CMIS, false);

/**
 * These are the unit tests for the main KTCMIS class
 *
 * NOTE All functions which require electronic signature checking need to send
 * the username and password and reason arguments, else the tests WILL fail IF
 * API Electronic Signatures are enabled.
 * Tests will PASS when API Signatures NOT enabled whether or not
 * username/password are sent.
 */
class CMISTestCase extends KTUnitTestCase {

    /**
    * @var object $ktcmis The main ktapi object
    */
    var $ktcmis;

    /**
    * @var object $session The KT session object
    */
    var $session;

    /**
     * @var object $root The KT folder object
     */
    var $root;

    private $folders;
    private $subfolders;
    private $docs;

    /**
    * This method sets up the KT session
    *
    */
    public function setUp() {
        $this->ktapi = new KTAPI();
        $this->session = $this->ktapi->start_session(KT_TEST_USER, KT_TEST_PASS);
        $this->root = $this->ktapi->get_root_folder();
        $this->folders = array();
        $this->docs = array();
    }

    /**
    * This method ends the KT session
    */
    public function tearDown() {
        $this->session->logout();
    }

    // Repository service functions
    function testRepositoryService()
    {
        $RepositoryService = new KTRepositoryService();

        // TEST 1
        // test get repositories
        $response = $RepositoryService->getRepositories();

        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results'][0]);

        // we only expect one repository, though there may be more
        $repository = $response['results'][0];

        // check for null
        $this->assertNotNull($repository['repositoryId']);
        $this->assertNotNull($repository['repositoryName']);
        $this->assertNotNull($repository['repositoryURI']);

        // check for empty string
        $this->assertNotEqual(trim($repository['repositoryId']), '');
        $this->assertNotEqual(trim($repository['repositoryName']), '');
        $this->assertNotEqual(trim($repository['repositoryURI']), '');

        // test printout
        $this->printTable($repository, 'Available Repositories (getRepositories())');

        /*-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-*/

        $repositoryId = $repository['repositoryId'];

        // TEST 2
        // test getting info for specified repository

        // get info
        $response = $RepositoryService->getRepositoryInfo($repositoryId);

        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results']);

        $repositoryInfo = $response['results'];

        // returned ID MUST match sent ID
        $this->assertEqual($repositoryId, $repositoryInfo['repositoryId']);

        // check for null
        $this->assertNotNull($repositoryInfo['repositoryName']);

        // check for empty string
        $this->assertNotEqual(trim($repositoryInfo['repositoryName']), '');

        $capabilities = $repositoryInfo['capabilities'];
        $this->assertNotNull($capabilities);
//        echo '<pre>'.print_r($repositoryInfo, true).'</pre>';

        // test printout
        $this->printTable($repositoryInfo, 'Repository Information for ' . $repositoryInfo['repositoryName'] . ' Repository (getRepositoryInfo())');

        /*-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-*/

        // TEST 3
        // test get object types supported by specified repository

        $response = $RepositoryService->getTypes($repositoryId);

        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results']);

        $supportedObjects = $response['results'];
//        echo '<pre>'.print_r($supportedObjects, true).'</pre>';

        // we're expecting support for Documents and Folders
        // TODO change test to not care about alphabetical or order alphabetically first,
        //      just in case at some point we manage to return the objects in another order :)
        // TODO this test is pretty arbitrary, needs work to properly test results,
        //      for now just testing that something was returned
        $this->assertEqual($supportedObjects[0]['typeId'], 'Document');
        $this->assertEqual($supportedObjects[1]['typeId'], 'Folder');

        // test printout
        $this->printTable($supportedObjects, 'CMIS Objects Supported by the ' . $repository['repositoryName'] . ' Repository (getTypes())', 'Object Type');

        /*-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-*/

        // TEST 4
        // test getting type definition for specified types
        // types to test
        $types = array('Document', 'Folder');

        // now get info
        foreach ($types as $typeId)
        {
            $response = $RepositoryService->getTypeDefinition($repositoryId, $typeId);

            $this->assertEqual($response['status_code'], 0);
            $this->assertNotNull($response['results']);

            $typeDefinition = $response['results'];

            // we're expecting support for Documents and Folders
            // TODO change test to not care about alphabetical or order alphabetically first,
            //      just in case at some point we manage to return the objects in another order :)
            // TODO this test is pretty arbitrary, needs work to properly test results,
            //      for now just testing that something was returned which matches the requested type
            $this->assertEqual($typeDefinition['typeId'], $typeId);

            // test printout
            $this->printTable($typeDefinition, 'CMIS Type Definition for ' . $typeDefinition['typeId'] . ' Objects (getTypeDefinition())');

            // TODO test properties as well
        }

        // test printout
        if (DEBUG_CMIS) echo '<div>&nbsp;</div>';
    }

    // Navigation service functions
    function testNavigationService()
    {
        $NavigationService = new KTNavigationService($this->ktapi);

        // set up the folder/doc tree structure with which we will be testing
        $this->createFolderDocStructure();
        
        $RepositoryService = new KTRepositoryService();
        $response = $RepositoryService->getRepositories();

        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results'][0]);

        // we only expect one repository
        $repository = $response['results'][0];
        $repositoryId = $repository['repositoryId'];

        // TEST 1
        // test getting descendants
        // test descendant functionality on first of created folders, should have depth 2;
        $folderid = 'F' . $this->folders[1];
//        echo "FOLDER: $folderid<BR>";
//        $folderid = 'F1';

        $depth = 2;
        $result = $NavigationService->getDescendants($repositoryId, $folderid, false, false, $depth);
//        echo '<pre>'.print_r($result, true).'</pre>';
//        var_dump($result);
        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results'][0]);

        $descendants = $result['results'];
        $this->assertNotNull($descendants);

        // check depth
        $dug = $this->array_depth($descendants);
//        echo "DUG TO $dug (aiming for $depth)<BR>";
//        $this->assertEqual($depth, $dug);

        // test printout
        $this->printTree($descendants, 'Descendants for Folder ' . $folderid . ' (getDescendants())', $dug);

        /*-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-*/

        // TEST 2
        // test getting direct children, using the second set of folders, should have a folder and a document as children
        $folderid_2 = 'F' . $this->folders[0];

        $result = $NavigationService->getChildren($repositoryId, $folderid_2, false, false);
        $this->assertNotNull($result['results']);

        $children = $result['results'];

        // total child count should be 2, as there is a single folder and a single document
//echo '<pre>'.print_r($children, true).'</pre>';

        // test printout
        $this->printTree($children, 'Children for Folder ' . $folderid_2 . ' (getChildren())');

        /*-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-*/

        // TEST 3
        // test getting folder parent, using first created folder, parent should be root folder

//        echo "OUTPUT FROM FIRST TEST<BR>";
        $ancestry = $NavigationService->getFolderParent($repositoryId, $folderid, false, false, false);
        $this->assertNotNull($ancestry['results']);
//        echo "OUTPUT FROM FIRST TEST<BR>";
//        echo '<pre>'.print_r($ancestry, true).'</pre>';

        // test printout
        $this->printTree($ancestry['results'], 'Parent for Folder ' . $folderid . ' (getFolderParent())');

        // test with one of the subfolders...
        $subfolder_id = 'F' . $this->subfolders[0];

//        echo "OUTPUT FROM SECOND TEST<BR>";
        // TODO since here we are testing more than one level up, add check for depth as with testGetDescendants
        $ancestry = $NavigationService->getFolderParent($repositoryId, $subfolder_id, false, false, true);
        $this->assertNotNull($ancestry['results']);
//        echo "OUTPUT FROM SECOND TEST<BR>";
//        echo '<pre>'.print_r($ancestry, true).'</pre>';

        // NOTE can only send depth here because we know it :)
        // test printout
        $this->printTree($ancestry['results'], 'Parent hierarchy (Return To Root) for Folder ' . $subfolder_id . ' (getFolderParent())', 2);

        /*-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-*/

        // TEST 4
        // test getting object parent(s) with a document

        $objectId = 'D' . $this->docs[0]->get_documentid();
        $ancestry = $NavigationService->getObjectParents($repositoryId, $objectId, false, false);
        $this->assertNotNull($ancestry);
//        echo '<pre>'.print_r($ancestry, true).'</pre>';

        // test printout
        $this->printTree($ancestry['results'], 'Parent for (Document) Object ' . $objectId . ' (getObjectParents())');

        // TEST
        // test getting object parent(s) with a folder

        $objectId = 'F' . $this->subfolders[0];
        $ancestry = $NavigationService->getObjectParents($repositoryId, $objectId, false, false);
        $this->assertNotNull($ancestry);
//        echo '<pre>'.print_r($ancestry, true).'</pre>';

        // test printout
        $this->printTree($ancestry['results'], 'Parent for (Folder) Object ' . $objectId . ' (getObjectParents())');
        
        // TODO test checked out documents listing (not yet implemented)
        
        // tear down the folder/doc tree structure with which we were testing
        $this->cleanupFolderDocStructure();

        // test printout
        if (DEBUG_CMIS) echo '<div>&nbsp;</div>';
    }

    // Object Services

    function testObjectService()
    {
        $ObjectService = new KTObjectService($this->ktapi);
//        $ObjectService->startSession(KT_TEST_USER, KT_TEST_PASS);

        // set up the folder/doc tree structure with which we will be testing
        $this->createFolderDocStructure();

        $RepositoryService = new KTRepositoryService();
        $response = $RepositoryService->getRepositories();

        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results'][0]);

        // we only expect one repository
        $repository = $response['results'][0];
        $repositoryId = $repository['repositoryId'];
        
        // TEST 1
        // test getting properties for a specific object
        $objectId = 'F'.$this->folders[0];

        $properties = $ObjectService->getProperties($repositoryId, $objectId, false, false);
        $this->assertNotNull($properties['results']);
//        echo '<pre>'.print_r($properties['results'], true).'</pre>';
//
        // test printout
        $this->printTable($properties['results'][0], 'Properties for Folder Object ' . $objectId . ' (getProperties())');

        $objectId = 'D'.$this->docs[0]->get_documentid();

        $properties = $ObjectService->getProperties($repositoryId, $objectId, false, false);
        $this->assertNotNull($properties['results']);

        // test printout
        $this->printTable($properties['results'][0], 'Properties for Folder Object ' . $objectId . ' (getProperties())');

        // TEST 2
        // test creation of a folder (random name so that we don't have to clean up after)
        // TODO test invalid type
        // TODO test invalid parent folder
        // TODO other invalid parameters
        $created = $ObjectService->createFolder($repositoryId, 'Folder', array('name' => 'My Test Folder ' . mt_rand()), 'F1');
        $this->assertNotNull($created['results']);

        if (!is_null($created['results']))
        {
            $folderId = $created['results'];

            // check that folder object actually exists
            $properties = $ObjectService->getProperties($repositoryId, $folderId, false, false);
            $this->assertNotNull($properties['results']);
            
            // test printout
            $this->printTable($properties['results'][0], 'Properties for CMIS Created Folder Object ' . $folderId . ' (getProperties())');

            // delete
            $this->ktapi->delete_folder(CMISUtil::decodeObjectId($folderId), 'Testing API', KT_TEST_USER, KT_TEST_PASS);
        }

        // TEST 3
        // test creation of document
        $folderId = 'F'.$this->folders[0];
//        $folderId = 'F1';
        $properties = array('name' => 'Test CMIS Document 1', 'title' => 'test_cmis_doc_' . mt_rand() . '.txt');
        $contentStream = base64_encode('Some arbitrary text content');
        $created = $ObjectService->createDocument($repositoryId, 'Document', $properties, $folderId, $contentStream);

        $this->assertNotNull($created['results']);

//        echo '<pre>'.print_r($created, true).'</pre>';

        if (!is_null($created['results']))
        {
            $documentId = $created['results'];

            // check that document object actually exists
            $properties = $ObjectService->getProperties($repositoryId, $documentId, false, false);
            $this->assertNotNull($properties['results']);

            // test printout
            $this->printTable($properties['results'][0], 'Properties for CMIS Created Document Object ' . $documentId . ' (getProperties())');
        }

//        // TEST 5
//        // test updating content stream for existing document
//        $contentStream = base64_encode('Some updated text content for the content stream');
//        $updated = $ObjectService->setContentStream($repositoryId, $documentId, true, $contentStream);
//
//        $this->assertNotNull($updated['results']);
//
////        echo '<pre>'.print_r($created, true).'</pre>';
//
//        if (!is_null($updated['results']))
//        {
////            $documentId = $updated['results'];
//
//            // TODO test getContentStream here when we have it
//
//        }

        // delete created document
        $this->ktapi->delete_document(CMISUtil::decodeObjectId($documentId), 'Testing API', false);
        
        // TEST
        $folderId = 'F'.$this->folders[0];
        $objectId = 'D'.$this->docs[0]->get_documentid();
        // test attempted delete via single object delete on folder which contains content - should fail
        $response = $ObjectService->deleteObject($repositoryId, $folderId);
        $this->assertEqual($response['status_code'], 1);
        $this->assertNotNull($response['message']);
        
        // test deletion of folder tree - should succeed unless there are non-deletable objects (checked out) (not testing latter)
        $response = $ObjectService->deleteTree($repositoryId, $folderId);
        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results']);
        
        // TODO test deleteTree with checkedout documents
        
        // test attempted delete of document with single version via single object delete.
        // should succeed unless there is a valid reason (checked out/immutable) (not testing latter)
        // TODO test failure on attempt to delete document with more than one version
        $response = $ObjectService->deleteObject($repositoryId, $objectId);
        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results']);
        
        // TEST
        // TODO test failed moves (checked out, etc)
        // folder to move - folder 2
        $folderId = 'F'.$this->folders[1];
        // document to move - document 3
        $objectId = 'D'.$this->docs[1]->get_documentid();
        // target folder for all moves
        $root = $this->ktapi->get_root_folder();
        $targetFolderId = 'F'.$root->get_folderid();
        
        // test moving of object (document)
        $response = $ObjectService->moveObject($repositoryId, $objectId, null, $targetFolderId);
        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results']);
        
        // test moving of object (folder)
        $response = $ObjectService->moveObject($repositoryId, $folderId, null, $targetFolderId);
        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results']);
        
        // TEST
        // TODO test failure conditions
        // TODO test fetching of content stream
        $response = $ObjectService->getContentStream($repositoryId, $objectId);
        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results']);
        
        // TEST
        // TODO test setContentStream?  unused at the moment

        // tear down the folder/doc tree structure with which we were testing
        $this->cleanupFolderDocStructure();

        // test printout
        if (DEBUG_CMIS) echo '<div>&nbsp;</div>';
    }
    
    function testVersioningService()
    {
        $VersioningService = new KTVersioningService($this->ktapi);
        $NavigationService = new KTNavigationService($this->ktapi);

        // set up the folder/doc tree structure with which we will be testing
        $this->createFolderDocStructure();

        $RepositoryService = new KTRepositoryService();
        $response = $RepositoryService->getRepositories();

        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results'][0]);
//
        // we only expect one repository
        $repository = $response['results'][0];
        $repositoryId = $repository['repositoryId'];
        
        // test deletion of document via deleteAllVersions
        $versionSeriesId = 'D'.$this->docs[0]->get_documentid();
        $response = $VersioningService->deleteAllVersions($repositoryId, $versionSeriesId);
        
        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results']);
                
        // TODO test checkout of document
        $documentId = CMISUtil::encodeObjectId('Document', $this->docs[1]->get_documentid());
        $response = $VersioningService->checkOut($repositoryId, $documentId);
        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results']);

////        // use this id for cancel checkout and checkin, not the original document id
////        $pwcId = $response['results'];
        $pwcId = CMISUtil::encodeObjectId(DOCUMENT, $this->docs[1]->get_documentid());
        
        // try again, this time it should fail - not working at the moment as ktapi registers the same user for download 
        // even if already checked out, so no error is generated unless a different user attempts to do a checkout
        /*
        $response = $VersioningService->checkOut($repositoryId, $documentId);
        $this->assertEqual($response['status_code'], 1);
        $this->assertNotNull($response['message']);
        */
        
        // test cancel checkout
//        echo "WITH: $pwcId<BR>";
        $response = $VersioningService->cancelCheckOut($repositoryId, $pwcId);
        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results']);
        
        // test cancel checkout of document no longer checked out
        $response = $VersioningService->cancelCheckOut($repositoryId, $pwcId);
        $this->assertEqual($response['status_code'], 1);
        $this->assertNotNull($response['message']);
        
        // test listing of checked out documents
        // first check out the document again :)
        $response = $VersioningService->checkOut($repositoryId, $documentId);
        // now check that it appears in the listing
        $response = $NavigationService->getCheckedOutDocs($repositoryId, false, false);
        $this->assertEqual($response['status_code'], 0);
        $this->assertNotNull($response['results']);
        $this->assertTrue($this->findInPropertiesArray('ObjectId', $documentId, $response['results']));
        // now let's cancel the checkout so that we can delete later during cleanup :)
        $response = $VersioningService->cancelCheckOut($repositoryId, $pwcId);
               
        // TODO test checkin
        
        // TODO add testing of failure conditions - e.g. checked out/immutable document (for all appropriate functions)
        
        // tear down the folder/doc tree structure with which we were testing
        $this->cleanupFolderDocStructure();
    }
    
    /**
     * Searches a CMIS properties array for a specific value
     * 
     * @param string $key The CMIS property key to look for
     * @param string $needle The value to check
     * @param array $haystack The CMIS properties array
     * @param int $propeLevel -1 or positive value -> -1 = not yet found | positive = found
     * @return boolean
     */
    function findInPropertiesArray($key, $needle, $haystack, $propLevel = null)
    {
        $found = false;
        
        if (empty($propLevel)) $propLevel = -1;

        foreach($haystack as $elKey => $elValue)
        {
            if (($propLevel == -1) && ((string)$elKey != 'properties')) {
                $found = $this->findInPropertiesArray($key, $needle, $elValue, $propLevel);
                if ($found) break;
            }
            else if ((string)$elKey == 'properties') {
                $propLevel = 1;
            }
            
            // now check through actual properties array
            $properties = $elValue;
            foreach($properties as $propKey => $property)
            {
                if (($propKey == $key) && ($property['value'] == $needle)) {
                    $found = true;
                    break;
                }
            }
        }
        
        return $found;
    }
    
    /**
     * Helper function to create a document
     */
    function createDocument($title, $filename, $folder = null)
    {
        if(is_null($folder)){
            $folder = $this->root;
        }

        // Create a new document
        $randomFile = $this->createRandomFile();
        $this->assertTrue(is_file($randomFile));

        $document = $folder->add_document($title, $filename, 'Default', $randomFile, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $this->assertNotError($document);

        @unlink($randomFile);
        if(PEAR::isError($document)) return false;

        return $document;
    }

    /**
     * Helper function to delete docs
     */
    function deleteDocument($document)
    {
        $document->delete('Testing API');
        // expunge appears to be causing problems at the moment
        // $document->expunge();
    }

    function createRandomFile($content = 'this is some text', $uploadDir = null)
    {
        if(is_null($uploadDir)){
           $uploadDir = dirname(__FILE__);
        }
        $temp = tempnam($uploadDir, 'myfile');
        $fp = fopen($temp, 'wt');
        fwrite($fp, $content);
        fclose($fp);
        return $temp;
    }

    function createFolderDocStructure()
    {
        // Create a folder
        $result1 = $this->ktapi->create_folder(1, 'New test api folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');

        $folder_id = $result1['results']['id'];
        $this->folders[] = $folder_id;

        // Create a sub folder
        $result2 = $this->ktapi->create_folder($folder_id, 'New test api sub-folder', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $folder_id2 = $result2['results']['id'];
        $this->subfolders[] = $folder_id2;
        
        // Add a document to the subfolder
        global $default;
        $dir = $default->uploadDirectory;
        $tempfilename = $this->createRandomFile('some text', $dir);
        $document1 = $this->ktapi->add_document($folder_id2,  'New API test subdoc', 'testsubdoc1.txt', 'Default',
                                               $tempfilename, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        @unlink($tempfilename);

        // Create a second set of folders
        $result1 = $this->ktapi->create_folder(1, 'New test api folder the second', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $folder_id = $result1['results']['id'];
        $this->folders[] = $folder_id;

        // Add a single document to the root folder of the second set
        $tempfilename = $this->createRandomFile('some text', $dir);
        $document2 = $this->ktapi->add_document($folder_id,  'New API test subdoc', 'testsubdoc3.txt', 'Default',
                                               $tempfilename, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        @unlink($tempfilename);

        // Create a sub folder
        $result2 = $this->ktapi->create_folder($folder_id, 'New test api sub-folder for the second', KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        $folder_id2 = $result2['results']['id'];
        $this->subfolders[] = $folder_id2;

        // Add a couple of documents to the second subfolder
        $tempfilename = $this->createRandomFile('some text', $dir);
        $document3 = $this->ktapi->add_document($folder_id2,  'New API test subdoc', 'testsubdoc2.txt', 'Default',
                                               $tempfilename, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        @unlink($randomFile);
        $randomFile = $this->createRandomFile();
        $document4 = $this->ktapi->add_document($folder_id2,  'New API test subdoc', 'testsubdoc4.txt', 'Default',
                                               $tempfilename, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        @unlink($tempfilename);

        // add root level docs

        $tempfilename = $this->createRandomFile('some text', $dir);
        $document5 = $this->root->add_document('title_1.txt', 'name_1.txt', 'Default', $randomFile, KT_TEST_USER, KT_TEST_PASS, 'Testing API');
        @unlink($tempfilename);

        $this->docs[] =& $document5;

        // create the document object
        $randomFile = $this->createRandomFile();
        $document6 = $this->root->add_document('title_2.txt', 'name_2.txt', 'Default', $randomFile, KT_TEST_USER, KT_TEST_PASS, 'Testing API');

        @unlink($randomFile);

        $this->docs[] =& $document6;
    }

    function cleanupFolderDocStructure()
    {
        // clean up root level docs
        foreach($this->docs as $doc)
        {
            if (++$coujnt > 10) exit;
            $doc->delete('Testing');
            // expunge appears to be breaking tests, times out
            // $doc->expunge();
        }

        // Clean up root level folders (sub-folders and contained docs should be removed automatically)
        foreach ($this->folders as $folder_id)
        {
            $this->ktapi->delete_folder($folder_id, 'Testing API', KT_TEST_USER, KT_TEST_PASS);
        }
    }

    function array_depth($array)
    {
//        echo '<pre>'.print_r($array, true).'</pre>';
        if (!(is_array($array))) return 0;

        $dug = 0;

        $array_str = print_r($array, true);
        $lines = explode("\n", $array_str);

        $matched = false;
        foreach ($lines as $line) {
            if (preg_match('/\[0\] => Array/', $line))
            {
                // going down one level
                ++$dug;
                $matched = true;
            }
            else if (preg_match('/\[[^0]\] => Array/', $line))
            {
                // coming up one level
                --$dug;
                $matched = false;
            }
            else if ($matched && (preg_match('/\[item_type\] => D/', $line)))
            {
                --$dug;
                $matched = false;
            }
        }
        
        // oops, glitch if single level only :)
        if ($dug < 1)
            return 1;
        
        return ++$dug;
    }

    function printTable($results, $header, $subheader = '', $depth = 1)
    {
        if (!DEBUG_CMIS) return null;
        if (!is_array($results)) return null;

        ?><div>&nbsp;</div>
        <table border="2"><tr><td colspan="2"><div style="padding: 8px; background-color: green; color: white;"><?php
        echo $header;
        ?></div></td></tr><?php
        foreach($results as $key => $value)
        {
            if ($value == '') continue;
            if (is_array($value))
            {
                if ($subheader != '')
                {
                    ?><tr><td colspan="2"><div style="padding: 8px; background-color: lightblue; color: black;"><?php
                    echo $subheader;
                    ?></div></td></tr><?php
                }
                
                foreach($value as $subkey => $subvalue)
                {
                    if ($subvalue == '') continue;
                    $this->printRow($subkey, $subvalue);
                }
            }
            else
            {
                $this->printRow($key, $value);
            }
        }
        ?></table><?php
    }

    function printRow($key, $value, $indent = 0)
    {
        echo '<tr>';
        if ($indent)
        {
            for ($i = 1; $i <= $indent; ++$i)
            {
                echo '<td>&nbsp;</td>';
            }
        }
        echo '            <td style="padding-left:15px;padding-right:15px;">' . $key . '</td>
                    <td style="padding-left:15px;padding-right:15px;">' . $value . '</td>
                </tr>';
    }

    function printTree($results, $header, $depth = 1)
    {
        if (!DEBUG_CMIS) return null;
        if (!is_array($results)) return null;
        
        ?><div>&nbsp;</div>
        <table border="2"><tr><td colspan="<?php echo 1 + $depth; ?>"><div style="padding: 8px; background-color: green; color: white;"><?php
        echo $header;
        ?></div></td></tr><?php

        // after header we want to print the first level, indent for second level, indent twice for 3rd, etc...

        foreach($results as $key => $value)
        {
            ?><tr><td colspan="<?php echo 1 + $depth; ?>"><div style="padding: 8px; background-color: lightblue; color: black;"><?php
            echo $value['properties']['name']['value'];
            ?></div></td></tr><?php
            // properties first
            foreach ($value['properties'] as $propkey => $propval)
            {
                if ($propval['value'] == '') continue;

                $this->printRow($propkey, $propval['value']);
            }

            // now any children
            if ($value['child'])
            {
                foreach ($value['child'] as $childkey => $childval)
                {
                    $this->printChild($childval);
                }
            }

//            if ($value == '') continue;
//            if (is_array($value))
//            {
//                if ($subheader != '')
//                {
/*
//                    ?><tr><td colspan="2"><div style="padding: 8px; background-color: lightblue; color: black;"><?php
//                    echo $subheader;
//                    ?></div></td></tr><?php
 * 
 */
//                }
//
//                foreach($value as $subkey => $subvalue)
//                {
//                    if ($subvalue == '') continue;
//                    $this->printRow($subkey, $subvalue);
//                }
//            }
//            else
//            {
//                $this->printRow($key, $value);
//            }
        }
        ?></table><?php
    }

    function printChild($child, $indent = 1)
    {
        ?><tr><?php
        if ($indent)
        {
            for ($i = 1; $i <= $indent; ++$i)
            {
                echo '<td>&nbsp;</td>';
            }
        }
        ?><td colspan="<?php echo 1 + $indent; ?>"><div style="padding: 8px; background-color: lightblue; color: black;"><?php
        echo $child['properties']['name'];
        ?></div></td></tr><?php
        foreach ($child['properties'] as $ckey => $cval)
        {
            if ($cval == '') continue;
            $this->printRow($ckey, $cval, $indent);
        }

        if ($child['child'])
        {
            $this->printChild($child['child'], ++$indent);
        }
    }
}
?>