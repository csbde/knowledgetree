window.onload = function()
  {
    settings = {
      tl: { radius: 15},
      tr: { radius: 15 },
      bl: { radius: 15 },
      br: { radius: 15 },
      antiAlias: true,
      autoPad: false
    }

    var browser = navigator.userAgent.toLowerCase();

	if(browser.msie && browser.version == '6.0'){
		var pageBody = document.getElementById("pageBody");
		pageBody.style.backgroundPosition="bottom left";
	}else{
		// pageBody
	    var pageBody = document.getElementById("pageBody");
	    var pageBlock = new curvyCorners(settings, pageBody);
	    pageBlock.applyCornersToAll();
	    pageBody.style.backgroundPosition="bottom left";

	    // footer
	    var footer = document.getElementById("copyrightbarBorder");
	    var footerBlock = new curvyCorners(settings, footer);
	    footerBlock.applyCornersToAll();
	}
	Loader();
  }

  window.onsubmit = function(){
  	if(typeof disable_loader == "undefined"){
	  	var myElem = document.getElementById("modalDiv");
		myElem.style.display = "block";
	}
  }
  function Loader()
{
	var myElem = document.getElementById("modalDiv");
	myElem.style.display = "none";
}