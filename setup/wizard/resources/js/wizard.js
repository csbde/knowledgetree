// Class Wizard
var ajaxOn = false;
function wizard() {
	this.ajaxOn = false;
}

// Toggle Advance Database options
wizard.prototype.toggleClass = function(ele, option) { //adv_options|php_details|php_ext_details|php_con_details
	var style = $('.'+ele).attr('style');
	style = w.trim(style);
	style = style.toLowerCase();
	var patt1=/none/gi; // preg match
	var patt2=/block/gi;
	if(style.match(patt1) == 'none') {
		if(this.ajaxOn) {
			w.slideElement($('.'+ele), 'down');
		} else {
			$('.'+ele).attr('style', 'display: block;');
		}
    	if($('#'+option).attr('innerHTML') != '&nbsp;&nbsp;Advanced Options') {
    		$('#'+option).attr('innerHTML', 'Hide Details');
    	}
	} else if(style.match(patt2) == 'block') {
		if(this.ajaxOn) {
			w.slideElement($('.'+ele), 'up');
		} else {
			$('.'+ele).attr('style', 'display: none;');
		}
		if($('#'+option).attr('innerHTML') != '&nbsp;&nbsp;Advanced Options') {
    		$('#'+option).attr('innerHTML', 'Show Details');
		}
	} else {
	}
}

wizard.prototype.slideElement = function(el, dir) {
	if(dir == 'down')
		$(el).slideDown("slow");
	else
		$(el).slideUp("slow");
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
	var first = $("#first");
	var last = $("#last");
	var email = $("#email");
	if(first.attr('value').length < 1) {
		$("#reg_error").html('Enter a First Name');
		w.focusElement(first);
		return false;
	}
	if(!w.nameCheck(first.attr('value'))) {
		$("#reg_error").html('Enter a valid First Name');
		w.focusElement(first);
		return false;
	}
	if(last.attr('value').length < 1) {
		$("#reg_error").html('Enter a Last Name');
		w.focusElement(last);
		return false;
	}
	if(!w.nameCheck(last.attr('value'))) {
		$("#reg_error").html('Enter a valid Last Name');
		w.focusElement(last);
		return false;
	}
	if(!w.emailCheck(email.attr('value'))) {
		$("#reg_error").html('Enter a valid email address');
		w.focusElement(email);
		return false;
	}

	return true;
}

wizard.prototype.nameCheck = function(str) {
	str = w.trim(str);
	var nameRegxp = /^([a-z A-Z]+)$/;
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
	//$.blockUI({message:''});
	$.blockUI({overlayCSS:{opacity:0.1}, fadeIn:500, fadeOut:500, message:''});
	$('#loading').attr('style', 'display:block;');
}

// post-submit callback
wizard.prototype.showResponse = function (responseText, statusText) {
	$.unblockUI();
	$('#loading').attr('style', 'display:none;');
}

wizard.prototype.refresh = function (page)  {
	var address = "index.php?step_name="+page;
	var div = 'content_container';
	$.ajax({
		url: address,
		dataType: "html",
		type: "GET",
		cache: false,
		beforeSubmit: w.showRequest,
		success: function(data) {
			$("#"+div).empty();
			$("#"+div).append(data);
			w.showResponse;
			return;
		}
	});
}

wizard.prototype.getUrl = function (address, div)  {
	$("#"+div).empty();
	$.ajax({
		url: address,
		dataType: "html",
		type: "GET",
		cache: false,
		success: function(data) {
			$("#"+div).empty();
			$("#"+div).append(data);
			return;
		}
	});
}

wizard.prototype.sendJavaLocation = function ()  {
	$('form').submit();
}

wizard.prototype.sendRegistration = function ()  {
	$('form').submit();
}

wizard.prototype.clearSessions = function ()  {

}