
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
    var freqForm = document.getElementById('changefreq');
    var fSelect = document.getElementById('frequency');
    var freq = fSelect.options[fSelect.selectedIndex].value;
    
    var callback = {
        success: function(o) {
            freqDiv.innerHTML = freq;
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
