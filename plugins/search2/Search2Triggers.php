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

        $sql = "SELECT document_id FROM search_saved_events WHERE document_id=$documentid";
		$rs = DBUtil::getResultArray($sql);
		if (count($rs) == 0)
		{
        	$sql = "INSERT INTO search_saved_events (document_id) VALUES ($documentid)";
	        DBUtil::runQuery($sql);
		}
    }
}


?>