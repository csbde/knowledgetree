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
    
    settings_dashlets = {
      tl: { radius: 8 },
      tr: { radius: 8 },
      bl: { radius: 8 },
      br: { radius: 8 },
      antiAlias: true,
      autoPad: false
    }
    var browser = $T.getBrowserAgent();
    
	// pageBody
    var pageBody = document.getElementById("pageBody");
    var pageBlock = new curvyCorners(settings, pageBody);
    pageBlock.applyCornersToAll();
    pageBody.style.backgroundPosition="bottom left";
    
    // footer
    var footer = document.getElementById("copyrightbarBorder");
    var footerBlock = new curvyCorners(settings, footer);
    footerBlock.applyCornersToAll();
    
    // standard dashlets
    var dashBlock = new curvyCorners(settings_dashlets, "ktBlock");
    dashBlock.applyCornersToAll();
    var dashboardBlocks = getElementsByClass("ktBlock");
    for(var t = 0; t < dashboardBlocks.length; t++){
	    dashboardBlocks[t].style.margin="0 0 26px 0";
    }
    
    //info dashlets
    var infoBlock = new curvyCorners(settings_dashlets, "ktInfo");
    infoBlock.applyCornersToAll();
    var infodashlets = getElementsByClass("ktInfo");
    for(var q = 0; q < infodashlets.length; q++){
	    infodashlets[q].style.margin="0 0 26px 0";
    }
    
    //info message popups
    var infoMessage = new curvyCorners(settings_dashlets, "ktInfoMessage");
    infoMessage.applyCornersToAll();
    var infoMessages = getElementsByClass("ktInfoMessage");
    
    //error message dashlets
    var errorBlock = new curvyCorners(settings_dashlets, "ktError");
    errorBlock.applyCornersToAll();
    var errordashlets = getElementsByClass("ktError");
    for(var r = 0; r < errordashlets.length; r++){
	    errordashlets[r].style.margin="0 0 26px 0";
    }
    
    //error message popups
    var errorMessage = new curvyCorners(settings_dashlets, "ktErrorMessage");
    errorMessage.applyCornersToAll();
    var errorMessages = getElementsByClass("ktErrorMessage");

    if(getElementsByClass("noportlets").length != '1'){
	    //portlets
	    	var portletBlock = new curvyCorners(settings_dashlets, "portlet");
		    portletBlock.applyCornersToAll();
		    var portlets = getElementsByClass("portlet");
		    for(var t = 0; t < portlets.length; t++){
			    portlets[t].style.margin="0 0 26px 0";
		    }
	    
	    //portlets
	    	var exp_portletBlock = new curvyCorners(settings_dashlets, "portlet expanded");
		    exp_portletBlock.applyCornersToAll();
		    var exp_portlets = getElementsByClass("portlet expanded");
		    for(var u = 0; u < exp_portlets.length; u++){
			    exp_portlets[u].style.margin="0 0 26px 0";
		    }
	}
  }