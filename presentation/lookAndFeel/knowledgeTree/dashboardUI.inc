<?php

require_once("$default->owl_fs_root/presentation/Html.inc");

/**
 * $Id$
 *
 * Dashboard page html UI building functions.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation
 */

/**
 * just a tmp document forging class
 */
class tmpDocument {

    var $title;
    var $id;
    var $status;
    var $days;
    var $statuses = array("good", "bad", "indifferent");

    /**
     * generate random attribute data on instantiation
     */
    function tmpDocument() {
        // initialise the random number generator
        srand ((float) microtime() * 10000000);
        
        // generate a random document title
        $number = rand(0,500);
        $this->title = "document title $number";
        $this->id = $number;

        // generate a random status
        $this->status = $this->statuses[array_rand($this->statuses, 1)];

        // random days
        $this->days = rand(0,25);
    }
    
    function getTitleLink() {
        return "<a href=\"control.php?action=viewDocument&fDocumentID=" . $this->id . "\">" .
               $this->title . "</a>";
    }
    
    function getStatus() {
        return $this->status;
    }
    
    function getDays() {
        return $this->days;
    }
}
 
function getPendingDocuments() {
    // generate random document objects and return
    return array(new tmpDocument(), new tmpDocument(), new tmpDocument());
}

function getCheckedoutDocuments() {
    // generate random document objects and return
    return array(new tmpDocument(), new tmpDocument(), new tmpDocument());    
}

function getSubscriptionDocuments() {
    // you know the drill
    return array(new tmpDocument(), new tmpDocument(), new tmpDocument());
}

function tableHeading($cssClassName, $colspan, $text) {
    return "<th colspan=\"$colspan\">
            <span class=\"$cssClassName\">$text</span>
            </th>";
}

function pendingDocumentsHeaders() {
    return "<th align=\"left\" width=\"33%\">
            <span class=\"sectionColumns\">Title</span>
            </th>
            <th align=\"left\" width=\"33%\">
            <span class=\"sectionColumns\">Status</span>
            </th>
            <th align=\"left\" width=\"33%\">
            <span class=\"sectionColumns\">Days</span>
            </th>";
}

function checkedOutDocumentsHeaders() {
    return "<th align=\"left\" width=\"33%\">
            <span class=\"sectionColumns\">Title</span>
            </th>
            <th align=\"left\" width=\"66%\">
            <span class=\"sectionColumns\">Days</span>
            </th>";
}

function subscriptionDocumentsHeaders() {
    return "<th align=\"left\" width=\"33%\">
            <span class=\"sectionColumns\">Title</span>
            </th>
            <th align=\"left\" width=\"33%\">
            <span class=\"sectionColumns\">Status</span>
            </th>
            <th align=\"left\" width=\"33%\">
            <span class=\"sectionColumns\">Days</span>
            </th>";
}
