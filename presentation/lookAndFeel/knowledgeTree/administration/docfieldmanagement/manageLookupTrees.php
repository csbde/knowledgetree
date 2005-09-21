<?php
require_once("../../../../../config/dmsDefaults.php");
require_once(KT_DIR . "/presentation/Html.inc");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/documentmanagement/MetaData.inc");
require_once(KT_LIB_DIR . "/documentmanagement/MDTree.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
$sectionName = "Administration";
require_once(KT_DIR . "/presentation/webpageTemplate.inc");

class ManageLookupTreeDispatcher extends KTAdminDispatcher {
    function do_main() {
        $oTemplating = new KTTemplating;
        $aTreeFields =& DocumentField::getList('has_lookuptree = 1');
        $aLookupFields =& DocumentField::getList('has_lookup = 1 AND (has_lookuptree IS NULL or has_lookuptree = 0)');
        $oTemplate = $oTemplating->loadTemplate("ktcore/manage_lookuptrees");
        $aTemplateData = array(
             "treefields" => $aTreeFields,
             "lookupfields" => $aLookupFields,
        );
        return $oTemplate->render($aTemplateData);
    }

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
    }

    function do_createTree() {
        // extract.
        $field_id = KTUtil::arrayGet($_REQUEST, 'field_id');

        // validate
        if (empty($field_id)) { return $this->errorRedirectToMain("Must select a field to convert."); }
        $oField =& DocumentField::get($field_id);
        if (PEAR::isError($oField)) { return $this->errorRedirectToMain("Invalid field."); }

        // set as a metadata tree.
        $oField->setHasLookupTree(1);
        $oField->update();
        $this->errorRedirectToMain("Converted ".$oField->getName()." to a tree.");
    }


    // create and display the tree editing form.
    function do_editTree() {
        global $default;
        // extract.
        $field_id = KTUtil::arrayGet($_REQUEST, 'field_id');
        $current_node = KTUtil::arrayGet($_REQUEST, 'current_node', 0);
        $subaction = KTUtil::arrayGet($_REQUEST, 'subaction');

        // validate
        if (empty($field_id)) { return $this->errorRedirectToMain("Must select a field to edit."); }
        $oField =& DocumentField::get($field_id);
        if (PEAR::isError($oField)) { return $this->errorRedirectToMain("Invalid field."); }

        // under here we do the subaction rendering.
        // we do this so we don't have to do _very_ strange things with multiple actions.
        $default->log->debug("Subaction: " . $subaction);
        $fieldTree =& new MDTree();
        $fieldTree->buildForField($oField->getId());

        if ($subaction !== null) {
            if ($subaction === "addCategory") {
                $new_category = KTUtil::arrayGet($_REQUEST, 'category_name');
                if (empty($new_category)) { return $this->errorRedirectTo("editTree", "Must enter a name for the new category.", array("field_id" => $field_id)); }
                else { $this->subact_addCategory($field_id, $current_node, $new_category, $fieldTree);}                
            }       
            if ($subaction === "deleteCategory") {
                $this->subact_deleteCategory($fieldTree, $current_node);
                $current_node = 0;      // clear out, and don't try and render the newly deleted category.                 
            }       
            if ($subaction === "linkKeywords") {
                $keywords = KTUtil::arrayGet($_REQUEST, 'keywordsToAdd');
                $this->subact_linkKeywords($fieldTree, $current_node, $keywords);
                $current_node = 0;      // clear out, and don't try and render the newly deleted category.                 
            }       
            if ($subaction === "unlinkKeyword") {
                $keyword = KTUtil::arrayGet($_REQUEST, 'keyword_id');
                $this->subact_unlinkKeyword($fieldTree, $keyword);
            }       
        }

        if ($fieldTree->root === null) { 
            return $this->errorRedirectToMain("Error building tree. Is this a valid tree-lookup field?"); 
        } 

        // FIXME extract this from MDTree (helper method?)
        $free_metadata = MetaData::getList('document_field_id = '.$oField->getId().' AND (treeorg_parent = 0 OR treeorg_parent IS NULL)');

        // render edit template.
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/edit_lookuptrees");
        $renderedTree = $this->_evilTreeRenderer($fieldTree);
        $aTemplateData = array(
            "field" => $oField,
            "tree" => $fieldTree,
            "renderedTree" => $renderedTree,
            "currentNode" => $current_node,
            "freechildren" => $free_metadata,
        );
        return $oTemplate->render($aTemplateData);
    }

    function subact_addCategory($field_id, $current_node, $new_category, &$constructedTree) {
        $newCategory = MDTreeNode::createFromArray(array (
             "iFieldId" => $field_id,
             "sName" => $new_category,
             "iParentNode" => $current_node,
        ));
        if (PEAR::isError($newCategory))
        {
            return false;
        }
        $constructedTree->addNode($newCategory);
        return true;
    }

    function subact_deleteCategory(&$constructedTree, $current_node) {
        $constructedTree->deleteNode($current_node);
        return true;
    }
    
    function subact_unlinkKeyword(&$constructedTree, $keyword) {
        $oKW = MetaData::get($keyword);
        $constructedTree->reparentKeyword($oKW->getId(), 0);
        return true;
    }


    function subact_linkKeywords(&$constructedTree, $current_node, $keywords) {
        foreach ($keywords as $md_id)
        {
            $constructedTree->reparentKeyword($md_id, $current_node);
        }
        return true;
    }

    /* ----------------------- EVIL HACK --------------------------
     *
     *  This whole thing needs to replaced, as soon as I work out how
     *  to non-sucking Smarty recursion.
     */

    function _evilTreeRecursion($subnode, $treeToRender)
    {
        $treeStr = "<ul>";
        foreach ($treeToRender->contents[$subnode] as $subnode_id => $subnode_val)
        {
            if ($subnode_id !== "leaves") {
                $treeStr .= '<li class="treenode"><a class="pathnode"  onclick="toggleElementClass(\'active\', this.parentNode);">' . $treeToRender->mapnodes[$subnode_val]->getName() . '</a>';
                $treeStr .= $this->_evilActionHelper($treeToRender->field_id, false, $subnode_val);
                $treeStr .= $this->_evilTreeRecursion($subnode_val, $treeToRender);
                $treeStr .= '</li>';
            }
            else
            {
                foreach ($subnode_val as $leaf)
                {
                    $treeStr .= '<li class="leafnode">' . $treeToRender->lookups[$leaf]->getName();
                    $treeStr .= $this->_evilActionHelper($treeToRender->field_id, true, $leaf);
                    $treeStr .=  '</li>';            }
                }
        }
        $treeStr .= '</ul>';
        return $treeStr;
        
    }

    // I can't seem to do recursion in smarty, and recursive templates seems a bad solution.
    // Come up with a better way to do this (? NBM)
    function _evilTreeRenderer($treeToRender) {
        //global $default;
        $treeStr = "<!-- this is rendered with an unholy hack. sorry. -->";
        $stack = array();
        $exitstack = array();

        // since the root is virtual, we need to fake it here.
        // the inner section is generised.
        $treeStr .= '<ul class="kt_treenodes"><li class="treenode"><a class="pathnode"  onclick="toggleElementClass(\'active\', this.parentNode);">Root</a>';
        $treeStr .= ' (<a href="manageLookupTrees.php?action=editTree&field_id='.$treeToRender->field_id.'&current_node=0">edit</a>)';
        $treeStr .= '<ul>';
        //$default->log->debug("EVILRENDER: " . print_r($treeToRender, true));
        foreach ($treeToRender->getRoot() as $node_id => $subtree_nodes)
        {
            //$default->log->debug("EVILRENDER: ".$node_id." => ".$subtree_nodes." (".($node_id === "leaves").")");
            // leaves are handled differently.
            if ($node_id !== "leaves") {
                // $default->log->debug("EVILRENDER: " . print_r($subtree_nodes, true));
                $treeStr .= '<li class="treenode"><a class="pathnode" onclick="toggleElementClass(\'active\', this.parentNode);">' . $treeToRender->mapnodes[$subtree_nodes]->getName() . '</a>';
                $treeStr .= $this->_evilActionHelper($treeToRender->field_id, false, $subtree_nodes);
                $treeStr .= $this->_evilTreeRecursion($subtree_nodes, $treeToRender);
                $treeStr .= '</li>';
            }
            else
            {
                foreach ($subtree_nodes as $leaf)
                {
                    $treeStr .= '<li class="leafnode">' . $treeToRender->lookups[$leaf]->getName();
                    $treeStr .= $this->_evilActionHelper($treeToRender->field_id, true, $leaf);
                    $treeStr .=  '</li>';
                }
            }
        }
        $treeStr .= '</ul></li>';
        $treeStr .= '</ul>';

        return $treeStr;
    }

    // don't hate me.
    function _evilActionHelper($iFieldId, $bIsKeyword, $current_node) {
        $actionStr = " (";
        if ($bIsKeyword === true) {
           $actionStr .= '<a href="manageLookupTrees.php?action=editTree&field_id='.$iFieldId.'&keyword_id='.$current_node.'&subaction=unlinkKeyword">unlink</a>';
        }
        else
        {
           $actionStr .= '<a href="manageLookupTrees.php?action=editTree&field_id='.$iFieldId.'&current_node='.$current_node.'">add items</a> ';
           $actionStr .= '| <a href="manageLookupTrees.php?action=editTree&field_id='.$iFieldId.'&current_node='.$current_node.'&subaction=deleteCategory">delete</a>';
        }
        $actionStr .= ")";
        return $actionStr;
    }

}

$oDispatcher = new ManageLookupTreeDispatcher();
$oDispatcher->dispatch();

?>
