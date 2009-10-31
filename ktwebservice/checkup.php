<?php
require_once('../config/dmsDefaults.php');

$config = KTConfig::getSingleton();

$cacheEnabled = $config->get('cache/cacheEnabled')?'The cache appears to be enabled. This is known to cause problems with the webservice. Please disable it.':'OK';

$uploadsDir = $config->get('webservice/uploadDirectory');

if (empty($uploadsDir)) $uploadsDir = 'The webservice/uploadDirectory setting is blank in the config.ini. Please configure it to an appropriate setting.';

$uploadsExists = !is_dir($uploadsDir)?'The upload directory does not exist.':'OK';
$uploadsWritable = !is_writable($uploadsDir)?'The upload directory is not writable.':'OK';

?>
<B>Basic Web Service Diagnosis</b>

<table>
<tr>
	<td>KnowledgeTree Cache</td>
	<td><?php print $cacheEnabled?></td>
</tr>
<tr>
	<td>Upload Directory</td>
	<td><?php print $uploadsDir?></td>
</tr>
<tr>
	<td>Upload Directory Exists</td>
	<td><?php print $uploadsExists?></td>
</tr>
<tr>
	<td>Upload Directory Writable</td>
	<td><?php print $uploadsWritable?></td>
</tr>
</table>
