<?php

/**
 * Creates the CMIS AtomPub service document
 * 
 * NOTE Includes the results of a repositoryInfo call as well as service links
 */

// NOTE currently we only support one repository, which will be the first one found in the repositories.xml config
// TODO multiple repositories as individual workspaces

include 'services/cmis/RepositoryService.inc.php';
$RepositoryService = new RepositoryService();

// fetch data for response
$repositories = $RepositoryService->getRepositories();
// fetch for default first repo;  NOTE that this will probably have to change at some point, quick and dirty for now
$repositoryInfo = $RepositoryService->getRepositoryInfo($repositories[0]['repositoryId']);

// generate service document
$service = new KTCMISAPPServiceDoc(KT_APP_BASE_URI);
$ws = $service->newWorkspace($repositoryInfo['repositoryName']);
$ws->appendChild($service->newAttr('cmis:repositoryRelationship', $repositoryInfo['repositoryRelationship']));

// repository information
$element = $service->newElement('cmis:repositoryInfo');
foreach($repositoryInfo as $key => $repoData)
{
    if ($key == 'rootFolderId')
    {
        $repoData = CMIS_BASE_URI . 'folder/' . $repoData;
    }

    if (!is_array($repoData))
    {
        $element->appendChild($service->newElement('cmis:' . $key, $repoData));
    }
    else
    {
        $elementSub = $service->newElement('cmis:' . $key);
        foreach($repoData as $key2 => $data)
        {
            $elementSub->appendChild($service->newElement('cmis:' . $key2, CMISUtil::boolToString($data)));
        }
        $element->appendChild($elementSub);
    }
}
$ws->appendChild($element);

// collection links
$col = $service->newCollection(CMIS_BASE_URI . 'folder/' . $repositoryInfo['rootFolderId'] . '/children',
                               'Root Folder Children Collection', 'root-children', $ws);
$col = $service->newCollection(CMIS_BASE_URI . 'folder/' . $repositoryInfo['rootFolderId'] . '/descendants',
                               'Root Folder Descendant Collection', 'root-descendants', $ws);
$col = $service->newCollection(CMIS_BASE_URI . 'checkedout', 'Checked Out Document Collection', 'checkedout', $ws);
$col = $service->newCollection(CMIS_BASE_URI . 'types', 'Object Type Collection', 'types-children', $ws);
$col = $service->newCollection(CMIS_BASE_URI . 'types', 'Object Type Collection', 'types-descendants', $ws);

$output = $service->getAPPdoc();

?>