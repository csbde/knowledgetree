<?php
/**
 * $Id$
 *    
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
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
