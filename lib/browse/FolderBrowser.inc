<?php
/**
 * $Id$
 *
 * Contains folder view document browsing business logic.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.browse
 */

require_once("$default->fileSystemRoot/lib/browse/Browser.inc");
require_once("$default->fileSystemRoot/lib/browse/Criteria.inc");
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");

class FolderBrowser extends Browser {

    /**
     * Construct a new FolderBrowser instance with the specified sort criteria
     *
     * @param string the field to sort the results by
     * @param string the direction to sort the results
     */
    function FolderBrowser($sSortField = "filename", $sSortDirection = "asc", $aNewSortCriteria = array()) {
        Browser::Browser($sSortField, $sSortDirection, $aNewSortCriteria);
    }

    /**
     * Browse the documents by folder
     *
     * @return array folders and documents
     */
    function browse() {
        global $default;

        // XXX: Shouldn't be getting this from $_REQUEST
        $iFolderID = $_REQUEST['fFolderID'];

        // we're browsing folders
        $this->setBrowseStart($iFolderID);
        $iFolderID = $iFolderID;

        // instantiate and initialise
        $results = array();
        $sql = $default->db;

        // no folder specified, so depending on the users groups, resolve to the right folderID
        if ($iFolderID == "") {
            // no folder specified, so start at the root folder

            //TO DO: Need to look up all the Units
            // look up this users unit
            $unitID = User::getUnitID($_SESSION["userID"]);

           $default->log->debug("Users UNIT = " . $unitID  );

            if ($unitID) {
                // if the user is in a unit, start at the unit's root folder

                // lookup the unit name
                $unitName = lookupField($default->units_table, "name", "id", $unitID);

                // the unit root folder has the same name as the unit
                // FIXME: dodgy i know, but its easy
                $unitRootFolderName = $unitName;

                // now lookup the folderID
                $aFolders = Folder::getList(array("name = ? and parent_id = 1", $unitRootFolderName));/*ok*/

                $default->log->debug("Users UNIT Name = " . $unitRootFolderName  );

                if (!$aFolders) {
                    // no folder exists with this name, so start at the root
                    $default->log->debug("Users UNIT Name NO FOLDER EXISTS WITH THIS NAME" . $unitRootFolderName  );
                    $iFolderID = lookupID($default->folders_table, "parent_id", 0);
                } else {
                    $iFolderID = $aFolders[0]->getID();
                    $default->log->debug("FOLDER EXISTS WITH THIS NAME" . $unitRootFolderName  );
                }

            } else {
                // else just start at the root
                // FIXME: start at the root folder for the default organisation
                $iFolderID = lookupID($default->folders_table, "parent_id", 0);
             }
        } else{
            $default->log->debug("Folder specified " . $iFolderID );
        }

        $default->log->debug("FolderBrowser::browseByFolder: folderID=" . $iFolderID);

        if (!Folder::folderExistsID($iFolderID)) {
            return new PEAR_Error(_("This folder does not exist"));
        }

        // get the folder
        $rootFolder = Folder::get($iFolderID);
        // FIXME: check permissions for the current folder before getting children
        $results["folders"][] = & $rootFolder;
        if ($results["folders"][0]) {

            // now find all the child folders relative to this one
            // FIXME: in the same unit?

            $oSortCriterion = KTUtil::arrayGet($this->aSortCriteria, $this->sSortField);

            // Something fishy is up with the sort field given, default
            // to sorting by name, but name might not even be in the
            // Sort Criteria set up for browsing, so use Criteria
            // directly to get it.
            if (is_null($oSortCriterion)) {
                $oSortCriterion = Criteria::getCriterionByNumber(-1);

                // If our sort field was broken, override the given sort
                // direction.
                $this->sSortDirection = 'asc';
            }
            $aLookupCriteria = $oSortCriterion->getLookup();

            $aQuery = $oSortCriterion->folderQuery($iFolderID, $this->sSortDirection);

            $oResult = DBUtil::runQuery($aQuery);
            if (PEAR::isError($oResult)) {
                var_dump($oResult);
                exit(0);
            }

            if ($oResult->numRows()) {
                while ($aRow = $oResult->fetchRow()) {
                  $default->log->debug("In folder iteration while, with folder_id " . $aRow["id"]);
                  // check whether to display folders which are not readable and display/hide these accordingly
                      $oFolder = Folder::get($aRow["id"]);
                      if ($default->folderHidingFlag) {
                          if (Permission::userHasFolderReadPermission($oFolder)) {
                              $default->log->debug("FOLDER PERMISSIONS: Does  have permission for folder " . $oFolder->getID() . ":" . $aRow["id"]);
                            $results["folders"][] =  $oFolder;
                          } else {
                              $default->log->debug("FOLDER PERMISSIONS: Does  NOT have permission for folder " . $aRow["id"] );
                          }
                      } else{
                        $results["folders"][] =  $oFolder;
                      }
                }
            }

            $default->log->debug("Going on to document checking");

            $aQuery = $oSortCriterion->documentQuery($iFolderID, $this->sSortDirection);
            $oResult = DBUtil::runQuery($aQuery);
            if (PEAR::isError($oResult)) {
                var_dump($oResult);
                exit(0);
            }

            // initialise access flag;
            $results["accessDenied"] = false;
            if ($oResult->numRows()) {
                // do the check for whether this documents have folder read permission, if they do, it's all good.
                $hasFolderRead = Permission::userHasFolderReadPermission($rootFolder);

                while ($aRow = $oResult->fetchRow()) {
                    $oDocument = &Document::get($aRow["id"]);
                    // proceed if the document is live
                    if ($oDocument->isLive()) {
                        // check permissions
                        if ($hasFolderRead || Permission::userHasDocumentReadPermission($oDocument)) {
                            // add documents to array
                            // set file attributes
                            $results["documents"][] = $oDocument;
                        } else {
                            // set access denied message
                            $results["accessDenied"] = true;
                        }
                    }
                }
            } else {
                $_SESSION["errorMessage"] = "documents table select failed";
            }

            return $results;
        }
    }

    function getSectionName() {
        return "Manage Documents";
    }
}
