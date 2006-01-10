/*
 * KT3 Calendar wrapper.
 *
 * use this with the following example code:
 
<span class="kt_calendar_holder">
   <strong class="kt_calendar_datetext">No Date Selected</strong>
   <input type="hidden" name="my_var_name" class="kt_calendar_value" />
   <input type="button" onclick="init_kt_calendar(this);">
</span>

 */

function breadcrumbFind(elem, tagName) {
    var stopTag = 'BODY';
    var currentTag = elem.tagName;
    var currentElem = elem;
    while ((currentTag != stopTag) && (currentTag != tagName)) {
        currentElem = currentElem.parentNode;
        currentTag = currentElem.tagName;
    }
    
    if (currentTag == tagName) { 
        return currentElem; 
    } else {
        return null;
    }
}

function resolve_calendar_date(display, store, calendar, date) {
    store.value = date;
    display.innerHTML = date;
    calendar.hide();
}

function close_calendar_ev(calendar) { calendar.hide(); }

function init_kt_calendar(source) {
    var match = breadcrumbFind(source, 'SPAN');
    if (match == null) {
        alert('invalid target for calendar.');
    }
    
    // if we have done this already:
    if (typeof(match._kt3cal) != 'undefined') {
        match._kt3cal.show();
    } else {
        // create the partial'd functions.
        var dwL = getElementsByTagAndClassName(null, 'kt_calendar_datetext', match);
        var sL = getElementsByTagAndClassName(null, 'kt_calendar_value', match);
        
        if ((dwL.length == 0) || (sL.length == 0)) {
            return null; // fail out.
        }
    
        var resolve = partial(resolve_calendar_date, dwL[0], sL[0]);
    
        var c = new Calendar(0, null, resolve, close_calendar_ev);
        c.showsTime = true;
        c.setDateFormat('%Y/%m/%d %H:%M');
        
        c.create();
        c.showAtElement(source,'Bc'); // the button
        c.show();
        match._kt3cal = c;
    }
}

