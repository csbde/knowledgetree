<?php

include_once(KT_ATOM_LIB_FOLDER.'KT_atom_service.inc.php');

class KT_cmis_atom_service extends KT_atom_service {

	// override and extend as needed

    static protected $authData = array();

    protected function parseHeaders()
    {
//        echo "PARSE HEADERS\n";
        parent::parseHeaders();
//        echo "CHECKING HEADERS\n";
//        print_r($this->headers);
//        print_r($_SERVER);
        // attempt to fetch auth info from supplied headers
        if (!empty($this->headers['Authorization']))
        {
            $auth = base64_decode(preg_replace('/Basic */', '', $this->headers['Authorization']));
            $authData = explode(':', $auth);
//            print_r($authData);
            self::$authData['username'] = $authData[0];
            self::$authData['password'] = $authData[1];
        }
        // if failed, attempt to fetch from $_SERVER array instead
        else if (isset($_SERVER['PHP_AUTH_USER']))
        {
            self::$authData['username'] = $_SERVER['PHP_AUTH_USER'];
            self::$authData['password'] = $_SERVER['PHP_AUTH_PW'];
        }
	}

}
?>