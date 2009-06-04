<?php

// really wanted to keep KT code out of here but I don't see how
require_once(KT_DIR . '/ktapi/ktapi.inc.php');
require_once(CMIS_DIR . '/objecttypes/CMISDocumentObject.inc.php');
require_once(CMIS_DIR . '/objecttypes/CMISFolderObject.inc.php');
require_once(CMIS_DIR . '/classes/CMISRepository.inc.php');
require_once(CMIS_DIR . '/util/CMISUtil.inc.php');

class CMISObjectService {

    protected $ktapi;

    function CMISObjectService(&$ktapi)
    {
        $this->ktapi = $ktapi;
    }

    /**
     * Fetches the properties for the specified object
     *
     * @param string $repositoryId
     * @param string $objectId
     * @param bool $includeAllowableActions
     * @param bool $includeRelationships
     * @param bool $returnVersion
     * @param string $filter
     * @return object CMIS object properties
     */
    // TODO optional parameter support
    // TODO FilterNotValidException: The Repository SHALL throw this exception if this property filter input parameter is not valid
    function getProperties($repositoryId, $objectId, $includeAllowableActions, $includeRelationships,
                           $returnVersion = false, $filter = '')
    {
        $repository = new CMISRepository($repositoryId);

        // TODO a better default value?
        $properties = array();

        $typeId = CMISUtil::decodeObjectId($objectId);

        switch($typeId)
        {
            case 'Document':
                $CMISObject = new CMISDocumentObject($this->ktapi, $repository->getRepositoryURI());
                break;
            case 'Folder':
                $CMISObject = new CMISFolderObject($this->ktapi, $repository->getRepositoryURI());
                break;
        }

        $CMISObject->get($objectId);
        $properties = $CMISObject->getProperties();

        return $properties;
    }

}

?>
