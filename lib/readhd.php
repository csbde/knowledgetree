<?php

/**
 * ReadHD.php
 *
 * this is used for file system manipulation
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * @version v 1.1.1.1 2002/12/04
 * @author michael
 * @package Owl
 */
 

#Ugly code by Anders Axesson.
# Adapted to OWL global config file by B0zz


//-------------------------------------------------------------
/**
 *   Function GetFromHD($GetWhat, $ThePath)
 *
 *   Retrieves files/folders from the Hard Drive, given
 *   a file/folder to get and a path
 *
 *   @param $GetWhat
 *	The File/Folder(s) that needs to be retrieved 
 *   @param $ThePath
 *	The Path to Search for the File/Folder(s)
 *   @Return $Files
 *	Returns an array of Files that needs to be retrieved 
 *   @Return $Folders
 *	Returns an array Folder(s) that needs to be retrieved 
*/
//-------------------------------------------------------------
// Usable

function GetFromHD($GetWhat, $ThePath)
 {
  if ($Dir = opendir($ThePath))
   {
     $FileCount = 0;
     $DirCount = 0;
     while($file = readdir($Dir))
      {
       $PathFile = $ThePath . "/" . $file; //must test with full path (is_file etc)

       if(($file <> ".") and ($file <> "..")) 
       {
         if (!is_file($PathFile)) 
         {  //check if it is a folder (dir) or file (dont check if it is a link)
            $DirCount++;
            $Dirs[$DirCount] = $file;
          }
          else
          {
            $FileCount++;
            $Files[$FileCount] = $file;
         }
       }
     }
     // if it is a file add it to an array of files and return it
     if ($GetWhat == 'file') 
     {
       $FileCount++;
       $Files[$FileCount] = "[END]";  //stop looping @ this
       return $Files;
     }
     
     // if it is a folder add it to the array of folders and return it
     if ($GetWhat == 'folder') 
     {
       $DirCount++;
       $Dirs[$DirCount] = "[END]";  //stop looping @ this
       return $Dirs;
     }
     
   }
}

//-------------------------------------------------------------
/**
 *   Function GetFileInfo($PathFile)
 *
 *   Gets the information on the specified file i.e. modification
 *   and file size
 *
 *   @param $PathFile
 *	The Path to the File
 *   @Return $FileInfo
 *	Returns an array with the information of the file
*/
//-------------------------------------------------------------
// Usable
function GetFileInfo($PathFile) {
  $TheFileSize = filesize($PathFile);  //get filesize
  $TheFileTime = date("Y-m-d H:i:s", filemtime($PathFile));  //get and fix time of last modifikation
  $TheFileTime2 = date("M d, Y \a\\t h:i a", filemtime($PathFile));  //get and fix time of last modifikation


  $FileInfo[1] = $TheFileSize;
  $FileInfo[2] = $TheFileTime; //s$modified
  $FileInfo[3] = $TheFileTime2; //modified

  return $FileInfo;
}


//-------------------------------------------------------------
/**
 *   Function CompareDBnHD($GetWhat, $ThePath, $DBList, $parent, $DBTable) 
 *
 *   Compare files or folders in database with files on harddrive
 *
 *   @param $GetWhat
 *	The File/Folder(s) that will be compared
 *   @param $ThePath
 *	The Path  of the File/Folder(s)
 *   @param $DBList
 *	The List of files in the DB
 *   @param $Parent
 *      The parent folder id
 *   @param $DBTable
 *	The DBTable to compare to
 *   @Return $RefreshPage
 *	Return true or false if page needs to be refreshed
*/
//-------------------------------------------------------------
// Usable

function CompareDBnHD($GetWhat, $ThePath, $DBList, $parent, $DBTable) {  //compare files or folders in database with files on harddrive
  
  // get from HD the relevant Files/Folders, store in array
  $F = GetFromHD($GetWhat, $ThePath);

$RefreshPage = false;  //if filez/Folders are found the page need to be refreshed in order to see them.

// if array exists
if(is_array($F)) 
{

// loop through file/folderarray and Dblist array to compare them
    for($HDLoopCount = 1; $F[$HDLoopCount] !== "[END]";$HDLoopCount++) 
    {

      for($DBLoopCount = 1; $DBList[$DBLoopCount] !== "[END]";$DBLoopCount++)
       {
        if($F[$HDLoopCount] == $DBList[$DBLoopCount]) 
        {
	  unset($F[$HDLoopCount]); //removing file/folder that is in db from list of filez on disc (leaving list of filez on disc but not in db)
	  break;
        }
      }
    }

// if certain files/Folders are not in the DB but are on the list, add them to the DB
  for($HDLoopCount = 1; $F[$HDLoopCount] !== "[END]";$HDLoopCount++) 
  {
    if(ord($F[$HDLoopCount]) !== 0) 
    {  //if not the file/folder name is empty...
      if($GetWhat == "file") 
      {
	$RefreshPage = true;
        InsertHDFilezInDB($F[$HDLoopCount], $parent, $ThePath, $DBTable); //call function that inserts the files-on-disc-but-not-in-db into the db.
        
      }
      else
      {
	$RefreshPage = false;
      }

      if($GetWhat == "folder") 
      {
	$RefreshPage = true;
        InsertHDFolderzInDB($F[$HDLoopCount], $parent, $ThePath, $DBTable); //call function that inserts the folders-on-disc-but-not-in-db into the db.
      }
    }
  }

}
 // return true or false
  return $RefreshPage;

}

//-------------------------------------------------------------
/**
 *   Function InsertHDFolderzInDB($TheFolder, $parent, $ThePath, $DBTable) 
 *
 *   Compare files or folders in database with files on harddrive
 *
 *   @param $TheFolder
 *	The Folder to be inserted
 *   @param $Parent
 *      The parent folder id
 *   @param $ThePath
 *	The Path  of the Folder
 *   @param $DBTable
 *	The DBTable to insert into
 */
//-------------------------------------------------------------
// Usable
function InsertHDFolderzInDB($TheFolder, $parent, $ThePath, $DBTable) 
{
  global $default;

  $sql = new Owl_DB;  //create new db connection

  $SQL = "insert into $DBTable (name,parent,security,groupid,creatorid) values ('$TheFolder', '$parent', '$default->owl_def_fold_security', '$default->owl_def_fold_group_owner', '$default->owl_def_fold_owner')";

  $sql->query($SQL);
}


//-------------------------------------------------------------
/**
 *   Function InsertHDFilezInDB($TheFile, $parent, $ThePath, $DBTable) 
 *
 *   Compare files or folders in database with files on harddrive
 *
 *   @param $TheFile
 *	The Folder to be inserted
 *   @param $Parent
 *      The parent folder id
 *   @param $ThePath
 *	The Path  of the File
 *   @param $DBTable
 *	The DBTable to insert into
 */
//-------------------------------------------------------------
// Usable
function InsertHDFilezInDB($TheFile, $parent, $ThePath, $DBTable) {

  global $default;
  $sql = new Owl_DB;  //create new db connection

  $FileInfo = GetFileInfo($ThePath . "/" . $TheFile);  //get file size etc. 2=File size, 2=File time (smodified), 3=File time 2 (modified)
  
  // if there is no file title assign it to default file title
  if ($default->owl_def_file_title == "")
  {
   $title_name =  $TheFile;
  }
  else
  {
     $title_name = $default->owl_def_file_title;
  }

// insert into DB
  $SQL = "insert into $DBTable (name,filename,size,creatorid,parent,modified,description,metadata,security,groupid,smodified) values ('$title_name', '$TheFile', '$FileInfo[1]', '$default->owl_def_file_owner', '$parent', '$FileInfo[3]', '$TheFile', '$default->owl_def_file_meta', '$default->owl_def_file_security', '$default->owl_def_file_group_owner','$FileInfo[2]')";
  $sql->query($SQL);
  
}

?>
