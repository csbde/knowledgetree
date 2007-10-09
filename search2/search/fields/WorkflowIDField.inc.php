<?php

class WorkflowIDField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('workflow_id', 'document_metadata_version', _kt('Workflow ID'));
        $this->setAlias('WorkflowID');
    }

    public function getInputRequirements()
    {
        return array('value'=>array('type'=>FieldInputType::INT));
    }

    public function is_valid()
    {
        return DefaultOpCollection::validateParent($this, DefaultOpCollection::$is);
    }
}

?>