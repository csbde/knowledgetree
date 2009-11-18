<?php
/**
 * $Id:$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

require_once('../../config/dmsDefaults.php');
require_once('../../ktapi/ktapi.inc.php');

class openSearch extends KTStandardDispatcher {
    // XML object
    private $dom;               // DOMDocument
    private $type;              // rss or atom
    private $query;             // Search Query
    private $results;           // Search Results
    private $requestUri;        //
    private $server;            //
    protected $ktapi;           //
    private $session_id;        //
    private $status_code;        //
    private $message;        //
    private $username;        //
    private $password;        //
    // 1.1 Draft parameters
    protected $searchTerms;     // required
    protected $count;           // optional
    protected $startIndex;      // optional
    protected $startPage;       // optional
    protected $language;        // optional
    protected $inputEncoding;   // optional
    protected $outputEncoding;  // optional
    
    private $osQuery;           // Query Element

    public function openSearch() {
        $this->dom = new DOMDocument("1.0", "UTF-8");
        $this->query = "";
        $this->searchTerms = "";
        $this->count = 20;
        $this->startIndex = 1;
        $this->startPage = 1;
        $this->language = "";
        $this->inputEncoding = "UTF-8";
        $this->outputEncoding = "UTF-8";
        $this->type = "rss";
        $this->osQuery = new osQuery();
        $this->requestUri = '';
        $this->results = false;
        $this->ktapi = null;
        $this->status_code = 1;
        $this->message = "";
    }

/* Getters */
    function getQuery($txtQuery) {
    	$query = str_replace(array("\r\n", "\r", "\n"), array(' ', ' ', ' '), $txtQuery);
    	$query = strip_tags($query);

        return $query;
    }

    function getSearchTerms($query) {
        if(preg_match("/\"([^\"\"]+|\"?R\")*\"/",$query,$matches)) {
            $query = $matches[1];
        }

        return $query;
    }

    function getResults($query) {
    	$this->status_code = 1;
		$response['results'] = array();
        $expr = parseExpression($query);
        $results = $expr->evaluate();

        //echo "<pre>";print_r($rs);echo "</pre>";die;
		if (PEAR::isError($results)) {

			return false;
		}
		if(empty($results)){
    		$this->message = _kt('Your search did not return any results');
		}
		$this->status_code = 0;
		$response['results'] = $results;

		return $response;
    }

    function getTotalResults() {
        return count($this->results['results']['docs'])+count($this->results['results']['folders']);
    }
/* Setters */
    // Set search terms
    function setQuery($query) {
        $this->query = $query;
    }

    // Set search terms
    function setSearchTerms($searchTerms) {
        $this->searchTerms = $searchTerms;
    }

    // Set count
    function setCount($count) {
        $this->count = $count;
    }

    // Set start index
    function setStartIndex($startIndex) {
        $this->startIndex = $startIndex;
    }

    // Set start page
    function setStartPage($startPage) {
        $this->startPage = $startPage;
    }

    // Set language
    function setLanguage($lang) {
        $this->language = $lang;
    }

    // Set input encoding
    function setInputEncoding($inputEncoding) {
        $this->inputEncoding = $inputEncoding;
    }

    // Set output encoding
    function setOutputEncoding($outputEncoding) {
        $this->outputEncoding = $outputEncoding;
    }

    // Set xml output type
    function setType($type) {
        $this->type = $type;
    }

    // Set search results
    function setResults($results) {
        $this->results = $results;
    }

    // Set up Open Search Query
    function setOSQuery() {
        $this->osQuery->setTotalResults($this->getTotalResults());
        $this->osQuery->setTitle($this->searchTerms);
        $this->osQuery->setRoles();
    }

    // Set search URL
    function setRequestUri($requestUri) {
        $this->requestUri = $requestUri;
    }

    // Set session id
    function setSessionId($session_id) {
        $this->session_id = $session_id;
    }

    // Set host
    function setServer($server) {
        $this->server = $server;
    }

    // Set username
    function setUsername($username) {
        $this->username = $username;
    }

    // Set password
    function setPassword($password) {
        $this->password = $password;
    }
/* Helpers */
    function presetParams() { // Set params needed regardless
        if(isset($_REQUEST['session_id'])) { $this->setSessionId($_REQUEST['session_id']); }
        if(isset($_REQUEST['type'])) { $this->setType($_REQUEST['type']); }
    }

    // Split request and instantiate open search object
    function setParams() { // Set all needed params
        if(isset($_REQUEST['txtQuery'])) {
            $query = $this->getQuery($_REQUEST['txtQuery']);
            $this->setQuery($query);
            $this->setSearchTerms($this->getSearchTerms($query));
            $this->setResults($this->getResults($query));
        }
        if(isset($_REQUEST['count'])) { $this->setCount($_REQUEST['count']); }
        if(isset($_REQUEST['starti'])) { $this->setStartIndex($_REQUEST['starti']); }
        if(isset($_REQUEST['startp'])) { $this->setStartPage($_REQUEST['startp']); }
        if(isset($_REQUEST['kt_language'])) { $this->setLanguage($_REQUEST['kt_language']); }
        if(isset($_SERVER['REQUEST_URI'])) { $this->setRequestUri($_SERVER['REQUEST_URI']); }
        if(isset($_SERVER['HTTP_HOST'])) { $this->setServer($_SERVER['HTTP_HOST']); }
        $this->setOSQuery();
    }

    private function build_feed() {
        if($this->type == "atom") { $this->build_atom(); } else { $this->build_rss(); }
    }

    private function build_atom() {
        $dom_response = $this->dom->appendChild($this->dom->createElement("response"));
        $dom_response->setAttribute("xmlns", "http://www.w3.org/2005/Atom");
        $dom_response->setAttribute("xmlns:opensearch", "http://a9.com/-/spec/opensearch/1.1/");
        if(!$this->status_code) {
            $dom_response = $this->body_atom($dom_response);
            $dom_response = $this->opensearch_results($dom_response); // Add search results in open search format
        }
        $channel_statusCode = $dom_response->appendChild($this->dom->createElement("status_code"));
        $channel_statusCode->appendChild($this->dom->createTextNode("{$this->status_code}"));
        if($this->message != '') {
            $channel_message = $dom_response->appendChild($this->dom->createElement("message"));
            $channel_message->appendChild($this->dom->createTextNode("{$this->message}"));
        }
    }

    private function body_atom($dom_response) {
        $channel_title = $dom_response->appendChild($this->dom->createElement("title"));
        $channel_title->appendChild($this->dom->createTextNode("KnowledgeTree Search: {$this->searchTerms}"));
        $channel_subtitle = $dom_response->appendChild($this->dom->createElement("subtitle"));
        $channel_subtitle->appendChild($this->dom->createTextNode('Search metadata and content on KnowledgeTree'));
        $channel_author = $dom_response->appendChild($this->dom->createElement("author"));
        $author_name = $channel_author->appendChild($this->dom->createElement("name"));
        $author_name->appendChild($this->dom->createTextNode("KnowledgeTree"));
        $channel_numResults = $dom_response->appendChild($this->dom->createElement("opensearch:totalResults"));
        $channel_numResults->appendChild($this->dom->createTextNode("{$this->osQuery->getTotalResults()}"));
        $channel_index = $dom_response->appendChild($this->dom->createElement("opensearch:startIndex"));
        $channel_index->appendChild($this->dom->createTextNode("{$this->startIndex}"));
        $channel_itemsPerPage = $dom_response->appendChild($this->dom->createElement("opensearch:itemsPerPage"));
        $channel_itemsPerPage->appendChild($this->dom->createTextNode("{$this->count}"));

        return $dom_response;
    }

    private function build_rss() {
        $dom_rss = $this->dom->appendChild($this->dom->createElement("rss"));
        $dom_rss->setAttribute("version", "2.0");
        $dom_rss->setAttribute("xmlns:opensearch", "http://a9.com/-/spec/opensearch/1.1/");
        $dom_rss->setAttribute("xmlns:atom", "http://www.w3.org/2005/Atom");
        $rss_channel = $dom_rss->appendChild($this->dom->createElement("channel"));
        if(!$this->status_code) { 
            $rss_channel = $this->body_rss($rss_channel);
            $rss_channel = $this->opensearch_results($rss_channel); // Add search results in open search format
        }
        $channel_statusCode = $rss_channel->appendChild($this->dom->createElement("status_code"));
        $channel_statusCode->appendChild($this->dom->createTextNode("{$this->status_code}"));
        if($this->message != '') {
            $channel_message = $rss_channel->appendChild($this->dom->createElement("message"));
            $channel_message->appendChild($this->dom->createTextNode("{$this->message}"));
        }
    }

    private function body_rss($rss_channel) {
        $channel_title = $rss_channel->appendChild($this->dom->createElement("title"));
        $channel_title->appendChild($this->dom->createTextNode("KnowledgeTree Search: {$this->searchTerms}"));
        $channel_description = $rss_channel->appendChild($this->dom->createElement("description"));
        $channel_description->appendChild($this->dom->createTextNode('Search metadata and content on KnowledgeTree'));
        $channel_numResults = $rss_channel->appendChild($this->dom->createElement("opensearch:totalResults"));
        $channel_numResults->appendChild($this->dom->createTextNode("{$this->osQuery->getTotalResults()}"));
        $channel_index = $rss_channel->appendChild($this->dom->createElement("opensearch:startIndex"));
        $channel_index->appendChild($this->dom->createTextNode("{$this->startIndex}"));
        $channel_itemsPerPage = $rss_channel->appendChild($this->dom->createElement("opensearch:itemsPerPage"));
        $channel_itemsPerPage->appendChild($this->dom->createTextNode("{$this->count}"));

        return $rss_channel;
    }

    private function opensearch_results($channel) { // Add search results in open search format
//        echo '<pre>';print_r($this->results);echo '</pre>';die;
        if($this->results) {
            $this->encodeDocs($channel, $this->results['results']['docs']);
            $this->encodeFolders($channel, $this->results['results']['folders']);
        }

        return $channel;
    }

    private function encodeDocs($channel, $docs) {
        foreach($docs as $doc) {
            if($this->type == 'atom') {
                $channel = $this->adocument_item($channel, $doc);
            } else {
                $channel = $this->document_item($channel, $doc);
            }
        }
    }

    private function encodeFolders($channel, $folders) {
        foreach($folders as $folder) {
            if($this->type == 'atom') {
                $channel = $this->afolder_item($channel, $folder);
            } else {
                $channel = $this->folder_item($channel, $folder);
            }
        }
    }

    private function adocument_item($channel, $doc) {
        $channel_entry = $channel->appendChild($this->dom->createElement("entry"));
        $channel_author = $channel_entry->appendChild($this->dom->createElement("author"));
        $channel_author->appendChild($this->dom->createTextNode("{$doc->createdBy}"));
        $channel_id = $channel_entry->appendChild($this->dom->createElement("id"));
        $channel_id->appendChild($this->dom->createTextNode("{$doc->id}"));
        $channel_title = $channel_entry->appendChild($this->dom->createElement("title"));
        $channel_title->appendChild($this->dom->createTextNode("{$doc->title}"));
        $channel_link = $channel_entry->appendChild($this->dom->createElement("link"));
        $channel_link->setAttribute("href", "http://{$this->server}/view.php?fDocumentId={$doc->id}");
        $channel_updated = $channel_entry->appendChild($this->dom->createElement("updated"));
        $channel_updated->appendChild($this->dom->createTextNode("{$doc->dateModified}"));

        return $channel;
    }

    private function afolder_item($channel, $folder) {
        $channel_entry = $channel->appendChild($this->dom->createElement("entry"));
        $channel_author = $channel_entry->appendChild($this->dom->createElement("author"));
        $channel_author->appendChild($this->dom->createTextNode("{$folder->createdBy}"));
        $channel_id = $channel_entry->appendChild($this->dom->createElement("id"));
        $channel_id->appendChild($this->dom->createTextNode("{$folder->id}"));
        $channel_title = $channel_entry->appendChild($this->dom->createElement("title"));
        $channel_title->appendChild($this->dom->createTextNode("{$folder->title}"));
        $channel_link = $channel_entry->appendChild($this->dom->createElement("link"));
        $channel_link->setAttribute("href", "http://{$this->server}/view.php?fFolderId={$folder->id}");
        $channel_updated = $channel_entry->appendChild($this->dom->createElement("updated"));
        $channel_updated->appendChild($this->dom->createTextNode("{$folder->dateModified}"));

        return $channel;
    }

    private function document_item($channel, $doc) { // Document results
        $channel_entry = $channel->appendChild($this->dom->createElement("item"));
        $channel_author = $channel_entry->appendChild($this->dom->createElement("author"));
        $channel_author->appendChild($this->dom->createTextNode("{$doc->createdBy}"));
        $channel_id = $channel_entry->appendChild($this->dom->createElement("guid"));
        $channel_id->appendChild($this->dom->createTextNode("{$doc->id}"));
        $channel_title = $channel_entry->appendChild($this->dom->createElement("title"));
        $channel_title->appendChild($this->dom->createTextNode("{$doc->title}"));
        $channel_link = $channel_entry->appendChild($this->dom->createElement("link"));
        $channel_link->setAttribute("href", "http://{$this->server}/view.php?fDocumentId={$doc->id}");

        return $channel;
    }

    private function folder_item($channel, $folder) { // Document results
        $channel_entry = $channel->appendChild($this->dom->createElement("item"));
        $channel_author = $channel_entry->appendChild($this->dom->createElement("author"));
        $channel_author->appendChild($this->dom->createTextNode("{$folder->createdBy}"));
        $channel_id = $channel_entry->appendChild($this->dom->createElement("guid"));
        $channel_id->appendChild($this->dom->createTextNode("{$folder->id}"));
        $channel_title = $channel_entry->appendChild($this->dom->createElement("title"));
        $channel_title->appendChild($this->dom->createTextNode("{$folder->title}"));
        $channel_link = $channel_entry->appendChild($this->dom->createElement("link"));
        $channel_link->setAttribute("href", "http://{$this->server}/view.php?fFolderId={$folder->id}");

        return $channel;
    }

    private function toRSS() {
        header('Content-Type: application/rss+xml; charset=utf-8;');
        header('Content-Disposition: inline; filename="rss.xml"');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        
        echo $this->dom->saveXML();
    }

    private function toAtom() {
        header('Content-Type: application/rss+xml; charset=utf-8;');
        header('Content-Disposition: inline; filename="rss.xml"');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

        echo $this->dom->saveXML();
    }

    private function outputRSS() {
        header('Content-Type: text/xml');
        echo $this->dom->saveXML();
    }

    private function outputAtom() {
        header('Content-Type: text/xml');
        echo $this->dom->saveXML();
    }

    public function auth() {
        $ktapi = $this->get_ktapi($_REQUEST['session_id']);// instantiate KTAPI and invoke method
        if(PEAR::isError($ktapi)) {
            $this->message = _kt('API could not be authenticated');

            return false;
        }

        return true;
    }

    /**
     * Instantiate KTAPI and get the active session, if the session id is supplied
     *
	 * @author KnowledgeTree Team
	 * @access protected
     * @param string $session_id
     * @return KTAPI
     */
    protected function &get_ktapi($session_id = null) {
    	if (!is_null($this->ktapi)) {
    		return $this->ktapi;
    	}
    	$kt = new KTAPI();
    	if(!empty($session_id)) { // if the session id has been passed through - get the active session.
        	$session = $kt->get_active_session($session_id, null);
        	if (PEAR::isError($session)) { 

                return $session;
        	}
    	}
    	$this->ktapi = $kt;
        
    	return $kt;
    }

    /**
     * Creates a new session for the user.
     *
     * @param string $username
     * @param string $password
     * @param string $ip
     * @return kt_response
     */
    public function login() {
        if(isset($_REQUEST['type'])) { $this->setType($_REQUEST['type']); }
        if(isset($_REQUEST['username'])) { $this->setUsername($_REQUEST['username']); }
        if(isset($_REQUEST['password'])) { $this->setPassword($_REQUEST['password']); }
    	$kt = new KTAPI();
    	$session = $kt->start_session($this->username,$this->password, $ip);
    	if (PEAR::isError($session))
    	{
            $this->status_code = 1;
    		$this->message = $session->getMessage();

            return $this->login_fail();
    	}
    	$session = $session->get_session();
    	$this->status_code = 0;
    	$this->message = "";
        $this->results = $session;

        return $this->login_pass($response);
    }

    private function login_fail() {
        if($this->type == 'atom') {
            $response = $this->dom->appendChild($this->dom->createElement("response"));
        } else {
            $response = $this->dom->appendChild($this->dom->createElement("rss"));
            $response->setAttribute("version", "2.0");
            $response->setAttribute("xmlns:opensearch", "http://a9.com/-/spec/opensearch/1.1/");
            $response->setAttribute("xmlns:atom", "http://www.w3.org/2005/Atom");
        }
        $response_status = $response->appendChild($this->dom->createElement("status_code"));
        $response_status->appendChild($this->dom->createTextNode("{$this->status_code}"));
        $response_message = $response->appendChild($this->dom->createElement("message"));
        $response_message->appendChild($this->dom->createTextNode("{$this->message}"));
        if($this->type == 'atom') {
            $this->outputAtom();
        } else {
            $this->outputRSS();
        }
    }

    private function login_pass() {
        if($this->type == 'atom') {
            $response = $this->dom->appendChild($this->dom->createElement("response"));
        } else {
            $response = $this->dom->appendChild($this->dom->createElement("rss"));
            $response->setAttribute("version", "2.0");
            $response->setAttribute("xmlns:opensearch", "http://a9.com/-/spec/opensearch/1.1/");
            $response->setAttribute("xmlns:atom", "http://www.w3.org/2005/Atom");
        }
        $response_status = $response->appendChild($this->dom->createElement("status_code"));
        $response_status->appendChild($this->dom->createTextNode("{$this->status_code}"));
        $response_results = $response->appendChild($this->dom->createElement("results"));
        $response_results->appendChild($this->dom->createTextNode("{$this->results}"));
        if($this->type == 'atom') {
            $this->outputAtom();
        } else {
            $this->outputRSS();
        }
    }
    
    public function driver($test = false) {
        if(isset($_GET['method'])) {
            if($_GET['method'] == 'login')
                return $this->$_GET['method']();
            else
                $this->message = 'Unknown Method';
        }
        $this->presetParams();
        if($this->auth()) { $this->setParams(); } else { $this->message = "API could not be authenticated";}
        $this->build_feed();
        if($this->type == 'atom') {
            if(!$test) { $this->driverAtom(); } else { $this->outputAtom(); }
        } else {
            if(!$test) { $this->driverRss(); } else { $this->outputRSS(); }
        }
    }

    public function driverAtom() {
        $this->toAtom();
    }

    public function driverRss() {
        $this->toRSS();
    }
}

class osQuery {
    // 1.1 Query Element
    private $role;              // Contains a string identifying how the search client should interpret the search request defined by this Query
    private $totalResults;      // Contains the expected number of results to be found if the search request were made.
    private $title;             // Contains a human-readable plain text string describing the search request.

    function osQuery() {
        $this->totalResults = 0;
        $this->title = "";
        $this->role = array();
    }

/* Getters */
    function getTotalResults() {
        return $this->totalResults;
    }

    function setTotalResults($totalResults) {
        $this->totalResults = $totalResults;
    }

/* Setters */
    function setTitle($title) {
        $this->title = $title;
    }

    function setRoles() {
        $this->role["request"] = new queryRole("request", "");      // request Represents the search query that can be performed to retrieve the same set of search results.
        $this->role["example"] = new queryRole("example", "");      // example Represents a search query that can be performed to demonstrate the search engine.
        $this->role["related"] = new queryRole("related", "");      // related Represents a search query that can be performed to retrieve similar but different search results.
        $this->role["correction"] = new queryRole("related", "");      // correction Represents a search query that can be performed to improve the result set, such as with a spelling correction.
        $this->role["subset"] = new queryRole("subset", "");        // subset Represents a search query that will narrow the current set of search results.
        $this->role["superset"] = new queryRole("superset", "");    // superset Represents a search query that will broaden the current set of search results.
    }

/* Helpers */
}

class queryRole {
    private $role;
    private $url;

    public function queryRole($role, $url) {
        $this->role = $role;
        $this->url = $url;
    }

    function setRole($role) {
        $this->role = $role;
    }

    function setUrl($url) {
        $this->url = $url;
    }
}

$os = new openSearch();
$os->driver(true);
?>