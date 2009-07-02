<?php
/**
 * AtomPub Service: fulltree
 *
 * Returns a full tree listing starting at the root document
 * Tree structure obtained by referencing parent id
 *
 */
class ktAPP_Service_fullTree extends ktAPP_Service {
	public function GET_action(){
		//Create a new response feed
		$feed=new KTAPPFeed(KT_APP_BASE_URI);

		//Invoke the KtAPI to get detail about the referenced document
		$tree=KTAPPHelper::getFullTree();

		//Create the atom response feed
		foreach($tree as $item){
			$id=$item['id'];
			$entry=$feed->newEntry();
			$feed->newField('id',$id,$entry);
			foreach($item as $property=>$value){
				$feed->newField($property,$value,$entry);
			}
		}
		//Expose the responseFeed
		$this->responseFeed=$feed;
	}

	public function DELETE_action(){
		$feed = new ktAPP_ResponseFeed_DELETE();
		$this->responseFeed=$feed;
	}
}




/**
 * AtomPub Service: folder
 *
 * Returns detail on a particular folder
 *
 */
class ktAPP_Service_folder extends ktAPP_Service {
	public function GET_action(){
		//Create a new response feed
		$feed=new KTAPPFeed(KT_APP_BASE_URI);

		//Invoke the KtAPI to get detail about the referenced document
		$folderDetail=KTAPPHelper::getFolderDetail($this->params[0]?$this->params[0]:1);

		//Create the atom response feed
		$entry=$feed->newEntry();
		foreach($folderDetail as $property=>$value){
			$feed->newField($property,$value,$entry);
		}

		//Expose the responseFeed
		$this->responseFeed=$feed;
	}
}




/**
 * AtomPub Service: document
 *
 * Returns detail on a particular document
 *
 */
class ktAPP_Service_document extends ktAPP_Service {
	public function GET_action(){
		//Create a new response feed
		$feed=new KTAPPFeed(KT_APP_BASE_URI);

		//Invoke the KtAPI to get detail about the referenced document
		$docDetail=KTAPPHelper::getDocumentDetail($this->params[0]);

		//Create the atom response feed
		$entry=$feed->newEntry();
		foreach($docDetail['results'] as $property=>$value){
			$feed->newField($property,$value,$entry);
		}
		//Add a downloaduri field manually
		$feed->newField('downloaduri',urlencode(KT_APP_SYSTEM_URI.'/action.php?kt_path_info=ktcore.actions.document.view&fDocumentId='.$docDetail['results']['document_id']),$entry);

		//Expose the responseFeed
		$this->responseFeed=$feed;
	}
}
?>