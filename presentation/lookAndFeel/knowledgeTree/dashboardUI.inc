<?php

require_once("$default->fileSystemRoot/presentation/Html.inc");

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

function pendingDocumentsHeaders() {
    return "<th align=\"left\" width=\"33%\" class=\"sectionColumns\">
            Title
            </th>
            <th align=\"left\" width=\"33%\" class=\"sectionColumns\">
            Status
            </th>
            <th align=\"left\" width=\"33%\" class=\"sectionColumns\">
            Days
            </th>";
}

function checkedOutDocumentsHeaders() {
    return "<th align=\"left\" width=\"33%\" class=\"sectionColumns\">
            Title
            </th>
            <th align=\"left\" width=\"66%\" class=\"sectionColumns\">
            Days
            </th>";
}

function subscriptionDocumentsHeaders() {
    return "<th align=\"left\" width=\"33%\" class=\"sectionColumns\">
            Title
            </th>
            <th align=\"left\" width=\"33%\" class=\"sectionColumns\">
            Status
            </th>
            <th align=\"left\" width=\"33%\" class=\"sectionColumns\">
            Days
            </th>";
}

function getPage() {
   $sToRender = renderDocumentPath($oDocument) . "\n<br>\n";
    $sToRender .= "<table border = 0>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td>\n";
    $sToRender .= "\t<table border = 0 width = 280>\n";
    $sToRender .= "\t<tr width>\n";
    $sToRender .= "\t\t<td>" . wrapInTable(renderDocumentData($oDocument)) . "</td>\n";
    //$sToRender .= "\t\t<td>" . renderGenericDocumentMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<td>" . wrapInTable(renderGenericMetaData($oDocument)) . "</td>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . wrapInTable(renderTypeSpecificMetaData($oDocument)) . "</td>\n";
    //$sToRender .= "\t\t<td>" . renderTypeSpecificMetaData($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "\t</table>\n";
    $sToRender .= "</td>\n";
    $sToRender .= "<td valign=top>\n";
    $sToRender .= "\t<table border = 0, width = 230>\n";
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td>" . wrapInTable(renderNonEditableDocumentRouting($oDocument)) . "</td>\n";
    //$sToRender .= "\t\t<td>" . renderDocumentRouting($oDocument) . "</td>\n";
    $sToRender .= "\t</tr>";
    $sToRender .= "\t</table>";
    $sToRender .= "</td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>";
    $sToRender .= "<table>\n";
    $sToRender .= "<tr>\n";
    $sToRender .= "<td><a href=\"" . $_SERVER["PHP_SELF"] . "?fDocumentID=" . $oDocument->getID() . "&fForDownload=1\"><img src=\"$default->graphicsUrl/widgets/download.gif\" border=\"0\" /></a></td>\n";
    $sToRender .= "<td><a href=\"" . generateControllerLink("emailDocument", "fDocumentID=" . $oDocument->getID()) . "\"><img src=\"$default->graphicsUrl/widgets/email.gif\" border=\"0\" /></a></td>\n";
    $sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";    
}
?>
