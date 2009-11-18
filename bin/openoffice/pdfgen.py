#!/usr/bin/env python
#
# $Id$
#
# KnowledgeTree Community Edition
# Document Management Made Simple
# Copyright (C) 2008, 2009 KnowledgeTree Inc.
# 
#
# This program is free software; you can redistribute it and/or modify it under
# the terms of the GNU General Public License version 3 as published by the
# Free Software Foundation.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
# details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
# California 94120-7775, or email info@knowledgetree.com.
#
# The interactive user interfaces in modified source and object code versions
# of this program must display Appropriate Legal Notices, as required under
# Section 5 of the GNU General Public License version 3.
#
# In accordance with Section 7(b) of the GNU General Public License version 3,
# these Appropriate Legal Notices must retain the display of the "Powered by
# KnowledgeTree" logo and retain the original copyright notice. If the display of the
# logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
# must display the words "Powered by KnowledgeTree" and retain the original
# copyright notice.
# Contributor( s): ______________________________________
#

import os, sys, glob

extrapaths = glob.glob('/usr/lib*/openoffice*/program/') + glob.glob('/usr/lib*/ooo*/program') + [ '/Applications/NeoOffice.app/Contents/program', 'c:/program files/ktdms/openoffice/program' ]

ooProgramPath = os.environ.get('ooProgramPath')
if not ooProgramPath is None:
    extrapaths = [ ooProgramPath ] + extrapaths

for path in extrapaths:
    try:
        sys.path.append(path)
        import uno
        os.environ['PATH'] = '%s:' % path + os.environ['PATH']
        break
    except ImportError:
        sys.path.remove(path)
        continue
else:
    print >>sys.stderr, "PyODConverter: Cannot find the pyuno.so library in sys.path and known paths."
    sys.exit(1)

from com.sun.star.beans import PropertyValue

NoConnectException = uno.getClass("com.sun.star.connection.NoConnectException")
IllegalArgumentException = uno.getClass("com.sun.star.lang.IllegalArgumentException")
RuntimeException = uno.getClass("com.sun.star.uno.RuntimeException")
IOException = uno.getClass("com.sun.star.io.IOException")

url_original = uno.systemPathToFileUrl(sys.argv[1])
url_save = uno.systemPathToFileUrl(sys.argv[2])

try:
    ### Get Service Manager
    context = uno.getComponentContext()
    resolver = context.ServiceManager.createInstanceWithContext("com.sun.star.bridge.UnoUrlResolver", context)
    ctx = resolver.resolve("uno:socket,host=localhost,port=8100;urp;StarOffice.ComponentContext")
    smgr = ctx.ServiceManager

    ### Load document
    properties = []
    p = PropertyValue()
    p.Name = "Hidden"
    p.Value = True
    properties.append(p)
    properties = tuple(properties)

    desktop = smgr.createInstanceWithContext("com.sun.star.frame.Desktop", ctx)

except NoConnectException, e:
    sys.stderr.write("OpenOffice process not found or not listening (" + e.Message + ")\n")
    sys.exit(1)
except IllegalArgumentException, e:
    sys.stderr.write("The url is invalid ( " + e.Message + ")\n")
    sys.exit(1)
except RuntimeException, e:
    sys.stderr.write("An unknown error occured: " + e.Message + "\n")

try:
    doc = desktop.loadComponentFromURL(url_original, "_blank", 0, properties)
except IOException, e:
    sys.stderr.write("URL couldn't be found or was corrupt (" + e.Message + ")\n")
    sys.exit(1)
except IllegalArgumentException, e:
    sys.stderr.write("Given parameters don't conform to the specification ( " + e.Message + ")\n")
    sys.exit(1)
except RuntimeException, e:
    sys.stderr.write("An unknown error occured: " + e.Message + "\n")

if doc == None:
    sys.stderr.write("The document could not be opened for conversion. This could indicate an unsupported mimetype.\n")
    sys.exit(1)


### Save File
properties = []
p = PropertyValue()
p.Name = "Overwrite"
p.Value = True
properties.append(p)
p = PropertyValue()
p.Name = "FilterName"
p.Value = 'writer_pdf_Export'
properties.append(p)
properties = tuple(properties)

try:
    doc.storeToURL(url_save, properties)
    doc.dispose()
except IOException, e:
    sys.stderr.write("URL (" + url_save + ") couldn't be found or was corrupt (" + e.Message + ")\n")
    sys.exit(1)
except IllegalArgumentException, e:
    sys.stderr.write("Given parameters don't conform to the specification ( " + e.Message + ")\n")
    sys.exit(1)
except RuntimeException, e:
    sys.stderr.write("An unknown error occured: " + e.Message + "\n")
