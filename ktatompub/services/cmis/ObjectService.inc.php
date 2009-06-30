<?php

require_once KT_LIB_DIR . '/api/ktcmis/ktcmis.inc.php';

/**
 * CMIS Service class which hooks into the KnowledgeTree interface
 * for processing of CMIS queries and responses via atompub/webservices
 */

class ObjectService extends KTObjectService {

    /**
     * Fetches the properties for the specified object
     *
     * @param string $repositoryId
     * @param string $objectId
     * @param boolean $includeAllowableActions
     * @param boolean $includeRelationships
     * @param boolean $returnVersion
     * @param string $filter
     * @return object CMIS object properties
     */
    public function getProperties($repositoryId, $objectId, $includeAllowableActions, $includeRelationships,
                                  $returnVersion = false, $filter = '')
    {
        $result = parent::getProperties($repositoryId, $objectId, $includeAllowableActions,
                                        $returnVersion, $filter);

        if ($result['status_code'] == 0)
        {
            return $result['results'];
        }
    }

}

?>
