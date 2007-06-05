<?
/**
 * Copyright (c) 2007, The Jam Warehouse Software (Pty) Ltd.
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *
 *    i) Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *   ii) Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *  iii) Neither the name of the The Jam Warehouse Software (Pty) Ltd nor the 
 *       names of its contributors may be used to endorse or promote products 
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES ( INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT ( INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

require_once('../ktwsapi.inc.php');

$ktapi = new KTWSAPI(KTWebService_WSDL);

// change session to something that is in table 'active_sessions'

$response = $ktapi->active_session('sj5827sohdoj6h3nvifrcsa1f2');
if (PEAR::isError($response))
{
	print $response->getMessage();
	exit;
}

$root = $ktapi->get_root_folder();
if (PEAR::isError($root))
{
	print $root->getMessage();
	exit;
}

$listing = $root->get_listing();
if (PEAR::isError($listing))
{
	print $listing->getMessage();
	exit;
}

// lets display the items

foreach($listing as $folderitem)
{
	$title = $folderitem->title;
	$item_type = $folderitem->item_type;
	$mime_display = $folderitem->mime_display;
	 
	print "$title $item_type $mime_display\n";
}

?>
