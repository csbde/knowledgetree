#!/usr/bin/env python
#
# 
#  Copyright (c) 2007 Jam Warehouse http://www.jamwarehouse.com
# 
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; using version 2 of the License.
# 
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
# 
#  You should have received a copy of the GNU General Public License
#  along with this program; if not, write to the Free Software
#  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
# 
#  -------------------------------------------------------------------------
# 
#  You can contact the copyright owner regarding licensing via the contact
#  details that can be found on the KnowledgeTree web site:
# 
#          http://www.knowledgetree.com/
# 

import uno
import sys
from com.sun.star.beans import PropertyValue

url_original = uno.systemPathToFileUrl(sys.argv[1])
url_save = uno.systemPathToFileUrl(sys.argv[2])

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
doc = desktop.loadComponentFromURL(url_original, "_blank", 0, properties)

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
