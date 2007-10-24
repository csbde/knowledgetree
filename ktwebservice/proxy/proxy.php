<?php

/**
 * $Id:$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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
 *
 */

set_time_limit(0);

if ($argc < 3)
{
	die('Usage: proxy.php listenport connectaddr connectport');
}

$cport = $argv[1];
$saddress = $argv[2];
$sport = $argv[2];


print "Listening on port: $cport\n";
print "Connecting to: $saddress:$sport\n";

if (($lsock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false)
{
	die('Cannot create socket: '. socket_strerror($res));
}

if (($res = @socket_bind($lsock, '127.0.0.2', $cport)) === false)
{
	die('Cannot bind socket: ' . socket_strerror($res));
}

if (($res = socket_listen($lsock, 5)) === false)
{
	die('Cannot listen on socket: ' . socket_strerror($res));
}

while(true)
{
	if (($csock = socket_accept($lsock)) < 0)
	{
		print 'Cannot accept socket: ' . socket_strerror($csock) . "\n";
		continue;
	}
	print "accepting client\n";

	if (($ssock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0)
	{
		print('Cannot create socket: '. socket_strerror($res));
		continue;
	}

	print "connecting\n";
	if (($res = socket_connect($ssock, $saddress, $sport)) < 0)
	{
		print('Cannot bind socket: ' . socket_strerror($res));
		continue;
	}


	ob_implicit_flush();

	$clientClose = false;
	$serverClose = false;
	while(!$clientClose && !$serverClose)
	{
		$arr = array();
		$carr = array();
		$sarr = array();

		if (!$clientClose)
		{
			$arr[]= $csock;
			$carr[]= $csock;
		}
		if (!$serverClose)
		{
			$arr[]= $ssock;
			$sarr[]= $ssock;
		}
ob_implicit_flush();		$res = socket_select($arr, $e2=null, $e = null, 5);
		if ($res === false)
		{
			print "problem\n";
			break;
		}
		else
		{
			$res = @socket_select($carr, $w = NULL, $e = NULL, 0);
			if (!$clientClose && ($res === 1))
			{
				$buf = @socket_read($csock, 2048, PHP_NORMAL_READ);
				if (strlen($buf) != 0)
				{
					socket_write($ssock, $buf, strlen($buf));
					print "C>>S: $buf\n";
				}
				if ($buf === false)
				{
					$clientClose = true;
					socket_write($ssock, "\n", 1);
					print "close connection to client\n";
				}

			}
			$res = @socket_select($sarr, $w = NULL, $e = NULL, 0);
			if (!$serverClose && ($res === 1))
			{
				$buf = @socket_read($ssock, 2048, PHP_NORMAL_READ);
				if (strlen($buf) != 0)
				{
					socket_write($csock, $buf, strlen($buf));
					print "C<<S: $buf\n";

				}
				if($buf ===0 )
				{
					print "\n";
				}
				if ($buf === false)
				{
					//socket_close($csock);
					$serverClose = true;
					print "close connection to server\n";
				}
			}
		}
	}
	socket_close($ssock);
	socket_close($csock);
}

socket_close($lsock);
?>