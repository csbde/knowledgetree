$(document).ready(function() {
    var options = {target: '#content_container', beforeSubmit:  w.showRequest, success: w.showResponse};
	$.blockUI.defaults.css = {};
	var override = $('form').attr('onsubmit');
 	if(override == undefined) {
	    $('form').ajaxForm(options);
	    w.adjustMenu($('form').attr('id'));
 	} else {
	    var options = {target: '#content_container', beforeSubmit: w.validateRegistration, success: w.adjustMenu($('form').attr('id'))};
	    $('form').ajaxForm(options);
 	}
 	$(document).bind('keydown', 'Ctrl+n',function (evt){alert("n"); return false; });
 	$(document).bind('keydown', 'Ctrl+p',function (evt){alert("p"); return false; });
 	$(document).bind('keydown', 'Ctrl+e',function (evt){alert("e"); return false; });
 	$(document).bind('keydown', 'Ctrl+i',function (evt){alert("i"); return false; });
});