<?php

if (!extension_loaded('mbstring'))
{

	function mb_detect_encoding($str, $encoding_list=null, $strict=null)
	{
		return mb_internal_encoding();
	}

	function mb_strtolower($str)
	{
		return strtolower($str);
	}

	function mb_internal_encoding($encoding=null)
	{
		return 'ISO-8859-1';
	}

	function mb_strlen($str, $encoding=null)
	{
		return strlen($str);
	}

	function mb_substr($str, $start, $length=null, $encoding=null)
	{
		return substr($str, $start, $length);
	}
}

?>