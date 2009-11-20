<?php
// retrieve directories and files
$here = realpath(dirname(__FILE__));
echo $here;
$directoryTree = scan_directory_recursively($here);
//print_r($directoryTree);
$firstLine = "<?php";
$secondLine = "/**";
$closingLine = "*/";
foreach ($directoryTree as $aNode) {
	$isFirstLine = true;
	if($aNode['kind'] == 'file') {
		$fileLines = getFileByLine($aNode['path']);
		foreach ($fileLines as $fileLine) {
			$line = trim($line);
			if($isFirstLine) {
				if($line == $firstLine) {

				} else {

				}
			}
		}
	}
}

function getFileByLine($path) {
	echo "replace $path<br/>";
	$fileLines = array();
	$file_handle = fopen($path, "rb");
	while (!feof($file_handle) ) {
		$line_of_text = fgets($file_handle);
		$parts = explode('=', $line_of_text);
		$fileLines[] = trim($line_of_text);
	}
	fclose($file_handle);

	return $fileLines;
}

function scan_directory_recursively($directory, $filter=FALSE)
{
	if(substr($directory,-1) == '/')
	{
		$directory = substr($directory,0,-1);
	}
	if(!file_exists($directory) || !is_dir($directory))
	{
		return FALSE;
	}elseif(is_readable($directory))
	{
		$directory_list = opendir($directory);
		while($file = readdir($directory_list))
		{
			if($file != '.' && $file != '..')
			{
				$path = $directory.'/'.$file;
				if(is_readable($path))
				{
					$subdirectories = explode('/',$path);
					if(is_dir($path))
					{
						$directory_tree[] = array(
							'path'      => $path,
							'name'      => end($subdirectories),
							'kind'      => 'directory',
							'content'   => scan_directory_recursively($path, $filter));
					}elseif(is_file($path))
					{
						$extension = end(explode('.',end($subdirectories)));
						if($filter === FALSE || $filter == $extension)
						{
							$directory_tree[] = array(
							'path'		=> $path,
							'name'		=> end($subdirectories),
							'extension' => $extension,
							'size'		=> filesize($path),
							'kind'		=> 'file');
						}
					}
				}
			}
		}
		closedir($directory_list);
		return $directory_tree;
	}else{
		return FALSE;
	}
}
?>