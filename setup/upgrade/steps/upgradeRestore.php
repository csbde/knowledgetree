<?php
/**
* Complete Step Controller. 
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright(C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software(Pty) Limited
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
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Upgrader
* @version Version 0.1
*/

require '../../config/dmsDefaults.php';

class upgradeRestore extends Step {

    /**
	* Reference to Database object
	*
	* @author KnowledgeTree Team
	* @access private
	* @var object
	*/	
    private $_dbhandler = null;

    private $privileges_check = 'tick';
    private $database_check = 'tick';
    protected $silent = true;
    
    protected $util = null;
    
    public function __construct() {
    	$this->temp_variables = array("step_name"=>"restore", "silent"=>$this->silent);
        $this->_dbhandler = new UpgradedbUtil();
    	$this->util = new UpgradeUtil();
    }

    function doStep() {
        parent::doStep();
        if(!$this->inStep("restore")) {
            $this->doRun();
            return 'landing';
        }
        if($this->next()) {
            if ($this->doRun()) {
                return 'next';
            }
        } else if($this->previous()) {
            return 'previous';
        }
        
        $this->doRun();
        return 'landing';
    }
    
    function doRun() {
        $this->temp_variables['selected'] = false;
        if ($this->select()) {
            $this->restoreSelected();
            $this->temp_variables['selected'] = true;
        }
        $this->restoreConfirm();
        $this->storeSilent();// Set silent mode variables
        
        return true;
    }
    
    function select() {
        return isset($_POST['RestoreSelect']);
    } 
    
    /**
     * Set all silent mode varibles
     *
     */
    private function storeSilent() {
    }
 /*   
    // these belong in a shared lib
    function set_state($value)
{
    $_SESSION['state'] = $value;
}
function check_state($value, $state='Home')
{
    if ($_SESSION['state'] != $value)
    {
        ?>
            <script type="text/javascript">
            document.location="?go=<?php echo $state;?>";
            </script>
            <?php
            exit;
    }
}
*/
    function restore()
{
//    check_state(1);
//    set_state(5);
//    title('Restore In Progress');
    $status = $_SESSION['backupStatus'];
    $filename = $_SESSION['backupFile']; 
    $stmt = $this->util->create_restore_stmt($filename);
    $dir = $stmt['dir'];

    if (is_file($dir . '/mysql') || is_file($dir . '/mysql.exe'))
    {

?>
        The restore is now underway. Please wait till it completes.
<?php
        print "\n";


        $curdir=getcwd();
        chdir($dir);


        $ok=true;
        $stmts=explode("\n",$stmt['cmd']);
        foreach($stmts as $stmt)
        {

            $handle = popen($stmt, 'r');
            if ($handle=='false')
            {
                $ok=false;
                break;
            }
            $read = fread($handle, 10240);
            pclose($handle);
            $_SESSION['restoreOutput']=$read;
        }

            $_SESSION['restoreStatus'] = $ok;

    }
    else
    {
?>
<P>
    The <i>mysql</i> utility was not found in the <?php echo $dir;?> subdirectory.

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')">
<?php
    }

}


function restoreDone()
{
//    check_state(5);
//    set_state(6);
//    title('Restore Status');
    $status = $_SESSION['restoreStatus'];
    $filename = $_SESSION['backupFile'];

    if ($status)
    {

?>
        The restore of <nobr><i>"<?php echo $filename;?>"</i></nobr> has been completed.
        <P>
        It appears as though the <font color=green>restore has been successful</font>.
        <P>



<?php
    }
    else
    {
?>
It appears as though <font color=red>the restore process has failed</font>. <P>
Unfortunately, it is difficult to diagnose these problems automatically
and would recommend that you try to do the backup process manually.
<P>
We appologise for the inconvenience.
<P>
<table bgcolor="lightgrey">
<tr>
<td>
<?php echo $_SESSION['restoreOutput'];?>
</table>
<?php

    }
?>

<br/>

&nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')">

<?php

}

function restoreSelect()
{
    $this->temp_variables['availableBackups'] = false;
//    title('Select Backup to Restore');
    $dir = $this->util->resolveTempDir();

    $files = array();
    if ($dh = opendir($dir))
    {
        while (($file = readdir($dh)) !== false)
        {
            if (!preg_match('/kt-backup.+\.sql/',$file)) {
                continue;
            }
            $files[] = $file;
        }
        closedir($dh);
    }
    
    $this->temp_variables['dir'] = $dir;
    if (count($files) != 0) {
        $this->temp_variables['availableBackups'] = true;
        $this->temp_variables['files'] = $files;
    }
}

function restoreSelected()
{
    $file=$_REQUEST['file'];

    $dir = $this->util->resolveTempDir();
    $_SESSION['backupFile'] = $dir . '/' . $file;
?>
<?php

}

function restoreConfirm()
{
    if (!isset($_SESSION['backupFile']) || !is_file($_SESSION['backupFile']) || filesize($_SESSION['backupFile']) == 0)
    {
        $this->restoreSelect();
        return;
    }

    $status = $_SESSION['backupStatus'];
    $filename = $_SESSION['backupFile'];
    $stmt = $this->util->create_restore_stmt($filename);

    $this->temp_variables['dir'] = $stmt['dir'];
    $this->temp_variables['display'] = $stmt['display'];
}




}
?>