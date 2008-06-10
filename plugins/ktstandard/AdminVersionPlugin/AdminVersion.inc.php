<?php

class AdminVersion
{
    //const KT_VERSION_URL = 'http://version.knowledgetree.com/kt_versions';
    const KT_VERSION_URL = 'http://ktdms.trunk/plugins/ktstandard/AdminVersionPlugin/test/latestVersion.php';

    public static
    function refresh($url = null)
    {
        $aEncoded = array();
        $aVersions = KTUtil::getKTVersions();

        foreach ($aVersions as $k => $v)
        {
           $aEncoded[] = sprintf("%s=%s", urlencode($k), urlencode($v));
        }

        if (empty($url))
            $sUrl = self::KT_VERSION_URL;
        else
            $sUrl = $url;
        $sUrl .= '?' . implode('&', $aEncoded);

        $sIdentifier = KTUtil::getSystemIdentifier();
        $sUrl .= '&' . sprintf("system_identifier=%s", $sIdentifier);

        if (!function_exists('curl_init'))
        {
            $stuff = @file_get_contents($sUrl);
        }
        else
        {
            $ch = @curl_init($sUrl);
            if (!$ch)
            {
                return false;
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $stuff = curl_exec($ch);
            curl_close($ch);
        }
        if ($stuff === false)
        {
            $stuff = "";
        }
        else
        {
            $stuff = str_replace('\'','"', $stuff);
            $decoded = json_decode($stuff);
            if ($decoded === false)
            {
                return false;
            }
            KTUtil::setSystemSetting('ktadminversion_lastcheck', date('Y-m-d H:i:s'));
            KTUtil::setSystemSetting('ktadminversion_lastvalue', serialize($decoded));
        }
    }

    public static
    function isNewVersionAvailable()
    {
        $aVersions = KTUtil::getKTVersions();

        $name = array_keys($aVersions);
        $name = $name[0];
        $version = array_values($aVersions);
        $version = $version[0];

	    $aRemoteVersions = unserialize(KTUtil::getSystemSetting('ktadminversion_lastvalue'));

	    $aVersions = get_object_vars($aRemoteVersions);

	    if (!isset($aVersions[$name]))
	    {
	        return false;
	    }

	    $newVersion = $aRemoteVersions->$name;
	    if (version_compare($version, $newVersion) == -1)
	    {
	        return array('name'=>$name,'version'=>$aRemoteVersions->$name);
	    }

        return false;
    }
}

?>