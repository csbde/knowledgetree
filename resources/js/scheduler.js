
var clearTasks = function(sUrl) {
    
    var callback = {
        success: function(o) {
            alert('Tasks have been successfully cleaned up');
        },
        failure: function(o) {
            alert('Clean up failed!');
        }
    }
    
    var transaction = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
}

//<!-- Change the run time to now -->
var runOnNext = function(fId, sUrl) {
    var runDiv = document.getElementById('runDiv'+fId);
    
    //<!-- Display the new runtime -->
    var displayDate = formatDate('','no');
    
    var callback = {
        success: function(o) {
            runDiv.innerHTML = displayDate;
        }
    }
    var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, "fId="+fId);
}

var showFrequencyDiv = function(fId) {
    var formDiv = document.getElementById('formDiv');
    var fInput = document.getElementById('fId');
    var tblCol = document.getElementById('tblCol');
    var freqDiv = document.getElementById('div'+fId);
    var posFreq = YAHOO.util.Dom.getXY('div'+fId);
    
    //<!-- Hide the frequency and display the form in place -->
    formDiv.style.display = "block";
    freqDiv.style.display = "none";
    formDiv.style.visibility = "visible";
    freqDiv.style.visibility = "hidden";
    fInput.value = fId;
    tblCol.width = "20%";
    YAHOO.util.Dom.setXY('formDiv', posFreq);
}

var saveFreq = function(sUrl) {
    var fId = document.getElementById('fId').value;
    var tblCol = document.getElementById('tblCol');
    var formDiv = document.getElementById('formDiv');
    var freqDiv = document.getElementById('div'+fId);
    var runDiv = document.getElementById('runDiv'+fId);
    var prevInput = document.getElementById('prev'+fId).value;
    var freqForm = document.getElementById('changefreq');
    var fSelect = document.getElementById('frequency');
    var freq = fSelect.options[fSelect.selectedIndex].value;
    
    //<!-- Work out new runtime using the frequency -->
    var prevNum = new Number(prevInput);
    var nextTime = calculateFreq(freq, prevNum);
    var displayDate = formatDate(nextTime);
    
    var callback = {
        success: function(o) {
            freqDiv.innerHTML = freq;
            runDiv.innerHTML = displayDate;
        }
    }
    
    YAHOO.util.Connect.setForm(freqForm);
    var transaction = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback);

    tblCol.width = "";
    formDiv.style.display = "none";
    freqDiv.style.display = "block";
    formDiv.style.visibility = "hidden";
    freqDiv.style.visibility = "visible";
}

//<!-- Calculate the next run time based on the previous runtime -->
var calculateFreq = function(freq, prev) {
    
    var curDate = new Date();
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
            case 'once':
                iDiff = 0;
                break;
        }
                
        var iNextTime = prev + iDiff;
        return iNextTime;
}

//<!-- Return a formatted date given a unix timestamp -->
var formatDate = function(unixTime, useUnix) {
    if(useUnix == 'no') {
        var newDate = new Date();
    }else{
        var milliTime = unixTime * 1000;
        var newDate = new Date(milliTime);
    }
    
    var year = newDate.getFullYear();
    var month = newDate.getMonth() + 1;
    var day = newDate.getDate();
    var hours = newDate.getHours();
    var minutes = newDate.getMinutes();
    
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
    
    var formattedDate = year+'-'+month+'-'+day+' '+hours+':'+minutes;
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