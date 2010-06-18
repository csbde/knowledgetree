<?php
require_once (KT_DIR . '/tests/test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');

require_once(KT_DIR . '/plugins/comments/comments.php');

// username and password for authentication
// must be set correctly for all of the tests to pass in all circumstances
define (KT_TEST_USER, 'admin');
define (KT_TEST_PASS, 'admin');

/**
 * These are the unit tests for the main KTAPI class
 *
 * NOTE All functions which require electronic signature checking need to send
 * the username and password and reason arguments, else the tests WILL fail IF
 * API Electronic Signatures are enabled.
 * Tests will PASS when API Signatures NOT enabled whether or not
 * username/password are sent.
 */
class CommentsTestCase extends KTUnitTestCase {

    /**
    * @var object $ktapi The main ktapi object
    */
    var $ktapi;

    /**
    * @var object $session The KT session object
    */
    var $session;

    /**
     * @var object $root The KT folder object
     */
    var $root;

    private $folder;
    private $folder_id;
    private $doc_id;


    /**
    * This method sets up the KT session
    *
    */
    public function setUp() {
        $this->ktapi = new KTAPI();
        $this->session = $this->ktapi->start_session(KT_TEST_USER, KT_TEST_PASS);
        $this->root = $this->ktapi->get_root_folder();
        $this->assertTrue($this->root instanceof KTAPI_Folder);

        // create a folder with a test document
        $this->folder = $this->root->add_folder('Unit Testing Comments');
        $this->folder_id = $this->folder->get_folderid();

        $content = 'This is the content for a small document to be used in the unit tests';
        $content = base64_encode($content);
        $result = $this->ktapi->add_small_document($this->folder_id, 'Comments Test Doc', 'comments_test.txt', 'Default', $content, null, null, 'Unit testing the comments functionality');

        $this->assertEqual($result['status_code'], 0);
        if($result['status_code'] == 0){
            $this->doc_id = $result['results']['document_id'];
        }
    }

    /**
    * This method emds the KT session
    *
    */
    public function tearDown() {
        // remove folder and expunge document
        $document = $this->ktapi->get_document_by_id($this->doc_id);
        $document->delete('Removing test document');
        $document->expunge();

        $this->folder->delete('Removing test folder');

        $this->session->logout();
    }

    public function testComments()
    {
        // Get the current list of comments
        $comments = Comments::get_comments($this->doc_id);
        $this->assertTrue(is_array($comments));

        // Check the number of comments (in case system is not clean)
        $num_comments = count($comments);

        // Create a new comment
        $comment1 = 'Testing comments 1';
        $res = Comments::add_comment($this->doc_id, $comment1);
        $this->assertTrue($res);

        // ensure a different date created.
        sleep(1);

        // Create a second comment
        $comment2 = 'Testing comments 2';
        $res = Comments::add_comment($this->doc_id, $comment2);
        $this->assertTrue($res);

        // Get newly created comments
        $comments = Comments::get_comments($this->doc_id);
        $this->assertTrue(is_array($comments));

        $new_num = count($comments);
        $diff = (int)$new_num - (int)$num_comments;
        $this->assertEqual($diff, 2);

        // Check that the last added comment is returned first
        $this->assertEqual($comments[0]['comment'], $comment2);

        // delete comments
        Comments::delete_comment($comments[0]['id']);
        Comments::delete_comment($comments[1]['id']);

        $comments = Comments::get_comments($this->doc_id);
        $this->assertTrue(is_array($comments));

        // Check the number of comments is the same as the original
        $new_num = count($comments);
        $this->assertEqual($new_num, $num_comments);
    }
}
?>