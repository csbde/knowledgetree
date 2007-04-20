#!/usr/bin/env python
#
#
# $Id: view.php 6203 2007-02-08 10:41:00Z kevin_fourie $
#
# The contents of this file are subject to the KnowledgeTree Public
# License Version 1.1 ("License"); You may not use this file except in
# compliance with the License. You may obtain a copy of the License at
# http://www.knowledgetree.com/KPL
# 
# Software distributed under the License is distributed on an "AS IS"
# basis,
# WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
# for the specific language governing rights and limitations under the
# License.
# 
# The Original Code is: KnowledgeTree Open Source
# 
# The Initial Developer of the Original Code is The Jam Warehouse Software
# (Pty) Ltd, trading as KnowledgeTree.
# Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
# (C) 2007 The Jam Warehouse Software (Pty) Ltd;
# All Rights Reserved.
#
#

import uno
import sys
from com.sun.star.beans import PropertyValue

url_original = uno.systemPathToFileUrl(sys.argv[1])
url_save = uno.systemPathToFileUrl(sys.argv[2])

try:
    ### Get Service Manager
    context = uno.getComponentContext()
    resolver = context.ServiceManager.createInstanceWithContext("com.sun.star.bridge.UnoUrlResolver", context)
    ctx = resolver.resolve("uno:socket,host=localhost,port=2002;urp;StarOffice.ComponentContext")
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
    sys.stderr.write("Given parameters doesn't conform to the specification ( " + e.Message + ")\n")
    sys.exit(1)
except RuntimeException, e:
    sys.stderr.write("An unknown error occured: " + e.Message + "\n")

if doc == None:
    sys.stderr.write("Could not load doc.\n")
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

doc.storeToURL(url_save, properties)
doc.dispose()
