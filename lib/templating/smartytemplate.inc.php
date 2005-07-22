<?php
/**
 * $Id$
 *
 * Smarty Template implementation
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once(KT_LIB_DIR . "/templating/template.inc.php");
require_once(KT_DIR . "/thirdparty/Smarty/Smarty.class.php");

class KTSmartyTemplate extends KTTemplate {
    function KTSmartyTemplate ($sPath) {
        $this->KTTemplate($sPath);
    }

    function render($aDict) {
        $smarty = new Smarty;
        $smarty->compile_dir = "/tmp";
        foreach ($aDict as $k => $v) {
            $smarty->assign($k, $v);
        }
        $smarty->caching = false;
        return $smarty->fetch($this->sPath);
    }
}

?>
