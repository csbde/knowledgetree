<?php

require_once(KT_ATOM_LIB_FOLDER.'KT_atom_responseFeed.inc.php');

class KT_cmis_atom_responseFeed extends KT_atom_responseFeed {

    // override and extend as needed

    static protected $workspace = null;

    /**
     * Overridden constructor to allow easy adding of additional header attributes
     *
     * @param string $baseURI
     */
    public function __construct($baseURI = null)
    {
        parent::__construct($baseURI);
        
        // append additional tags
		$this->feed->appendChild($this->newAttr('xmlns:app', 'http://www.w3.org/2007/app'));
		$this->feed->appendChild($this->newAttr('xmlns:cmis', 'http://docs.oasis-open.org/ns/cmis/core/200908/'));
		$this->feed->appendChild($this->newAttr('xmlns:cmisra', 'http://docs.oasis-open.org/ns/cmis/restatom/200908/'));
        
        // require the workspace for creating links within responses
        $queryArray = split('/', trim($_SERVER['QUERY_STRING'], '/'));
        $this->workspace = strtolower(trim($queryArray[0]));
	}

    function getWorkspace()
    {
        return $this->workspace;
    }

    // TODO try to get rid of this function
    function appendChild($element)
    {
        $this->feed->appendChild($element);
    }

}

class KT_cmis_atom_ResponseFeed_GET extends KT_cmis_atom_responseFeed{}
class KT_cmis_atom_ResponseFeed_PUT extends KT_cmis_atom_responseFeed{}
class KT_cmis_atom_ResponseFeed_POST extends KT_cmis_atom_responseFeed{}
class KT_cmis_atom_ResponseFeed_DELETE extends KT_cmis_atom_responseFeed{}

?>
