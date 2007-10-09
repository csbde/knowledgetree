<?php

class SavedSearchSubscriptionTrigger
{
    var $aInfo = null;

    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate()
    {
        global $default;

        $document =& $this->aInfo["document"];

        $documentid = $document->getId();

        $sql = "INSERT INTO search_saved_events (document_id) VALUES ($documentid)";
        DBUtil::runQuery($sql);
    }
}


?>