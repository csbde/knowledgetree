<?php

class WorkflowStateIDField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('workflow_state_id', 'document_metadata_version', _kt('Workflow State ID'));
        $this->setAlias('WorkflowStateID');
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