// Class Wizard
function wizard() {	
}

// Toggle Advance Database options
wizard.prototype.toggleClass = function(ele, option) { //adv_options|php_details|php_ext_details|php_con_details
	if($('.'+ele).attr('style') == 'display: none;') {
	    $('.'+ele).attr('style', 'display: block;');
	    if($('#'+option).attr('innerHTML') != '&nbsp;&nbsp;Advanced Options')
	    	$('#'+option).attr('innerHTML', 'Hide Details');
	} else {
	    $('.'+ele).attr('style', 'display: none;');
	    if($('#'+option).attr('innerHTML') != '&nbsp;&nbsp;Advanced Options')
	    	$('#'+option).attr('innerHTML', 'Show Details');
	}
}

// Focus on element
wizard.prototype.focusElement = function(el) {
	el.focus();
}

// Force previous click
wizard.prototype.pClick = function() {
	var state = $('#state');
	if(state != undefined) {
		state.attr('name', 'previous');
	}
}

// Force next click
wizard.prototype.nClick = function() {
	var state = $('#state');;
	if(state != undefined) {
		state.attr('name', 'next');
	}
}

// Validate Registration Page
wizard.prototype.validateRegistration = function() {
	// See if next or previous is clicked.
	var state = $('#state').attr('name');
	if(state == 'next') {
		if(w.valRegHelper()) {
			$('#sendAll').attr('name', 'Next'); // Force the next step
			$('#sendAll').attr('value', 'next');
			return true;
		}
	} else if(state == 'previous') {
		$('#sendAll').attr('name', 'Previous'); // Force the previous step
		$('#sendAll').attr('value', 'previous');
		return true;
	}
	
	return false;
}

wizard.prototype.valRegHelper = function() {
	var first = document.getElementById('first');
	var last = document.getElementById('last');
	var email = document.getElementById('email');
	if(first.value.length < 1) {
		document.getElementById("reg_error").innerHTML = "Please enter a First Name";
		w.focusElement(first);
		return false;
	}
	if(!w.nameCheck(first.value)) {
		document.getElementById("reg_error").innerHTML = "Please enter a valid First Name";
		w.focusElement(first);
		return false;
	}
	if(last.value.length < 1) {
		document.getElementById("reg_error").innerHTML = "Please enter a Last Name";
		w.focusElement(last);
		return false;
	}
	if(!w.nameCheck(last.value)) {
		document.getElementById("reg_error").innerHTML = "Please enter a valid Last Name";
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

wizard.prototype.nameCheck = function(str) {
	var nameRegxp = /^([a-zA-Z]+)$/;
	if(str.match(nameRegxp)) {
		return true;
	} else {
		return false;
	}
}

// Validate Registration Page Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
wizard.prototype.emailCheck = function(str) { 
	str = w.trim(str);
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

wizard.prototype.trim = function (str, chars) {
	return w.ltrim(w.rtrim(str, chars), chars);
}
 
wizard.prototype.ltrim = function (str, chars) {
	chars = chars || "\\s";
	return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}
 
wizard.prototype.rtrim = function (str, chars) {
	chars = chars || "\\s";
	return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}

wizard.prototype.adjustMenu = function (form_id, previous) {
	form_name = form_id.split('_');
	if(form_name.length == 2) {
		current_step = form_name[0];
		next_step = form_name[1];
		$('#'+current_step).attr('class', 'current');
		$('#'+next_step).attr('class', 'inactive');
	} else if(form_name.length == 3) {
		previous_step = form_name[0];
		current_step = form_name[1];
		next_step = form_name[2];
		$('#'+previous_step).attr('class', 'active');
		$('#'+current_step).attr('class', 'current');
		$('#'+next_step).attr('class', 'inactive');
	}
}

wizard.prototype.dummy = function () {
	
}

// pre-submit callback 
wizard.prototype.showRequest = function (formData, jqForm, options) {
	$.blockUI({message:''});
	$('#loading').attr('style', 'display:block;');
}

// post-submit callback 
wizard.prototype.showResponse = function (responseText, statusText)  {
	$.unblockUI();
	$('#loading').attr('style', 'display:none;');
}