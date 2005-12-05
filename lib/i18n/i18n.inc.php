<?

class KTi18n {
    function KTi18n($sDomain, $sPath) {
        $this->sDomain = $sDomain;
        $this->sPath = $sPath;
    }

    function gettext($sContents) {
        return dcgettext($this->sDomain, $sContents, LC_MESSAGES);
    }
}

class KTi18nGeneric {
    function KTi18n() {
    }

    function gettext($sContents) {
        return $sContents;
    }
}
