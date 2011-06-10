/*  */

kt = new function() {

	this.init = function() {

	}

};

kt.lib = {}; /* General functions */
kt.events = {}; /* KT's own symantic event engine library */
kt.ktjapi = {}; /* AJAX library to connect with KTAPI in the background */
kt.pages = {}; /* Specific to a certain page */

(function($) {
	$(document).ready(function() {kt.init();});
})(jQuery);