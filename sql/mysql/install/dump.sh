#!/bin/sh

DIR=`dirname $0`
cd $DIR

case $1 in
'') DB=dms_clean ;;
*) DB=$1 ;;
esac

HEADER = '--
-- $Id: $
--
-- KnowledgeTree Community Edition
-- Document Management Made Simple
-- Copyright (C) 2008, 2009 KnowledgeTree Inc.
-- 
--
-- This program is free software; you can redistribute it and/or modify it under
-- the terms of the GNU General Public License version 3 as published by the
-- Free Software Foundation.
--
-- This program is distributed in the hope that it will be useful, but WITHOUT
-- ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
-- FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
-- details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.
--
-- You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
-- California 94120-7775, or email info@knowledgetree.com.
--
-- The interactive user interfaces in modified source and object code versions
-- of this program must display Appropriate Legal Notices, as required under
-- Section 5 of the GNU General Public License version 3.
--
-- In accordance with Section 7(b) of the GNU General Public License version 3,
-- these Appropriate Legal Notices must retain the display of the "Powered by
-- KnowledgeTree" logo and retain the original copyright notice. If the display of the
-- logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
-- must display the words "Powered by KnowledgeTree" and retain the original
-- copyright notice.
-- Contributor( s): ______________________________________
--'


PATH=$PATH:../../../../mysql/bin:/usr/local/mysql/bin
DATE=`date +%Y-%m-%d-%H-%M-%S`

# Create files headers
echo $HEADER > structure-$DATE.sql
echo $HEADER > data-$DATE.sql

# Create the Structure Dump
mysqldump -u root -p $DB --no-data --skip-add-drop-table >> structure-$DATE.sql

cat structure-$DATE.sql | sed 's/ AUTO_INCREMENT=[0-9]*//g' > structure.tmp
mv structure.tmp structure-$DATE.sql

# Create the Data Dump
mysqldump -u root -p $DB --no-create-info >> data-$DATE.sql


sed "s/[)],[(]/),\n(/g"  data-$DATE.sql > data.tmp
sed "s/VALUES [(]/VALUES\n(/g"  data.tmp >  data-$DATE.sql
rm data.tmp
