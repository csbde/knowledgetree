<?php

class TagField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('tag', 'tag_words', 'Tag');
        $this->setAlias('Tag');
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::TEXT));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$is);
    }
}

?>