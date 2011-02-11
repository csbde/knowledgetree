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
            // split into multiple requests
            $parameters = $this->structArray('parameters', $req->jsonArray['request']['parameters']['name']);
            foreach ($parameters as $param) {
                if (empty($param)) { continue; }
                // MUST clone because we will be modifying the copy and require the original to remain intact
                // NOTE we may want to do a deep copy, as cloning leaves internal variables which are objects as shared
                //      deep copy can be achieved by $clone = unserialize(serialize($object1));
                //      This incurs a potential performance hit (how much?) so we'll go with shallow copy for now.
                $request = clone $req;
                foreach ($request->jsonArray['request']['parameters']['name'] as $key => $internalParam) {
                    if ($internalParam != $param) {
                        unset($request->jsonArray['request']['parameters']['name'][$key]);
                    }
                }
                $request->jsonArray['request']['parameters']['name'] = $param;
                $this->requests[] = $request;
            }
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

    // messy, abstract out of both classes or get this in the main class?
    /**
	 * Provide a structured array. The resultant array will contain all the keys (empty values) listed in the $structString.
	 * Where these values exist in the passed array $arr, they will be used, otherwise they will be empty.
	 *
	 * @param $structString
	 * @param $arr
	 * @return array
	 */
    private function structArray($structString = null, $arr = null)
    {
        $struct = array_flip(split(',', (string)$structString));
        return array_merge($struct, is_array($arr) ? $arr : array());
    }

}

?>