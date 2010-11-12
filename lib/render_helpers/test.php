<?php

	require_once('../../config/dmsDefaults.php');
	require_once('sharedContent.inc');
	
//	create_object(6, 80, 'folder', 1);
//	create_object(6, 81, 'folder', 0);
//	create_object(6, 83, 'folder', 1);
//	create_object(6, 117, 'document', 1);
//	create_object(6, 118, 'document', 0);
//	
//	function create_object($user_id, $object_id, $object_type, $permission)
//	{
//		$oSharedContent = new SharedContent($user_id, $object_id, $object_type, $permission);
//		if($oSharedContent->exists())
//		{
//			$oSharedContent->delete();
//		}
//		$oSharedContent->create();
//	}

//	SharedContent::canViewDocument(6, 117, 1); // Root
//	SharedContent::canViewDocument(6, 118, 1); // Root
	SharedContent::canViewDocument(6, 121, 83); // 1 Level 3d
	SharedContent::canViewDocument(6, 122, 85); // 2 Level
	
?>