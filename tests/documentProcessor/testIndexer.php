<?php

require_once(KT_DIR . '/tests/test.php');

class DocumentIndexerTestCase extends KTUnitTestCase {

    function setup()
    {
        $this->indexer = Indexer::get();
        //$this->path = KT_DIR . '/tests/documentProcessor/dataset/';
        $tempPath = KT_DIR . '/var/tmp';

        $this->targetFile = tempnam($tempPath, 'ktindexer');
        file_put_contents($this->targetFile, $this->getContent());

        $this->id = 'test_id_1';
        $this->title = 'The Critique of Pure Reason';
        $this->version = '0.1';
    }

    function tearDown()
    {
        @unlink($this->targetFile);
    }

    /**
     * Index extracted text
     */
    function testIndexDocument()
    {
        // Get extracted test
//        $text = $this->extractText('word_doc', 'doc', 'application/msword');

        $status = $this->indexer->indexDocument($this->id, $this->targetFile, $this->title, $this->version);

        $this->assertTrue($status);

        $results = $this->indexer->query('content:Human reason, in one sphere of its cognition, is called upon');

        print_r($results);

        // Run test
        //$this->assertTrue(strpos($text, 'Human reason, in one sphere of its cognition, is called upon') !== false);

        $this->indexer->deleteDocument($this->id);
    }

    /**
     * Index discussion text
     */
    function testIndexDiscussion()
    {
    }

    /**
     * Index extracted text and discussion
     */
    function testIndexTextAndDiscussion()
    {
    }

    function getDiscussion()
    {
        return "Discussion of the critique of pure reason. It seems a very reasonable document, but
                with a hint of insanity and a dollop of psychosis.";
    }

    function getContent()
    {
        return "THE CRITIQUE OF PURE REASON
                by Immanuel Kant
                translated by J. M. D. Meiklejohn
                PREFACE TO THE FIRST EDITION, 1781
                Human reason, in one sphere of its cognition, is called upon to
                consider questions, which it cannot decline, as they are presented
                by its own nature, but which it cannot answer, as they transcend every
                faculty of the mind.
                It falls into this difficulty without any fault of its own. It
                begins with principles, which cannot be dispensed with in the field
                of experience, and the truth and sufficiency of which are, at the same
                time, insured by experience. With these principles it rises, in
                obedience to the laws of its own nature, to ever higher and more
                remote conditions. But it quickly discovers that, in this way, its
                labours must remain ever incomplete, because new questions never cease
                to present themselves; and thus it finds itself compelled to have
                recourse to principles which transcend the region of experience, while
                they are regarded by common sense without distrust. It thus falls into
                confusion and contradictions, from which it conjectures the presence
                of latent errors, which, however, it is unable to discover, because
                the principles it employs, transcending the limits of experience,
                cannot be tested by that criterion. The arena of these endless
                contests is called Metaphysic.";
    }
}

?>