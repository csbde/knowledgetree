<?php

require_once(KT_LIB_DIR . '/util/sanitize.inc');

// more advanced, intelligent columns.

class AdvancedColumn {

    // the internal tracking name
    public $namespace = 'ktcore.columns.base';

    public $label = '';
    public $sort_on = false;
    public $sort_direction = 'asc';
    public $sortable = false;
    public $return_url = null;
    public $aOptions;

    // no params - important
    public function AdvancedColumn()
    {
        $this->label = _kt('Base Column');
    }

    // Meld the internal vars with those from the options.
    public function setOptions($aOptions = null)
    {
        $this->aOptions = kt_array_merge($this->aOptions, $aOptions);
        $this->sortable = KTUtil::arrayGet($this->aOptions, 'sortable', $this->sortable);
        $this->return_url = KTUtil::arrayGet($this->aOptions, 'return_url', $this->return_url);
        $this->sort_on = KTUtil::arrayGet($this->aOptions, 'sort_on', $this->sort_on);
        $this->sort_direction = KTUtil::arrayGet($this->aOptions, 'sort_on', $this->sort_direction);
    }

    public function getOptions()
    {
        return $this->aOptions;
    }

    public function renderHeader()
    {
        // short-circuit
        if (empty($this->label)) {
            return '';
        }

        // For safety.
        $label = htmlentities($this->label, ENT_NOQUOTES, 'UTF-8');

        // Without sorthing to sort on, don't bother.
        if (empty($this->namespace)) {
            $this->sortable = false;        // if we haven't set which column we're sorted by, do nothing.
        }

        // No sorting, no link.
        if (!$this->sortable) {
            return $label;
        }

        // Merge the sorting options into the header.
        $sortOrder = $this->sort_direction == 'asc' ? 'desc' : 'asc';
        $qs = sprintf('sort_on=%s&sort_order=%s', $this->namespace, $sortOrder);
        if (is_null($this->return_url)) {
            $url = KTUtil::addQueryStringSelf($qs);
        }
        else {
            $url = KTUtil::addQueryString($this->return_url, $qs);
        }

        return sprintf('<a href="%s">%s</a>', $url, $label);
    }

    public function renderData($dataRow)
    {
        if ($dataRow['type'] == 'folder') {
            return $this->name . ': '. $dataRow['folder']->getName();
        }
        else {
           return $this->name . ': '. $dataRow['document']->getName();
        }
    }

    public function setSortedOn($isSortedOn) { $this->sort_on = $isSortedOn; }
    public function getSortedOn() { return $this->sort_on; }
    public function setSortDirection($sortDirection) { $this->sort_direction = $sortDirection; }
    public function getSortDirection() { return $this->sort_direction; }

    public function addToFolderQuery() { return array(null, null, null); }
    public function addToDocumentQuery() { return array(null, null, null); }

    public function getName()
    {
        return sanitizeForSQLtoHTML($this->label);
    }

    public function getEntryId()
    {
        return KTUtil::arrayGet($this->aOptions, 'column_id', null);
    }

    public function getRequiredInView()
    {
        return KTUtil::arrayGet($this->aOptions, 'required_in_view', null);
    }

}

?>
