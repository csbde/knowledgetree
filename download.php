<?php

/*
 * download.php
 *
 * Copyright (c) 1999-2002 The Owl Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
*/

require("./config/owl.php");
require("./lib/owl.lib.php");
require("./config/html.php");
require("./lib/security.lib.php");

function find_path($parent) {
	global $parent, $default;
        $path = fid_to_name($parent);
        $sql = new Owl_DB;
        while($parent != 1) {
                $sql->query("select parent from $default->owl_folders_table where id = '$parent'");
                while($sql->next_record()) {
                        $path = fid_to_name($sql->f("parent"))."/".$path;
                        $parent = $sql->f("parent");
                }
        }
        return $path;
}

function fid_to_filename($id) {
	global $default;
	$sql = new Owl_DB;
	$sql->query("select filename from $default->owl_files_table where id = '$id'");
	while($sql->next_record()) return $sql->f("filename");
}

function zip_folder($id, $userid) {

  global $default, $sess;
  
  $tmpdir = $default->owl_FileDir . "/owltmpfld_$sess.$id";
  //if (file_exists($tmpdir)) system("rm -rf " . escapeshellarg($tmpdir));
  if (file_exists($tmpdir)) myDelete($tmpdir);

  mkdir("$tmpdir", 0777);
  //system("mkdir " . escapeshellarg($tmpdir));
  $sql = new Owl_DB;
  $sql2 = new Owl_DB;

  $sql->query("select name, id from $default->owl_folders_table where id = '$id'");
  while($sql->next_record()) {
    $top= $sql->f("name");
  }
  $path = "$tmpdir/$top";
  mkdir("$path", 0777);
  //system("mkdir " . escapeshellarg($path));

  folder_loop($sql, $sql2, $id, $path, $userid);
  // get all files in folder 
  // GETTING IE TO WORK IS A PAIN!
  if (strstr($_SERVER["HTTP_USER_AGENT"], "MSIE"))
        header("Content-Type: application/x-gzip");
  else
        header("Content-Type: application/octet-stream");


  header("Content-Disposition: attachment; filename=\"$top.tgz\"");
  header("Content-Location: \"$top.tgz\"");
 // header("Content-Length: $fsize");
  header("Expires: 0");
  //header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  //header("Pragma: Public");
  
  if (file_exists($default->tar_path)) {
	if (file_exists($default->gzip_path)) {
	 //passthru("$default->tar_path -C ". escapeshellarg($tmpdir) . " -zc " . escapeshellarg($top));
	passthru("$default->tar_path cf - -C ". escapeshellarg($tmpdir) . " " . escapeshellarg($top) . "| " . $default->gzip_path . " -c -9");
	} else {
	 //passthru("$default->tar_path -C ". escapeshellarg($tmpdir) . " -zc " . escapeshellarg($top));
	passthru("$default->tar_path cf - -C ". escapeshellarg($tmpdir) . " " . escapeshellarg($top) );
	}
  } else {
  	myDelete($tmpdir);
	printError("$default->tar_path was not found","");
  }
  myDelete($tmpdir);
  //system("rm -rf " . escapeshellarg($tmpdir));
}



//function folder_loop(&$sql, &$sql2, $id, $tmpdir, $userid) {
function folder_loop($sql, $sql2, $id, $tmpdir, $userid) {

  global $default;

  if(check_auth($id, "folder_view", $userid) == 1) {

    $sql = new Owl_DB;
    // write out all the files
    $sql->query("select * from $default->owl_files_table where parent = '$id'");
    while($sql->next_record()) {
      $fid = $sql->f("id");
      $filename = $tmpdir . "/" . $sql->f("filename");
      if(check_auth($fid, "file_download", $userid) == 1) {

        if ($default->owl_use_fs) {
	   $source = $default->owl_FileDir . "/" . get_dirpath($id) . "/" . $sql->f("filename");
	   copy($source, $filename);
	}
	else {
	$sql2->query("select data,compressed from " . $default->owl_files_data_table . " where id='$fid'");
        while($sql2->next_record()) {
          if ($sql2->f("compressed")) {

            $fp=fopen($filename . ".gz","w");
            fwrite($fp, $sql2->f("data"));
            fclose($fp);
            system($default->gzip_path . " -d " . escapeshellarg($filename) .".gz");

          } else {
            $fp=fopen($filename,"w");
            fwrite($fp, $sql2->f("data"));
            fclose($fp);
          } // end if     

          } // end if     

        } // end while

      } // end if

    } // end while

    // recurse into directories
    $sql->query("select name, id from $default->owl_folders_table where parent = '$id'");
    while($sql->next_record()) {
      $saved = $tmpdir;
      $tmpdir .= "/" . $sql->f("name");
      mkdir("$tmpdir", 0777);
      //system("mkdir " . escapeshellarg($tmpdir));	
      folder_loop($sql, $sql2, $sql->f("id"), $tmpdir, $userid);
      $tmpdir = $saved;
    }
  }
}



if ($action == "folder") {
  $abort_status = ignore_user_abort(true);
  zip_folder($id, $userid);
  ignore_user_abort($abort_status);
  exit;
}

if(check_auth($id, "file_download", $userid) == 1) {
	$filename = fid_to_filename($id);
	$mimeType = "application/octet-stream";

	if ($binary != 1) {
		if ($filetype = strrchr($filename,".")) {
		        $filetype = substr($filetype,1);
			$sql = new Owl_DB;
			$sql->query("select * from $default->owl_mime_table where filetype = '$filetype'");
			while($sql->next_record()) $mimeType = $sql->f("mimetype");
		}
	}

	// BEGIN wes change

        if ($default->owl_use_fs) {
          $path = find_path($parent)."/".$filename;
          $fspath = $default->owl_FileDir."/".$path;
          $fsize = filesize($fspath);
        } else {
          $sql->query("select size from " . $default->owl_files_table . " where id='$id'");
          while($sql->next_record()) $fsize = $sql->f("size");
        }
        // END wes change


	// BEGIN BUG: 495556 File download sends incorrect headers
        // header("Content-Disposition: filename=\"$filename\"");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Location: $filename");
	header("Content-Type: $mimeType");
	header("Content-Length: $fsize");
	//header("Pragma: no-cache");
	header("Expires: 0");
	// END BUG: 495556 File download sends incorrect headers

        // BEGIN wes change
        if ($default->owl_use_fs) {
        if (substr(php_uname(), 0, 7) != "Windows")
		$fp=fopen("$fspath","r");
        else
                $fp=fopen("$fspath","rb");
        print fread($fp,filesize("$fspath"));
        fclose($fp);
        } else {
          $sql->query("select data,compressed from " . $default->owl_files_data_table . " where id='$id'");
          while($sql->next_record()) {
            if ($sql->f("compressed")) {
              $tmpfile = $default->owl_FileDir . "owltmp.$id";
              if (file_exists($tmpfile)) unlink($tmpfile);

              $fp=fopen($tmpfile,"w");
              fwrite($fp, $sql->f("data"));
              fclose($fp);
              flush(passthru($default->gzip_path . " -dfc $tmpfile"));
              unlink($tmpfile);
            } else {
              print $sql->f("data");
              flush();
            }
          }
        }
        // END wes change
} else {
	print($lang_nofileaccess);
}

?>
