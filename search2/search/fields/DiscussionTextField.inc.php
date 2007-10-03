<?php


class DiscussionTextField extends SearchableText
{
    public function __construct()
    {
        parent::__construct('Discussion', _kt('Discussion Text'));
        $this->setAlias('DiscussionText');
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