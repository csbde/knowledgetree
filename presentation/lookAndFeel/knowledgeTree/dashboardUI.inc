<?php

//require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");

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
        /*
        return "<a href=\"control.php?action=viewDocument&fDocumentID=" . $this->id . "\">" .
               $this->title . "</a>";
        */
        return "<u>$this->title</u>";
               
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
    /*
    global $default;
    $sQuery = "SELECT D.name, D.modified, DTT.datetime AS created, U.name AS initiator, CONCAT(CONCAT(D.major_version, '.'), D.minor_version) AS version, WDSL.name AS status " .
              "FROM $default->owl_documents_table AS D INNER JOIN $default->owl_web_documents_table AS WD ON WD.document_id = D.ID " .
              "INNER JOIN $default->owl_web_documents_status_table AS WDSL ON WD.status_id = WDSL.id " .
              "INNER JOIN $default->owl_users_table AS U ON U.id = D.creator_id " .
              "INNER JOIN $default->owl_document_transactions_table AS DTT ON DTT.document_id = D.id " .
              "INNER JOIN $default->owl_transaction_types_table AS TT ON DTT.transaction_id = TT.id " .
              "WHERE D.id = " . $oDocument->getID() . " " .
              "AND TT.name LIKE 'Create'";

    $aColumns = array("name", "last_modified", "created", "initiator", "version", "status");
    $aColumnNames = array("Document title", "Last updated", "Created", "Document initiator", "Version", "Status");
    $aColumnTypes = array(1,1,1,1,1);
    $oPatternListFromQuery = & new PatternListFromQuery($sQuery, $aColumns, $aColumnNames, $aColumnTypes);
    $oPatternListFromQuery->setTableHeading("Document Data");
    $oPatternListFromQuery->setTableWidth("90%");
    return $oPatternListFromQuery->render();
    */
    
    // you know the drill
    return array(new tmpDocument(), new tmpDocument(), new tmpDocument());
}

function renderPendingDocuments($aPendingDocumentList) {
    $sToRender  = "\t<tr align=\"left\" bgcolor=\"#996600\">\n";
    $sToRender .= "\t\t<th class=\"sectionHeading\" colspan=\"3\">Pending Documents</th>\n";
    $sToRender .= "\t</tr>\n";
    for ($i = 0; $i < count($aPendingDocumentList); $i++) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "<td>" . $aPendingDocumentList[$i]->getTitleLink() . "</td>\n";
        $sToRender .= "\t</tr>\n";
    }    
    return $sToRender;
}

function renderCheckedOutDocuments($aCheckedOutDocumentList) {
    $sToRender  = "\t<tr align=\"left\" bgcolor=\"#996600\">\n";    
    $sToRender .= "\t\t<th class=\"sectionHeading\" colspan=\"3\">Checked Out Documents</th>\n";
    $sToRender .= "\t</tr>\n";
    
	$sToRender .= "\t<tr>\n";    
    $sToRender .= "<th align=\"left\" width=\"66%\" class=\"sectionColumns\" colspan=\"2\">\n";
    $sToRender .= "Title\n";
    $sToRender .= "</th>\n";
    $sToRender .= "<th align=\"left\" width=\"33%\" class=\"sectionColumns\">\n";
    $sToRender .= "Days\n";
    $sToRender .= "</th>";
    $sToRender .= "\t\t</tr>\n";
    
    for ($i = 0; $i < count($aCheckedOutDocumentList); $i++) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "<td colspan=\"2\">" . $aCheckedOutDocumentList[$i]->getTitleLink() . "</td>\n";
        $sToRender .= "<td>" . $aCheckedOutDocumentList[$i]->getDays() . "</td>\n";
        $sToRender .= "\t</tr>\n";
    }      
    return $sToRender;
}

function renderSubscriptionAlerts($aSubscriptionAlertList) {
    $sToRender  = "\t<tr align=\"left\" bgcolor=\"#996600\">\n";
    $sToRender .= "\t\t<th class=\"sectionHeading\" colspan=\"3\">Subscription Alerts</th>\n";
    $sToRender .= "\t</tr>\n";
    for ($i = 0; $i < count($aSubscriptionAlertList); $i++) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "<td colspan=\"3\">" . $aSubscriptionAlertList[$i]->getAlertLink() . "</td>\n";
        $sToRender .= "\t</tr>\n";
    }    
    return $sToRender;
}

function quickLinkHeaders() {
}

/**
 * Renders the dashboard
 *
 * @param array pending collaboration documents for this user
 * @param array checked out documents for this user
 * @param array subscription alerts for this user
 */
function renderPage($aPendingDocumentList, $aCheckedOutDocumentList, $aSubscriptionAlertList) {
    global $default;
    
    $sToRender  = "<table border=\"0\" width=\"600\" >\n";
	$sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td><img src=\"$default->graphicsUrl/welcome.gif\"/></td>\n";
    
    // some arb welcoming text goes here
    $oUser = User::get($_SESSION["userID"]);    
    $sToRender .= "\t\t<td>Hi" . (strlen($oUser->getName()) > 0 ? " " . $oUser->getName() : "") . ", welcome back to the MRC DMS, part of the Knowledge Tree.</td>\n";
    $sToRender .= "\t</tr>\n";
    
    // dashboard alerts
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td width=\"50%\">\n";
    $sToRender .= "\t\t\t<table width=\"100%\">\n";
    $sToRender .= renderPendingDocuments($aPendingDocumentList);
    $sToRender .= renderCheckedOutDocuments($aCheckedOutDocumentList) . "\n";
    $sToRender .= renderSubscriptionAlerts($aSubscriptionAlertList) . "\n";
    $sToRender .= "\t\t\t</table>\n";
    $sToRender .= "\t\t</td>\n";
    
    // quick links
    $sToRender .= "\t\t<td width=\"50%\" valign=top>\n";
    $sToRender .= "\t\t\t<table width=\"100%\">\n";
    $sToRender .= "\t<tr align=\"left\" bgcolor=\"#996600\">\n";
    $sToRender .= "\t\t<th class=\"sectionHeading\">Quick Links</th>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= quickLinkHeaders();
    $sToRender .= "\t</tr>\n";    
    $sToRender .= "\t\t\t</table>\n";
    
    $sToRender .= "\t\t</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "</table>\n";  
    
    return $sToRender;
}
?>
