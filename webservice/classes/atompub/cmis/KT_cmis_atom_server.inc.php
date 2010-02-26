<?php

include_once(KT_ATOM_LIB_FOLDER . 'KT_atom_server.inc.php');
require_once(CMIS_API . '/ktRepositoryService.inc.php');

class KT_cmis_atom_server extends KT_atom_server {

    // override and extend as needed
    public $repositoryInfo;
    public $headersSet = false;
    
    protected function hook_beforeDocRender($doc)
    {
        if ($doc->isContentDownload())
        {
            // not going to create a feed/entry response, just returning the raw data
            $this->output = $doc->getOutput(); 
            
            // generic headers for all content downloads
            header('Cache-Control: must-revalidate');
            // these two are to override the default header values
            header('Expires:');
            header('Pragma:');
            
            // prevent output of standard text/xml header
            $this->headersSet = true;
            
            return false;
        }
        else if ($doc->notModified())
        {
            // prevent output of standard text/xml header
            $this->headersSet = true;
            $this->setNoContent(true);
            
            return false;
        }
        
        return true;
    }

    public function initServiceDocument()
    {
		$queryArray = split('/', trim($_SERVER['QUERY_STRING'], '/'));
		$workspace = strtolower(trim($queryArray[0]));
        if ($workspace == 'servicedocument')
        {
            $RepositoryService = new KTRepositoryService();

            // fetch data for response
            $repositories = $RepositoryService->getRepositories();

            // hack for removing one level of access
            $repositories = $repositories['results'];
            
            // fetch for default first repo;  NOTE that this will probably have to change at some point, quick and dirty for now
            // hack for removing one level of access
            $repositoryInfo = $RepositoryService->getRepositoryInfo($repositories[0]['repositoryId']);
            $this->repositoryInfo = $repositoryInfo['results'];
        }
    }
    
	public function serviceDocument()
    {
		$service = new KT_cmis_atom_serviceDoc(KT_APP_BASE_URI);

		foreach($this->services as $workspace => $collection)
        {
			//Creating the Default Workspace for use with standard atomPub Clients
			$ws = $service->newWorkspace();

            $hadDetail=false;
			if(isset($this->workspaceDetail[$workspace]))
            {
                if(is_array($this->workspaceDetail[$workspace]))
                {
                    foreach ($this->workspaceDetail[$workspace] as $wsTag=>$wsValue)
                    {
                        $ws->appendChild($service->newElement($wsTag,$wsValue));
                        $hadDetail=true;
                    }
                }
            }

			if(!$hadDetail) {
				$ws->appendChild($service->newElement('atom:title',$workspace));
			}

            $ws->appendChild($service->newAttr('cmis:repositoryRelationship', $this->repositoryInfo['repositoryRelationship']));
            
            // repository information
            $element = $service->newElement('cmisra:repositoryInfo');
            foreach($this->repositoryInfo as $key => $repoData)
            {
                if ($key == 'rootFolderId') {
                    $repoData = CMIS_APP_BASE_URI . $workspace . '/folder/' . rawurlencode($repoData);
                }

                if (!is_array($repoData)) {
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

            foreach($collection as $serviceName => $serviceInstance)
            {
                foreach($serviceInstance as $instance)
                {
                    $collectionStr = CMIS_APP_BASE_URI . $workspace . '/' . $serviceName . '/'
                                   . (is_array($instance['parameters']) ? implode('/', $instance['parameters']).'/' : '');
                    $col = $service->newCollection($collectionStr, $instance['title'], $instance['collectionType'], $instance['accept'], $ws);
                }
			}
		}
		
//		ob_start();
//		readfile('C:\Users\Paul\Documents\Downloads\cmis_mod_kt.xml');
//		$this->output = ob_get_contents();
//		ob_end_clean();

		$this->output = $service->getAPPdoc();
	}

    public function registerService($workspace = NULL, $serviceName = NULL, $serviceClass = NULL, $title = NULL, 
                                    $serviceParameters = NULL, $collectionType = NULL, $accept = null)
    {
		$workspace = strtolower(trim($workspace));
		$serviceName = strtolower(trim($serviceName));

		$serviceRecord = array(
			'fileName' => $fileName,
			'serviceClass' => $serviceClass,
			'title' => $title,
            'parameters' => $serviceParameters,
            'collectionType' => $collectionType,
            'accept' => $accept
		);

		$this->services[$workspace][$serviceName][] = $serviceRecord;
	}

    public function getRegisteredService($workspace, $serviceName = NULL)
    {
		$serviceName = strtolower(trim($serviceName));
		if(isset($this->services[$workspace][$serviceName])) {
            return $this->services[$workspace][$serviceName][0];
        }

		return false;
	}
    
    public function render()
    {
		ob_end_clean();
        if (!$this->headersSet) header('Content-type: text/xml');

        //include('/var/www/atompub_response.xml');

//        if (false && preg_match('/F1\-children/', $this->output)) {
//            readfile('C:\Users\Paul\Documents\Downloads\alfresco folder tree atompub response.xml');
//        }
//        else if (false && preg_match('/urn:uuid:checkedout/', $this->output)) {
//            readfile('C:\Users\Paul\Documents\Downloads\alfresco checkedout atompub response.xml');
//        }
//        else if (false && preg_match('/urn:uuid:types\-all/', $this->output)) {
//            readfile('C:\Users\Paul\Documents\Downloads\alfresco types atompub response.xml');
//        }
//        else if (false && preg_match('/\<service\>/', $this->output)) {
//            readfile('C:\Users\Paul\Documents\Downloads\cmis_mod_kt.xml');
//        }
//        else {
		  if ($this->renderBody) echo $this->output;
//        }
	}

}

?>
