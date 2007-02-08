<?php
/**
 * $Id$
 *
 * Contains datetime functions.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.database 
 */

/**
 * Returns the current date time
 *
 * @return string the current date time (Y-m-d H:i:s)
 */
function getCurrentDateTime() {
    return date("Y-m-d H:i:s", time());
}

/**
 * Returns the specified date time, formatted as Y-m-d H:i:s
 *
 * @param int the date time to format
 * @return string the formatted date time
 */
function formatDateTime($dateTime) {
    return date("Y-m-d H:i:s", $dateTime);
}
?>
