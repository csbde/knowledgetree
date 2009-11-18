<?php
/**
 * $Id$
 *
 * Utilities using file-like objects
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
