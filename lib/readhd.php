<?php

#Ugly code by Anders Axesson.
# Adapted to OWL global config file by B0zz

function GetFromHD($GetWhat, $ThePath) {
  if ($Dir = opendir($ThePath)) {
     $FileCount = 0;
     $DirCount = 0;
     while($file = readdir($Dir)) {
       $PathFile = $ThePath . "/" . $file; //must test with full path (is_file etc)

       if(($file <> ".") and ($file <> "..")) {
         if (!is_file($PathFile)) {  //check if it is a folder (dir) or file (dont check if it is a link)
            $DirCount++;
            $Dirs[$DirCount] = $file;
          }else{
            $FileCount++;
            $Files[$FileCount] = $file;
         }
       }
     }
     if ($GetWhat == 'file') {
       $FileCount++;
       $Files[$FileCount] = "[END]";  //stop looping @ this
       return $Files;
     }

     if ($GetWhat == 'folder') {
       $DirCount++;
       $Dirs[$DirCount] = "[END]";  //stop looping @ this
       return $Dirs;
     }
     
   }
}

function GetFileInfo($PathFile) {
  $TheFileSize = filesize($PathFile);  //get filesize
  $TheFileTime = date("Y-m-d H:i:s", filemtime($PathFile));  //get and fix time of last modifikation
  $TheFileTime2 = date("M d, Y \a\\t h:i a", filemtime($PathFile));  //get and fix time of last modifikation


  $FileInfo[1] = $TheFileSize;
  $FileInfo[2] = $TheFileTime; //s$modified
  $FileInfo[3] = $TheFileTime2; //modified

  return $FileInfo;
}

function CompareDBnHD($GetWhat, $ThePath, $DBList, $parent, $DBTable) {  //compare files or folders in database with files on harddrive
  $F = GetFromHD($GetWhat, $ThePath);

$RefreshPage = false;  //if filez/Folderz are found the page need to be refreshed in order to see them.

if(is_array($F)) {

    for($HDLoopCount = 1; $F[$HDLoopCount] !== "[END]";$HDLoopCount++) {

      for($DBLoopCount = 1; $DBList[$DBLoopCount] !== "[END]";$DBLoopCount++) {
        if($F[$HDLoopCount] == $DBList[$DBLoopCount]) {
	  unset($F[$HDLoopCount]); //removing file/folder that is in db from list of filez on disc (leaving list of filez on disc but not in db)
	  break;
        }
      }
    }

  for($HDLoopCount = 1; $F[$HDLoopCount] !== "[END]";$HDLoopCount++) {
    if(ord($F[$HDLoopCount]) !== 0) {  //if not the file/folder name is empty...
      if($GetWhat == "file") {
	$RefreshPage = true;
        InsertHDFilezInDB($F[$HDLoopCount], $parent, $ThePath, $DBTable); //call function that inserts the filez-on-disc-but-not-in-db into the db.
      }else{
	$RefreshPage = false;
      }

      if($GetWhat == "folder") {
	$RefreshPage = true;
        InsertHDFolderzInDB($F[$HDLoopCount], $parent, $ThePath, $DBTable); //call function that inserts the folderz-on-disc-but-not-in-db into the db.
      }
    }
  }

}
 
  return $RefreshPage;

}

function InsertHDFolderzInDB($TheFolder, $parent, $ThePath, $DBTable) {
  global $default;

  $sql = new Owl_DB;  //create new db connection

  $SQL = "insert into $DBTable (name,parent,security,groupid,creatorid) values ('$TheFolder', '$parent', '$default->owl_def_fold_security', '$default->owl_def_fold_group_owner', '$default->owl_def_fold_owner')";

  $sql->query($SQL);
}


function InsertHDFilezInDB($TheFile, $parent, $ThePath, $DBTable) {

  global $default;
  $sql = new Owl_DB;  //create new db connection

  $FileInfo = GetFileInfo($ThePath . "/" . $TheFile);  //get file size etc. 2=File size, 2=File time (smodified), 3=File time 2 (modified)
  
  if ($default->owl_def_file_title == "")
  {
   $title_name =  $TheFile;
  }
  else
  {
     $title_name = $default->owl_def_file_title;
  }

  $SQL = "insert into $DBTable (name,filename,size,creatorid,parent,modified,description,metadata,security,groupid,smodified) values ('$title_name', '$TheFile', '$FileInfo[1]', '$default->owl_def_file_owner', '$parent', '$FileInfo[3]', '$TheFile', '$default->owl_def_file_meta', '$default->owl_def_file_security', '$default->owl_def_file_group_owner','$FileInfo[2]')";
  $sql->query($SQL);
  
}

?>
