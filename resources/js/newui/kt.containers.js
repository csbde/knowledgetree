/*  */

kt=new function(){
//	var $priv={
//		call:function(functionName, params){
//			//does functionName exist?
//			
//		}
//	};
	
	this.init=function(){
//		$priv.call('kt.pages.all.onload');
//		$priv.call('kt.pages.browseView.onload');
		jQuery(document).click(function(){setTimeout('kt.lib.setFooter();',500);});
		setTimeout('kt.lib.setFooter();',500);
	}
};

kt.lib={}; /* General functions */
kt.events={}; /* KT's own symantic event engine library */
kt.ktjapi={}; /* AJAX library to connect with KTAPI in the background */
kt.pages={}; /* Specific to a certain page */

(function($){
	$(document).ready(function(){kt.init();});
})(jQuery);