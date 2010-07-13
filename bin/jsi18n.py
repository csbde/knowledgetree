#!/usr/bin/python
#
# JS Gettext-style extractor.

import re
import sys

usage_str = '''
   Extracts _() items in JS.

   Usage:  jsi18n.py [list of files] > output.smarty
'''

baseline = '''
/*
 *  Javascript (actual translations);
 */
 
{literal}
var i18n = {};

function _(trans_string) {
    var newstr = i18n[trans_string];
    if (!isUndefinedOrNull(newstr)) { return newstr; } 
    else {
       return trans_string;
    }
}
{/literal}


'''

peritem = "i18n['%s'] = '{i18n}%s{/i18n}';\n"

# this is not the best way to do this ...

class JSExtractor:
    def __init__(self, filename):
        self.strings = []
        handle = file(filename, 'r')
        self.content = handle.read()
        handle.close()
        
    def process(self):
        proc = re.compile('(_\(\'(.*?)\'\))')
        self.strings = [i[1] for i in proc.findall(self.content)] 
            
    def getStrings(self):
        out = ''
        for l in self.strings:
            out += peritem%(l, l)
        return out
        
if __name__ == '__main__':
    fake_po = baseline
    
    filelist = sys.stdin.readlines()
    for filename in filelist:
        processor = JSExtractor(filename[:-1])
        processor.process()
        fake_po += "\n// strings for file: %s\n"%(filename[:-1]);
        fake_po += processor.getStrings()
    
    print fake_po