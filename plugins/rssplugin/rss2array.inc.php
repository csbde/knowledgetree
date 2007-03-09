<?

/**
 *
 * Copyright (c) 2007 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.knowledgetree.com/
 */
    

    #
    # global vars
    #

    global $rss2array_globals;

    #
    # fetch_feed
    #

    function rss2array($url){

        global $rss2array_globals;

        #
        # empty our global array
        #

        $rss2array_globals = array();

        #
        # if the URL looks ok
        #

        if(preg_match("/^http:\/\/([^\/]+)(.*)$/", $url, $matches)){
			
            $host = $matches[1];
            $uri = $matches[2];
            $hostSplit = explode(':', $host);            
            if(isset($hostSplit[1])){
            	$port = $hostSplit[1];
				$host = $hostSplit[0];
            }else{
            	$port = '80';
            }
            $request = "GET $uri HTTP/1.0\r\n";
			$request .= "Host: $host\r\n";
            $request .= "User-Agent: RSSMix/0.1 http://www.rssmix.com\r\n";
			$request .= "Connection: close\r\n\r\n";

            #
            # open the connection
            #

            if($http = fsockopen($host, $port, $errno, $errstr, 5)){

                #
                # make the request
                #

                fwrite($http, $request);

                #
                # read in for max 5 seconds
                #

                $timeout = time() + 5;

                while(time() < $timeout && !feof($http)) {

                    $response .= fgets($http, 4096);

                }

                #
                # split on two newlines
                #

                list($header, $xml) = preg_split("/\r?\n\r?\n/", $response, 2);

                #
                # get the status
                #

                if(preg_match("/^HTTP\/[0-9\.]+\s+(\d+)\s+/", $header, $matches)){

                    $status = $matches[1];

                    #
                    # if 200 OK
                    #

                    if($status == 200){

                        #
                        # create the parser
                        #

                        $xml_parser = xml_parser_create();

                        xml_set_element_handler($xml_parser, "startElement", "endElement");
                        xml_set_character_data_handler($xml_parser, "characterData");

                        #
                        # parse!
                        #

                        xml_parse($xml_parser, trim($xml), true) or $rss2array_globals[errors][] = xml_error_string(xml_get_error_code($xml_parser)) . " at line " . xml_get_current_line_number($xml_parser);

                        #
                        # free parser
                        #

                        xml_parser_free($xml_parser);

                    }

                    else {

                        $rss2array_globals[errors][] = "Can't get feed: HTTP status code $status";

                    }

                }

                #
                # Can't get status from header
                #

                else {

                    $rss2array_globals[errors][] = "Can't get status from header";

                }

            }

            #
            # Can't connect to host
            #

            else {

                $rss2array_globals[errors][] = "Can't connect to $host";

            }

        }

        #
        # Feed url looks wrong
        #

        else {

            $rss2array_globals[errors][] = "Invalid url: $url";

        }

        #
        # unset all the working vars
        #

        unset($rss2array_globals[channel_title]);

        unset($rss2array_globals[inside_rdf]);
        unset($rss2array_globals[inside_rss]);
        unset($rss2array_globals[inside_channel]);
        unset($rss2array_globals[inside_item]);

        unset($rss2array_globals[current_tag]);
        unset($rss2array_globals[current_title]);
        unset($rss2array_globals[current_link]);
        unset($rss2array_globals[current_description]);

        return $rss2array_globals;

    }
    
    function rss2arrayBlock($xml){

        global $rss2array_globals;

        #
        # empty our global array
        #

        $rss2array_globals = array();
      

        #
        # create the parser
        #

        $xml_parser = xml_parser_create();

        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($xml_parser, "characterData");

        #
        # parse!
        #

        xml_parse($xml_parser, trim($xml), true) or $rss2array_globals[errors][] = xml_error_string(xml_get_error_code($xml_parser)) . " at line " . xml_get_current_line_number($xml_parser);

        #
        # free parser
        #

        xml_parser_free($xml_parser);


        #
        # unset all the working vars
        #

        unset($rss2array_globals[channel_title]);

        unset($rss2array_globals[inside_rdf]);
        unset($rss2array_globals[inside_rss]);
        unset($rss2array_globals[inside_channel]);
        unset($rss2array_globals[inside_item]);

        unset($rss2array_globals[current_tag]);
        unset($rss2array_globals[current_title]);
        unset($rss2array_globals[current_link]);
        unset($rss2array_globals[current_description]);

        return $rss2array_globals;

    }

    #
    # this function will be called everytime a tag starts
    #

    function startElement($parser, $name, $attrs){

        global $rss2array_globals;

        $rss2array_globals[current_tag] = $name;

        if($name == "RSS"){

            $rss2array_globals[inside_rss] = true;

        }

        elseif($name == "RDF:RDF"){

            $rss2array_globals[inside_rdf] = true;

        }

        elseif($name == "CHANNEL"){

            $rss2array_globals[inside_channel] = true;
            $rss2array_globals[channel_title] = "";

        }

        elseif(($rss2array_globals[inside_rss] and $rss2array_globals[inside_channel]) or $rss2array_globals[inside_rdf]){

            if($name == "ITEM"){

                $rss2array_globals[inside_item] = true;

            }

            elseif($name == "IMAGE"){

                $rss2array_globals[inside_image] = true;

            }

        }

    }

    #
    # this function will be called everytime there is a string between two tags
    #

    function characterData($parser, $data){

        global $rss2array_globals;

        if($rss2array_globals[inside_item]){

            switch($rss2array_globals[current_tag]){

                case "TITLE":
                $rss2array_globals[current_title] .= $data;
                break;
                case "DESCRIPTION":
                $rss2array_globals[current_description] .= $data;
                break;
                case "LINK":
                $rss2array_globals[current_link] .= $data;
                break;

            }

        }

        elseif($rss2array_globals[inside_image]){

        }

        elseif($rss2array_globals[inside_channel]){

            switch($rss2array_globals[current_tag]){

                case "TITLE":
                $rss2array_globals[channel_title] .= $data;
                break;

            }

        }

    }

    #
    # this function will be called everytime a tag ends
    #

    function endElement($parser, $name){

        global $rss2array_globals;

        #
        # end of item, add complete item to array
        #

        if($name == "ITEM"){

            $rss2array_globals[items][] = array(title => trim($rss2array_globals[current_title]), link => trim($rss2array_globals[current_link]), description => trim($rss2array_globals[current_description]));

            #
            # reset these vars for next loop
            #

            $rss2array_globals[current_title] = "";
            $rss2array_globals[current_description] = "";
            $rss2array_globals[current_link] = "";

            $rss2array_globals[inside_item] = false;

        }

        elseif($name == "RSS"){

            $rss2array_globals[inside_rss] = false;

        }

        elseif($name == "RDF:RDF"){

            $rss2array_globals[inside_rdf] = false;

        }

        elseif($name == "CHANNEL"){

            $rss2array_globals[channel][title] = trim($rss2array_globals[channel_title]);

            $rss2array_globals[inside_channel] = false;

        }

        elseif($name == "IMAGE"){

            $rss2array_globals[inside_image] = false;

        }

    }

?>