<?php

include_once(KT_ATOM_LIB_FOLDER.'KT_atom_responseFeed.inc.php');

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
		$this->feed->appendChild($this->newAttr('xmlns:cmis', 'http://docs.oasis-open.org/ns/cmis/core/200901'));
        
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

    // this is ALL going away...adjust all calling code...
    /*
    protected function constructHeader()
    {
        if (!is_null($this->id))
        {
            $this->newId($this->id, $this->feed);
        }

        $link = $this->newElement('link');
		$link->appendChild($this->newAttr('rel','self'));
		$link->appendChild($this->newAttr('href', $this->baseURI . trim($_SERVER['QUERY_STRING'], '/')));
		$feed->appendChild($link);

        if (!is_null($this->title))
        {
            $this->feed->appendChild($this->newElement('title', $this->title));
        }

        $this->DOM->appendChild($this->feed);
	}

    public function &newId($id, $entry = null)
    {
		$id = $this->newElement('id', $id);
        if(isset($entry))$entry->appendChild($id);
		return $id;
	}

    public function &newField($name = NULL, $value = NULL, &$entry = NULL)
    {
        $append = false;

        if(func_num_args() > 3)
        {
            $append = ((func_get_arg(3) === true) ? true : false);
		}

        $field = $this->newElement($name, $value);

		if (isset($entry)) $entry->appendChild($field);
        else if ($append) $this->feed->appendChild($field);

		return $field;
	}
\
     * 
     */

}

class KT_cmis_atom_ResponseFeed_GET extends KT_cmis_atom_responseFeed{}
class KT_cmis_atom_ResponseFeed_PUT extends KT_cmis_atom_responseFeed{}
class KT_cmis_atom_ResponseFeed_POST extends KT_cmis_atom_responseFeed{}
class KT_cmis_atom_ResponseFeed_DELETE extends KT_cmis_atom_responseFeed{}

?>