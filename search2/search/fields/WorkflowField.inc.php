<?php

class WorkflowField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('workflow_id', 'document_metadata_version', 'Workflow');
        $this->setAlias('Workflow');
        $this->joinTo('workflows', 'id');
		$this->matchField('name');
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