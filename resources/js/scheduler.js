// Clean up tasks marked as completed
var clearTasks = function(sUrl) {

    Ext.Ajax.request({
        url: sUrl,
        success: function(response) {
            alert('The tasks have been successfully cleaned up');
        },
        failure: function(response) {
            alert('Error. The clean up failed.');
        }
    });
}

//<!-- Reschedule the task to run the next time the scheduler runs -->
var runOnNext = function(fId, sUrl) {
    var runDiv = document.getElementById('runDiv'+fId);

    //<!-- Display the new runtime -->
    var displayDate = formatDate('','no');

    Ext.Ajax.request({
        url: sUrl,
        success: function(response) {
            runDiv.innerHTML = displayDate;
        },
        failure: function(response) {
            alert('Error. The update failed, please refresh the page and try again.');
        },
        params: { fId: fId }
    });
}

//<!-- Enable / disable the task -->
var toggleStatus = function(fId, sUrl, sDisableText, sEnableText) {

    var statusLink = document.getElementById('statusLink'+fId);
    var freqLink = document.getElementById('freqLink'+fId);
    var runnowLink = document.getElementById('runnowLink'+fId);
    var fontClass = document.getElementById('font'+fId);
    var freqDiv = document.getElementById('div'+fId);
    var runDiv = document.getElementById('runDiv'+fId);
    var freq = document.getElementById('freq_'+fId).value;

    var date = new Date();
    var msNow = date.getTime();
    var now = parseInt(msNow / 1000);
    var runTime = calculateFreq(freq, now);
    var displayDate = formatDate(runTime);

    Ext.Ajax.request({
        url: sUrl,
        success: function(response) {
            if(statusLink.value == sDisableText){
                statusLink.value = sEnableText;
                freqLink.style.visibility = "hidden";
                runnowLink.style.visibility = "hidden";
                runDiv.style.visibility = 'hidden';
                fontClass.className = 'descriptiveText';
                freqDiv.style.display = "none";
            }else{
                statusLink.value = sDisableText;
                freqLink.style.visibility = "visible";
                runnowLink.style.visibility = "visible";
                freqDiv.style.display = "block";
                freqDiv.style.visibility = "visible";
                fontClass.className = '';

                runDiv.style.visibility = 'visible';
                runDiv.innerHTML = displayDate;
            }
        },
        failure: function(response) {
            alert('Error. The status update failed, please refresh the page and try again.');
        },
        params: { fId: fId }
    });
}

// Display the form to changed the frequency at which the task runs
var showFrequencyDiv = function(fId) {
    var formDiv = document.getElementById('formDiv');

    if(formDiv.innerHTML == ''){
        return;
    }

    var fInput = document.getElementById('fId');
    var tblCol = document.getElementById('col_'+fId);

    fInput.value = fId;
    tblCol.innerHTML = formDiv.innerHTML;
    tblCol.style.display = 'block';
    formDiv.innerHTML = '';
}

// Save the new frequency
var saveFreq = function(sUrl) {
    var fId = document.getElementById('fId').value;
    var tblCol = document.getElementById('col_'+fId);
    var formDiv = document.getElementById('formDiv');
    var freqDiv = document.getElementById('div'+fId);
    var runDiv = document.getElementById('runDiv'+fId);
    var prevInput = document.getElementById('prev'+fId).value;
    var freqForm = document.getElementById('changefreq');
    var fSelect = document.getElementById('frequency');
    var freq = fSelect.options[fSelect.selectedIndex].value;
    var freqLabel = fSelect.options[fSelect.selectedIndex].label;

    // Move the form content back to the form div
    formDiv.innerHTML = tblCol.innerHTML;
    tblCol.innerHTML = '';
    tblCol.style.display = 'none';

    //<!-- Work out new runtime using the frequency -->
    var prevNum = new Number(prevInput);
    var nextTime = calculateFreq(freq, prevNum);
    var displayDate = formatDate(nextTime);

    Ext.Ajax.request({
        url: sUrl,
        success: function(response) {
            freqDiv.innerHTML = freqLabel;
            runDiv.innerHTML = displayDate;
            document.getElementById('freq_'+fId).value = freq;
        },
        failure: function(response) {
            alert('Error. The frequency update failed, please refresh the page and try again.');
        },
        params: {
            frequency: freq,
            fId: fId
        }
    });
}

//<!-- Calculate the next run time based on the given runtime -->
var calculateFreq = function(freq, prev) {
    var iDiff = 0;

    switch(freq) {
        case 'monthly':
                iDays = getMonthDays(prev);
                iDiff = (60*60)*24*iDays;
                break;
            case 'weekly':
                iDiff = (60*60)*24*7;
                break;
            case 'daily':
                iDiff = (60*60)*24;
                break;
            case 'hourly':
                iDiff = (60*60);
                break;
            case 'half_hourly':
                iDiff = (60*30);
                break;
            case 'quarter_hourly':
                iDiff = (60*15);
                break;
            case '10mins':
                iDiff = (60*10);
                break;
            case '5mins':
                iDiff = (60*5);
                break;
            case '1min':
                iDiff = 60;
                break;
            case '30secs':
                iDiff = 30;
                break;
            case 'once':
                iDiff = 0;
                break;
        }

        var iNextTime = prev + iDiff;

        return iNextTime;
}

//<!-- Return a formatted date given a unix timestamp -->
// If the date if prior to the current date then return the current date
var formatDate = function(unixTime, useUnix) {
    if(useUnix == 'no') {
        var newDate = new Date();
    }else{
        var milliTime = unixTime * 1000;
        var newDate = new Date(milliTime);

        var curDate = new Date();
        if(newDate < curDate){
            newDate = curDate;
        }
    }

    var year = newDate.getFullYear();
    var month = newDate.getMonth() + 1;
    var day = newDate.getDate();
    var hours = newDate.getHours();
    var minutes = newDate.getMinutes();
    var seconds = newDate.getSeconds();

    if(month < 10){
        month = '0'+month;
    }
    if(day < 10){
        day = '0'+day;
    }
    if(hours < 10){
        hours = '0'+hours;
    }
    if(minutes < 10){
        minutes = '0'+minutes;
    }
    if(seconds < 10){
        seconds = '0'+seconds;
    }

    var formattedDate = year+'-'+month+'-'+day+' '+hours+':'+minutes+':'+seconds;
    return formattedDate;
}

//<!-- Get the number of days in a month -->
var getMonthDays = function(unixTime) {
    var milliTime = unixTime * 1000;
    var newDate = new Date(milliTime);
    var year = newDate.getFullYear();
    var month = newDate.getMonth();

    var num = 32 - new Date(year, month, 32).getDate();
    return num;
}