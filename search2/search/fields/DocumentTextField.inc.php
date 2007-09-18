<?php


class DocumentTextField extends SearchableText
{
    public function __construct()
    {
        parent::__construct('Content', 'Document Text');
        $this->setAlias('DocumentText');
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::FULLTEXT));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$contains);
    }






}

?>