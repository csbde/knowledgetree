<?php

class requestHandler {

    private $rawRequestObject;
    private $requests = array();

    public function __construct()
    {
        $this->rawRequestObject = isset($_GET['request']) ? $_GET['request'] : (isset($_POST['request']) ? $_POST['request'] : '');
        $req = new jsonWrapper($this->rawRequestObject);

        // if we have a packaged request set, we need to loop over this and create a set of requests
        if ($req->packaged) {
            $this->requests = $this->structArray('package', $req->jsonArray['package']);
        }
        else {
            $this->requests[] = $req;
        }
    }

    public function getRequests()
    {
        return $this->requests;
    }

    public function getRawRequest()
    {
        return $this->rawRequestObject;
    }

}

?>