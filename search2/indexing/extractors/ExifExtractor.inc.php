<?php

class ExifExtractor extends DocumentExtractor
{
	public function getDisplayName()
	{
		return _kt('Exif Extractor');
	}

	public function getSupportedMimeTypes()
	{
		return array(
			'image/tiff','image/jpeg'
		);
	}

	public function extractTextContent()
	{
		$exif = exif_read_data($this->sourcefile, 0, true);
		$content = '';
		foreach ($exif as $key => $section)
		{
			foreach ($section as $name => $val)
			{
				if (is_numeric($val))
				{
					// no point indexing numeric content. it will be ignored anyways!
					continue;
				}
				$content .= "$val\n";
			}
		}

		$result = file_put_contents($this->targetfile, $content);

		return false !== $result;
	}

	public function diagnose()
	{
		if (!function_exists('exif_read_data'))
		{
			return sprintf(_kt('The Exif extractor requires the module exif php extension. Please include this in the php.ini.'));
		}

		return null;
	}
}

?>