<?php
/**
 * $Id: view.php 6584 2007-05-23 13:43:15Z kevin_fourie $
 *    
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 */

/* a pluggable validation environment */

class KTValidator {
    var $sNamespace;
    
    var $sInputVariable;    // what name to look for in "data"
    var $sBasename;         // the key to use for errors
    var $sOutputVariable;   // where to put the output, if any
    var $bProduceOutput;    // should be produce an output in "results"
    var $bRequired = false;
    
    var $aOptions;
    
    function configure($aOptions) {
        $this->sInputVariable = KTUtil::arrayGet($aOptions, 'name', KTUtil::arrayGet($aOptions, 'test'));
        if (empty($this->sInputVariable)) { return PEAR::raiseError(_kt("You must specify a variable name")); }
        $this->sBasename = KTUtil::arrayGet($aOptions, 'basename', $this->sInputVariable);      
        $this->sOutputVariable = KTUtil::arrayGet($aOptions, 'output');
        if (empty($this->sOutputVariable)) {
            if (!KTUtil::arrayGet($aOptions, 'no_output', false)) {
                $this->sOutputVariable = $this->sInputVariable;
            }
        }
        $this->bProduceOutput = !empty($this->sOutputVariable);
        $this->bRequired = KTUtil::arrayGet($aOptions, 'required', false , false);
        
        $this->aOptions = $aOptions;
    }
    
    function validate($data) {
        $res = array();
        
        $res['results'] = array();
        $res['errors'] = array();
        
        return $res;
    }
}

?>
