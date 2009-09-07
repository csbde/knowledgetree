// Class Wizard
function wizard() {	
}

// Does a form check on every new page load
wizard.prototype.doFormCheck = function() {
	w.addReadOnly();
	w.load();
}

// Toggle Advance Database options
wizard.prototype.toggleClass = function(el) {
	var el = document.getElementsByClassName(el); //adv_options|php_details|php_ext_details|php_con_details
	if (el == 'adv_options') {
		
	}
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
    	} else if(p == 3) { // Check User 2
    		ueq = w.validateUsers('dmsusername', 'dmsuserpassword', 'dmsuserpassword2');
    	}
    	if(ueq != 0) {
    		return w.display("error_"+ ueq + "_" + p) ;
    	}
	}
	w.hideErrors(); // If theres no errors, hide the ones displaying
	var el = document.getElementsByClassName("step"+p);
	el[0].style.display = 'none';
	var j = 0;
	if(d == "n") {
		j = p+1;
	} else if(d == "p") {
		j = p-1;
	}
	el = document.getElementsByClassName("step"+j);
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
	return 'display';
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
wizard.prototype.onSubmitValidate = function(silent) {
	var response = w.showStep(3, 'n');
	if(response == true || silent == true) {
		document.getElementById('sendAll').name = 'Next'; // Force the next step
		document.getElementById('sendAll').value = 'next';
		document.getElementById('dbsettings').submit();
	} else if(response == 'display') {
		var el = document.getElementsByClassName("step1");
		if(el[0].style.display == 'block') {
			document.getElementById('sendAll').name = 'Previous'; // Force the previous step
			document.getElementById('sendAll').value = 'previous';
			document.getElementById('dbsettings').submit();
		} else {
			return false;
		}
	}
	return true;
}

wizard.prototype.pClick = function() {
	var state = document.getElementById('state');
	if(state != "undefined") {
		state.name = 'previous';
	}
}

wizard.prototype.nClick = function() {
	var state = document.getElementById('state');
	if(state != "undefined") {
		state.name = 'next';
	}
}

// Validate Registration Page
wizard.prototype.validateRegistration = function() {
	// See if next or previous is clicked.
	var state = document.getElementById('state').name;
	if(state == 'next') {
		if(w.valRegHelper()) {
			document.getElementById('sendAll').name = 'Next'; // Force the next step
			document.getElementById('sendAll').value = 'next';
			document.getElementById('registration').submit();
		}
	} else if(state == 'previous') {
		document.getElementById('sendAll').name = 'Previous'; // Force the previous step
		document.getElementById('sendAll').value = 'previous';
		document.getElementById('registration').submit();
	}
}

wizard.prototype.valRegHelper = function() {
	var first = document.getElementById('first');
	var last = document.getElementById('last');
	var email = document.getElementById('email');
	
	if(first.value.length < 2) {
		document.getElementById("reg_error").innerHTML = "Please enter a First Name";
		w.focusElement(first);
		return false;
	}
	if(last.value.length < 2) {
		document.getElementById("reg_error").innerHTML = "Please enter a Last Name";
		w.focusElement(last);
		return false;
	}
	if(!w.emailCheck(email.value)) {
		document.getElementById("reg_error").innerHTML = "Please enter a valid email address";
		w.focusElement(email);
		return false;
	}
	
	return true;
}

// Validate Registration Page Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
wizard.prototype.emailCheck = function(str) { 
	var at="@";
	var dot=".";
	var lat=str.indexOf(at);
	var lstr=str.length;
	var ldot=str.indexOf(dot);
	if (str.indexOf(at)==-1) {
		return false;
	}
	if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr) {
		return false;
	}
	if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr) {
		return false;
	}
	if (str.indexOf(at,(lat+1))!=-1) {
		return false;
	}
	if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		return false;
	}
	if (str.indexOf(dot,(lat+2))==-1){
		return false;
	}
	if (str.indexOf(" ")!=-1){
		return false;
	}
	return true;
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

/* */
wizard.prototype.load = function() {
//	$('#tooltips').tooltip();
}