<?php
require_once (KT_DIR . '/tests/test.php');
require_once (KT_DIR . '/ktapi/ktapi.inc.php');

/**
* These are the unit tests for the main KTAPI class
*
*/
class RESTTestCase extends KTUnitTestCase {

    /**
    * @var object $ktapi The main ktapi object
    */
    var $ktapi;

    /**
    * @var object $session The KT session object
    */
    var $session;

    /**
     * @var string $rootUrl The root server url for the rest web service
     */
    var $rootUrl;

    /**
    * This method sets up the server url
    *
    */
    public function setUp()
    {
        $url = KTUtil::kt_url();
        $this->rootUrl = $url.'/ktwebservice/KTWebService.php?';
    }

    /**
    * This method is a placeholder
    */
    public function tearDown()
    {
    }

    /**
     * Test login
     */
    public function testLogin()
    {
        // Login and authenticate
        $url = $this->rootUrl.'method=login&password=admin&username=admin';
        $response = $this->call($url);
        $response = $response['response'];
        $session_id = $response['results'];

        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(!empty($response['results']));

        // Logout
        $url = $this->rootUrl.'method=logout&session_id='.$session_id;
        $response = $this->call($url);
        $response = $response['response'];

        $this->assertEqual($response['status_code'], 0);
    }

    /**
     * Test the successful running of a method
     */
    public function testFolderDetails()
    {
        // Login and authenticate
        $url = $this->rootUrl.'method=login&password=admin&username=admin';
        $response = $this->call($url);
        $response = $response['response'];

        $this->assertEqual($response['status_code'], 0);
        $session_id = $response['results'];

        $url = $this->rootUrl.'method=get_folder_detail&session_id='.$session_id.'&folder_id=1';
        $response = $this->call($url);
        $response = $response['response'];

        $this->assertEqual($response['status_code'], 0);
        $this->assertTrue(!empty($response['results']));
        $this->assertEqual($response['results']['folder_name'], 'Root Folder');
        $this->assertEqual($response['results']['permissions'], 'RWA');

        // Logout
        $url = $this->rootUrl.'method=logout&session_id='.$session_id;
        $response = $this->call($url);
        $response = $response['response'];

        $this->assertEqual($response['status_code'], 0);
    }

    /**
     * Test incorrect authentication and no authentication
     */
    public function testAuthenticationError()
    {
        // Incorrect password
        $url = $this->rootUrl.'method=login&password=random&username=admin';
        $response = $this->call($url);
        $response = $response['response'];

        $this->assertNotEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));
        $this->assertTrue(!empty($response['message']));

        // No session set up - use a random session id
        $url = $this->rootUrl.'method=get_folder_detail&session_id=09sfirandom3828492&folder_id=1';
        $response = $this->call($url);
        $response = $response['response'];

        $this->assertNotEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));
        $this->assertTrue(!empty($response['message']));
    }

    /**
     * Test incorrect method error and the error response on incorrect parameters
     */
    public function testMethodErrors()
    {
        $url = $this->rootUrl.'method=incorrect_method&parameter=something';
        $response = $this->call($url);
        $response = $response['response'];

        $this->assertNotEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));
        $this->assertTrue(!empty($response['message']));

        $url = $this->rootUrl.'method=get_folder_detail&parameter=something';
        $response = $this->call($url);
        $response = $response['response'];

        $this->assertNotEqual($response['status_code'], 0);
        $this->assertTrue(empty($response['results']));
        $this->assertTrue(!empty($response['message']));
    }

    /**
     * Convert xml into an array structure
     *
     * @param unknown_type $contents
     * @param unknown_type $get_attributes
     * @param unknown_type $priority
     * @return unknown
     */
    private function xml2array($contents, $get_attributes = 1, $priority = 'tag')
    {
        if (!function_exists('xml_parser_create'))
        {
            return array ();
        }
        $parser = xml_parser_create('');

        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);

        if (!$xml_values)
            return; //Hmm...

        $xml_array = array ();
        $parents = array ();
        $opened_tags = array ();
        $arr = array ();
        $current = & $xml_array;
        $repeated_tag_index = array ();
        foreach ($xml_values as $data)
        {
            unset ($attributes, $value);
            extract($data);
            $result = array ();
            $attributes_data = array ();
            if (isset ($value))
            {
                if ($priority == 'tag')
                    $result = $value;
                else
                    $result['value'] = $value;
            }
            if (isset ($attributes) and $get_attributes)
            {
                foreach ($attributes as $attr => $val)
                {
                    if ($priority == 'tag')
                        $attributes_data[$attr] = $val;
                    else
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }
            if ($type == "open")
            {
                $parent[$level -1] = & $current;
                if (!is_array($current) or (!in_array($tag, array_keys($current))))
                {
                    $current[$tag] = $result;
                    if ($attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current = & $current[$tag];
                }
                else
                {
                    if (isset ($current[$tag][0]))
                    {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    }
                    else
                    {
                        $current[$tag] = array (
                            $current[$tag],
                            $result
                        );
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset ($current[$tag . '_attr']))
                        {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset ($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            }
            elseif ($type == "complete")
            {
                if (!isset ($current[$tag]))
                {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                }
                else
                {
                    if (isset ($current[$tag][0]) and is_array($current[$tag]))
                    {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $get_attributes and $attributes_data)
                        {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    }
                    else
                    {
                        $current[$tag] = array (
                            $current[$tag],
                            $result
                        );
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes)
                        {
                            if (isset ($current[$tag . '_attr']))
                            {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset ($current[$tag . '_attr']);
                            }
                            if ($attributes_data)
                            {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                    }
                }
            }
            elseif ($type == 'close')
            {
                $current = & $parent[$level -1];
            }
        }
        return ($xml_array);
    }

    /**
     * Use curl to run the webservice function
     *
     * @param unknown_type $url
     * @return unknown
     */
    private function call($url) {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $xml = curl_exec($c);
        curl_close($c);

        $xmlArray = $this->xml2array($xml);
        return $xmlArray;
    }
}
?>