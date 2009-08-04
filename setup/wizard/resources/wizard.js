// Class Wizard
function wizard() {	
}

// Does a form check on every new page load
wizard.prototype.doFormCheck = function() {
	w.addReadOnly();	
}

// Disable DnD on element
// Element has to have a readOnly status set to readonly
wizard.prototype.disableDnd = function(el_id) {
//    el = document.getElementById(el_id);
//    el.removeAttribute('readOnly');
}

// Add readOnly access on all inputs of a form
wizard.prototype.addReadOnly = function() {
	inputs = document.getElementsByTagName('input');
	for(i=0;i<inputs.length;i++) {
		var input_id = inputs[i].id;
		if(input_id != '') {
//    		inputs[i].setAttribute('readOnly', 'readonly');
//    		inputs[i].setAttribute('onfocus', "javascript:{w.disableDnd('"+ input_id +"')}");
//    		inputs[i].focus();
//    		w.focusElement(inputs[i]);
		}
	}
}

// Toggle Advance Database options
wizard.prototype.showAO = function() {
	var el = document.getElementsByClassName("adv_options");
	if(el[0].style.display == 'none')
	    el[0].style.display = 'block';
	else
	    el[0].style.display = 'none';
}

// Toggle display of an element
wizard.prototype.toggleElement = function(el) {
    if(el.style.display == 'none')
        el.style.display = 'block';
    else
        el.style.display = 'none';
}

// Handle steps within database page
 wizard.prototype.showStep = function(p, d) {
	if(d != 'p') { // Don't check if previous is clicked
    	var ueq = 0;
    	if(p == 2) { // Check User 1
    		ueq = w.validateUsers('dmsname', 'dmspassword', 'dmspassword2');
    	} else if(p == 3) { // Check User 1
    		ueq = w.validateUsers('dmsusername', 'dmsuserpassword', 'dmsuserpassword2');
    	}
    	if(ueq != 0) {
    		return w.display("error_"+ ueq + "_" + p) ;
    	}
	}
	w.hideErrors(); // If theres no errors, hide the ones displaying
	var el = document.getElementsByClassName("step"+p);
	el[0].style.display = 'none';
	if(d == "n") {
		var j = p+1;
	} else if(d == "p") {
		var j = p-1;
	}
	var el = document.getElementsByClassName("step"+j);
	el[0].style.display = 'block';
	
	return true;
}

// Validate Users
 wizard.prototype.validateUsers = function(id1, id2, id3) {
	var el1 = document.getElementById(id1);
	var el2 = document.getElementById(id2);
	var el3 = document.getElementById(id3);
	var elVal1 = el1.value;
	var elVal2 = el2.value;
	var elVal3 = el3.value;
	if(elVal1 == '') { // User name empty 
		w.focusElement(el1);
		return 1;
	} else if(elVal2 == '') { // Empty Password
		w.focusElement(el2);
		return 2;
	} else if(elVal3 == '') { // Empty Confirmation Password
		w.focusElement(el3);
		return 3;
	} else if(elVal2 != elVal3) { // Passwords not equal
		w.focusElement(el2);
		return 4;
	} else {
		return 0;
	}
}

// Display Errors
wizard.prototype.display = function(elname, er) {
	var el = document.getElementById(elname);
	w.showElement(el);
}

// Hide Errors
wizard.prototype.hideErrors = function() {
	var errors = document.getElementsByClassName('error');
	var i;
	for(i=0;i<errors.length;i++) {
		w.hideElement(errors[i]);
	}
	return true;
}

// Hide an element
wizard.prototype.hideElement = function(el) {
	if(el.style.display == 'block')
		el.style.display = 'none';
}

// Show an element
wizard.prototype.showElement = function(el) {
	if(el.style.display == 'none')
		el.style.display = 'block';
}

// Focus on element
wizard.prototype.focusElement = function(el) {
	el.focus();
}

// Catch form submit and validate
wizard.prototype.onSubmitValidate = function() {
	var response = w.showStep(3, 'n');
	if(response == true) {
		alert(response);
		document.getElementById('sendAll').name = 'Next'; // Force the next step
		document.getElementById('sendAll').value = 'next';
		document.getElementById('dbsettings').submit();
	} else {
		alert('asd');
		return false;
		/*
		document.getElementById('sendAll').name = 'Previous'; // Force the previous step
		document.getElementById('sendAll').value = 'previous';
		document.getElementById('dbsettings').submit();
		*/
	}
	


}