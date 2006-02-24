<?php

require_once(KT_LIB_DIR . "/ktentity.inc");
//require_once("../../../../../config/dmsDefaults.php"); // gak.
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/documentmanagement/MetaData.inc");

class MDTreeNode extends KTEntity {
    /** boilerplate DB code. */
    /** primary key */
    var $iId = -1; 
    var $iFieldId;
    var $sName;
    var $iParentNode;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iFieldId" => "document_field_id",
        "sName" => "name",
        "iParentNode" => "metadata_lookup_tree_parent",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function setID($iId) { $this->iId = $iId; }
    function getFieldId() { return $this->iFieldId; }
    function setFieldId($iFieldId) {  $this->iFieldId = $iFieldId; }
    function getName() { return $this ->sName; }
    function setName($sName) { $this->sName = $sName; }
    function getParentNode() { return $this->iParentNode; }
    function setParentNode($iNode) { $this->iParentNode = $iParentNode; }

    function _table () {
        global $default;
        return $default->metadata_treenode_table;
    }

    // Static Functions (dull)
    function &get($iId) { return KTEntityUtil::get('MDTreeNode', $iId); }
    function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('MDTreeNode', $aOptions); }
    function &getList($sWhereClause = null) { global $default; return KTEntityUtil::getList2('MDTreeNode', $sWhereClause); }

    /** end boilerplate.  anything interesting goes below here. */

}

/* simple class to encapsulate tree-as-a-whole behaviour. 
    NBM - should this move, be refactored?  It certainly doesn't belong in the DB,
            since its just an aggregate utility.
*/
class MDTree {
    var $contents = null;       // private.
    var $mapnodes = null;
    var $root = null;
    var $field_id;
    var $lookups;
    var $activenodes = array();
    var $activevalue = null;

    function getRoot() { return $this->root; }
    function getMapping() { return $this->mapnodes; }
    function clear() {
        $this->contents = null;
        $this->mapnodes = null;
        $this->root = null;
        $this->lookups = null;  
        $this->field_id = null;
    }

    /* function buildForField 
     *
     * build a tree for a particular field instance.
     * sets contents, so we can edit "stuff".
     */
    function buildForField($iFieldId)
     {
        global $default;
        // before we start, we need to check that 
        // the specified field exists and is organised into a tree.
        $organisedField =& DocumentField::get($iFieldId);
        if (PEAR::isError($organisedField) || ($organisedField === false)) {
            $this->clear();  // make sure we don't get pollution.
            return ; // and leave all null.  WHY DOESN'T PHP HAVE EXCEPTIONS?
        }

        if ($organisedField->getHasLookupTree() === false) {
            $this->clear(); // make sure we don't get pollution.
            return ; // not a tree-lookup.
        }
        // right. we are now ready to start with the treebuild.
        // root is a virtual node (id: 0).
        $this->field_id = $iFieldId;
        $orderedTreeNodes =& MDTreeNode::getList('WHERE document_field_id = '.$iFieldId.' ORDER BY metadata_lookup_tree_parent, name ASC');
        
        // since we have these nodes ordered by parent, we can perform a build
        // we can build:
        //      $this->mapnodes   [node_id => node]
        //      $this->root       [node_id => subtree_root_arr]
        //      $this->contents   [node_id => subtree_root_arr]
        // THIS IS IMPORTANT:  BOTH subtree_root_arr are the same object.
        // Without this, we CAN'T build the tree this way.  PLEASE, let PHP support
        // this magic.

        // initialise.

        $this->mapnodes = array(); // will hold the actual nodes, mapped by id.
        $this->contents = array(); // will hold references to each nodes subtree.
        $this->contents[0] = array();

        foreach ($orderedTreeNodes as $treeNode) {

            // step 1: set the map entry for this item.
            $iParent = $treeNode->getParentNode();
            $iCurrId = $treeNode->getId();
            $this->mapnodes[$iCurrId] = $treeNode; // always works, setting our own value.

            $parent_arr = null;
            if (!array_key_exists($iParent, $this->contents)) { $this->contents[$iParent] = array(); }
            if (!array_key_exists($iCurr, $this->contents)) { $this->contents[$iCurrId] = array(); }

            $this->contents[$iParent][] = $iCurrId;
            //$default->log->debug("MDTree::buildForField bound to subtree " . print_r($this->contents, true));
        }

        $md_list =& MetaData::getList('document_field_id = ' .$organisedField->getId());
        $this->lookups = array();
        foreach ($md_list as $lookup_value) {

            // failsafe. unparented and orphaned items go into root.
            $iParentId = $lookup_value->getTreeParent();
            if ($iParentId === null) $iParentId = 0;
            if (array_key_exists($iParentId, $this->contents)) {
                $target_set =& $this->contents[$iParentId];
            }
            else
            {
                $target_set =& $this->contents[0];
            }

            $leafArray = null;
            if (!array_key_exists("leaves", $target_set)) { 
                $target_set["leaves"] = array($lookup_value->getId()); 
            }
            else
            {

                array_push($target_set["leaves"], $lookup_value->getId());
            }

            $this->lookups[$lookup_value->getId()] = $lookup_value;

        }      
        $this->root =& $this->contents[0];
        $default->log->debug("MDTree::buildForField done: " . print_r($this, true));
        
    }

    // handle deleting subtrees
    function deleteNode($iNode) { 
        $stack = array();
        array_push($stack, $iNode);
        while (count($stack) != 0)
        {
            $currentNode = array_pop($stack);
            foreach ($this->contents[$currentNode] as $label => $value)
            {
                if ($label === "leaves")
                {
                    foreach ($value as $leaf)
                    {
                        $this->lookups[$leaf]->setTreeParent(0);
                        $this->lookups[$leaf]->update();
                        $this->contents[0]["leaves"][] = $leaf;
                    }   
                }
                else array_push($stack, $value);
            }
            $this->mapnodes[$currentNode]->delete();
        }
        // finally, we prune the appropriate item from its parent.
        $iParent = $this->mapnodes[$iNode]->getParentNode();
        foreach ($this->contents[$iParent] as $index => $val)
            if ($iNode === $val) unset($this->contents[$iParent][$index]);
    }     

    // add a node to the mapping after the fact (e.g. created later in the process.)
    function addNode($oNode) {
        $iParent = $oNode->getParentNode();
        $this->mapnodes[$oNode->getId()] =& $oNode;
        $this->contents[$oNode->getId()] = array();
        array_push($this->contents[$iParent], $oNode->getId());
    }

    function reparentKeyword($lookup_id, $destination_parent_id)
    {
        global $default;

        $oKeyword = $this->lookups[$lookup_id];
        $oldParent = $oKeyword->getTreeParent();
        $oNewParent = $this->mapnodes[$destination_parent_id];
        // we will have failed by here if its bogus.
        //$default->log->debug('MDTree::reparentKeyword '.print_r($oNewParent, true));
        
        // if its 0 or NULL, we reparent to null.
        if (($oNewParent === null) or ($desintation_parent_id === 0)) {
            $new_home = 0;
        } else {
            $new_home = $oNewParent->getId();
        }
        $oKeyword->setTreeParent($new_home);
        // don't assume we're reparenting from 0.
        if (!empty($this->contents[$oldparent])) {
             $KWIndex = array_search($lookup_id, $this->contents[$oldParent]["leaves"]);
             unset($this->contents[$oldParent]["leaves"][$KWIndex]);
            
        }
        $this->contents[$new_home]["leaves"][] = $oKeyword->getId();
        $oKeyword->update();
    }   
    

    // STUB FUNCTIONS:  need to be filled in.
    

    // REALLY need to deprecate this, but how?
    function render($bEditable) { return null; }     // render using a template (with edit / buttons.) FIXME build a widget / renderer.
    

    /* ----------------------- EVIL HACK --------------------------
     *
     *  This whole thing needs to replaced, as soon as I work out how
     *  to non-sucking Smarty recursion.
     */

    function _evilTreeRecursion($subnode, $treeToRender, $inputname)
    {
        $treeStr = "<ul>";
        foreach ($treeToRender->contents[$subnode] as $subnode_id => $subnode_val)
        {
            if ($subnode_id !== "leaves") {
                $extraclass = '';
                if (array_key_exists($subnode_val, $this->activenodes)) {
                    $extraclass = ' active';
                }
                $treeStr .= '<li class="treenode' . $extraclass . '"><a class="pathnode" onclick="toggleElementClass(\'active\', this.parentNode);">' . $treeToRender->mapnodes[$subnode_val]->getName() . 'Root</a>';
                $treeStr .= $this->_evilTreeRecursion($subnode_val, $treeToRender, $inputname);
                $treeStr .= '</li>';
            }
            else
            {
                foreach ($subnode_val as $leaf)
                {
                    $is_selected = '';
                    if ($leaf === $this->activevalue) {
                       $is_selected=' checked="checked"';
                    }
                    $treeStr .= '<li class="leafnode"><input type="radio" name="'.$inputname.'" value="'.$treeToRender->lookups[$leaf]->getName().'" '.$is_selected.'>' . $treeToRender->lookups[$leaf]->getName() .'</input>';
                    $treeStr .=  '</li>';            }
                }
        }
        $treeStr .= '</ul>';
        return $treeStr;
        
    }

    // I can't seem to do recursion in smarty, and recursive templates seems a bad solution.
    // Come up with a better way to do this (? NBM)
    function _evilTreeRenderer($treeToRender, $inputname) {
        //global $default;
        $treeStr = "<!-- this is rendered with an unholy hack. sorry. -->";
        $stack = array();
        $exitstack = array();

        // $treeStr .= print_r($this->activenodes,true);
        // the inner section is generised.
        //$treeStr .= '<ul class="kt_treenodes"><li class="treenode active"><a class="pathnode"  onclick="toggleElementClass(\'active\', this.parentNode);">Root</a>';
        $treeStr .= '<ul class="kt_treenodes">';
        //$default->log->debug("EVILRENDER: " . print_r($treeToRender, true));
        foreach ($treeToRender->getRoot() as $node_id => $subtree_nodes)
        {
            //$default->log->debug("EVILRENDER: ".$node_id." => ".$subtree_nodes." (".($node_id === "leaves").")");
            // leaves are handled differently.
            if ($node_id !== "leaves") {
                $extraclass = '';
                if (array_key_exists($subtree_nodes, $this->activenodes)) {
                    $extraclass = ' active';
                }
                $treeStr .= '<li class="treenode' . $extraclass . '"><a class="pathnode"  onclick="toggleElementClass(\'active\', this.parentNode);">' . $treeToRender->mapnodes[$subtree_nodes]->getName().'</a>';
                $treeStr .= $this->_evilTreeRecursion($subtree_nodes, $treeToRender, $inputname);
                $treeStr .= '</li>';
            }
            else
            {
                foreach ($subtree_nodes as $leaf)
                {
                    $is_selected = '';
                    if ($leaf === $this->activevalue) {
                       $is_selected=' checked="checked"';
                    }
                    $treeStr .= '<li class="leafnode"><input type="radio" name="'.$inputname.'" value="'.$treeToRender->lookups[$leaf]->getName().'" '.$is_selected.'>' . $treeToRender->lookups[$leaf]->getName() .'</input>';
                    $treeStr .=  '</li>';
                }
            }
        }
        $treeStr .= '</ul>';
        //$treeStr .= '</li></ul>';

        return $treeStr;
    
    }
    
    // again, not pretty.  set a particular item as "active"
    function setActiveItem($sMetadataMatch) {
        // also need to:
        //  (a) find the parent and mark it "live".
        //  (b) repeat until we hit parent == 0.
        //
        // we need the md-item, then.
        $md_node = null;
        foreach ($this->lookups as $lookup) {
            if ($sMetadataMatch == $lookup->getName()) {
                $md_node = $lookup;
            }
        }
        if ($md_node === null) {
            return ;
        } else {
            $this->activevalue = $md_node->getId();
            $current_node = $md_node->getTreeParent();
            $this->activenodes[0] = true; // ALWAYS the case, since we have a node.
            while ($current_node != 0) {
                $this->activenodes[$current_node] = true;
                $current_node = $this->mapnodes[$current_node]->getParentNode();
            }
        }
    }
}

?>
