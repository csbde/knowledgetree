<?php

class WorkflowStateField extends DBFieldExpr
{
    public function __construct()
    {
        parent::__construct('workflow_state_id', 'document_metadata_version', 'Workflow State');
        $this->setAlias('WorkflowState');
        $this->joinTo('workflow_states', 'id');
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