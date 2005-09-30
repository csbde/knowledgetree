/* 
// once site is in production, rather use:

function simpleLog(severity, item) { ; }

*/

// inline logger
function simpleLog(severity,item) {
    var logTable = getElement('brad-log');
    if (logTable == null) return ;

    // we have a table, do the log.
    newRow = createDOM('TR', {'class':'logtable','valign':'top'},
        TD({'class':'severity-'+severity}, severity),
        TD({'class':'timestamp'},toISOTime(new Date())),
        TD({'class':'explanation'}, item)
    );
    logTable.getElementsByTagName('tbody')[0].appendChild(newRow);
}
