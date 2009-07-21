<?php
/**
 * AtomPub Service: fulltree
 *
 * Returns a full tree listing starting at the root document
 * Tree structure obtained by referencing parent id
 *
 */
class KT_atom_service_fulltree extends KT_atom_service {
	public function GET_action(){
		//Create a new response feed

		$feed=new KT_atom_ResponseFeed_GET(KT_APP_BASE_URI);

		//Invoke the KtAPI to get detail about the referenced document
		$tree=KT_atom_service_helper::getFullTree();

		//Create the atom response feed
		foreach($tree as $item){
			$id=$item['id'];
			$entry=$feed->newEntry();
			$feed->newField('id',$id,$entry);
			foreach($item as $property=>$value){
				$feed->newField($property,$value,$entry);
			}
		}
		$this->setStatus(self::STATUS_OK);
		//Expose the responseFeed
		$this->responseFeed=$feed;
	}

	public function DELETE_action(){
		$feed = new KT_atom_ResponseFeed_DELETE();
		$this->responseFeed=$feed;
	}
}




/**
 * AtomPub Service: folder
 *
 * Returns detail on a particular folder
 *
 */
class KT_atom_service_folder extends KT_atom_service {
	public function GET_action(){
		//Create a new response feed
		$feed=new KT_atom_Response_GET(KT_APP_BASE_URI);

		//Invoke the KtAPI to get detail about the referenced document
		$folderDetail=KT_atom_service_helper::getFolderDetail($this->params[0]?$this->params[0]:1);

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
class KT_atom_service_document extends KT_atom_service {
	public function GET_action(){
		//Create a new response feed
		$feed=new KT_atom_responseFeed(KT_APP_BASE_URI);

		//Invoke the KtAPI to get detail about the referenced document
		$docDetail=KT_atom_service_helper::getDocumentDetail($this->params[0]);

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








class KT_atom_service_test extends KT_atom_service{
	public function GET_action(){
		$feed=new KT_atom_ResponseFeed_GET(KT_APP_BASE_URI);
		$elem=$feed->newElement('test','Responding to a GET request');
		$feed->DOM->appendChild($elem);
		$this->setStatus(self::STATUS_OK);
		$this->responseFeed=$feed;
	}
	public function PUT_action(){
		$feed=new KT_atom_ResponseFeed_GET(KT_APP_BASE_URI);
		$elem=$feed->newElement('test','Responding to a PUT request');
		$feed->DOM->appendChild($elem);
		$this->setStatus(self::STATUS_OK);
		$this->responseFeed=$feed;
	}
	public function POST_action(){
		$feed=new KT_atom_ResponseFeed_GET(KT_APP_BASE_URI);
		$elem=$feed->newElement('test','Responding to a POST request');
		$feed->DOM->appendChild($elem);
		$this->setStatus(self::STATUS_OK);
		$this->responseFeed=$feed;
	}
	public function DELETE_action(){
		$feed=new KT_atom_ResponseFeed_GET(KT_APP_BASE_URI);
		$elem=$feed->newElement('test','Responding to a DELETE request');
		$feed->DOM->appendChild($elem);
		$this->setStatus(self::STATUS_OK);
		$this->responseFeed=$feed;
	}
}










class KT_atom_service_logout extends KT_atom_service{
	public function GET_action(){
		//$this->setStatus(self::STATUS_OK);
		KT_atom_HTTPauth::logout();
		ob_end_clean();
		KT_atom_HTTPauth::login('KnowledgeTree AtomPub','You are not allowed on this realm');
		exit;
	}
	public function PUT_action(){}
	public function POST_action(){}
	public function DELETE_action(){}
}
?>