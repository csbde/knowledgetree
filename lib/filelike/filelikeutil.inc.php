<?php
/**
 * $Id$
 *
 * Utilities using file-like objects
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

class KTFileLikeUtil {
    function copy_contents($oFrom, $oTo) {
        if ($oFrom->bSupportChunking && $oTo->bSupportChunking) {
            $res = $oFrom->open("r");
            if (PEAR::isError($res)) {
                return $res;
            }
            $res = $oTo->open("w");
            if (PEAR::isError($res)) {
                return $res;
            }
            while (!$oFrom->eof()) {
                $res = $oFrom->read(8192);
                if (PEAR::isError($res)) {
                    return $res;
                }
                $res = $oTo->write($res);
                if (PEAR::isError($res)) {
                    return $res;
                }
            }
            $res = $oFrom->close();
            if (PEAR::isError($res)) {
                return $res;
            }
            $res = $oTo->close();
            if (PEAR::isError($res)) {
                return $res;
            }
        } else {
            $oTo->put_contents($oFrom->get_contents());
        }
        return;
    }

    function send_contents($oFrom, $bChunking = true) {
        if ($oFrom->bSupportChunking && $bChunking) {
            $res = $oFrom->open("r");
            if (PEAR::isError($res)) {
                return $res;
            }
            while (!$oFrom->eof()) {
                $res = $oFrom->read(8192);
                if (PEAR::isError($res)) {
                    return $res;
                }
                print $res;
            }
            $res = $oFrom->close();
            if (PEAR::isError($res)) {
                return $res;
            }
        } else {
            print $oFrom->get_contents();
        }
    }
}

?>
