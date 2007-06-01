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
    var copyrightBar = document.getElementById("copyrightbarBorder");
    var footerBlock = new curvyCorners(settings, copyrightBar);
    footerBlock.applyCornersToAll();
    
    // standard dashlets
    var dashboardBlocks = getElementsByClass("ktBlock");
    for(var t = 0; t < dashboardBlocks.length; t++){
    	var dashBlock = new curvyCorners(settings_dashlets, dashboardBlocks[t]);
    	dashBlock.applyCornersToAll();
	    dashboardBlocks[t].style.margin="0 0 26px 0";
    }
    
    //info dashlets
    var infodashlets = getElementsByClass("ktInfo");
    for(var q = 0; q < infodashlets.length; q++){
    	var infoBlock = new curvyCorners(settings_dashlets, infodashlets[q]);
    	infoBlock.applyCornersToAll();
	    infodashlets[q].style.margin="0 0 26px 0";
    }
    
    //info message popups
    var infoMessages = getElementsByClass("ktInfoMessage");
    for(var s = 0; s < infoMessages.length; s++){
    	var infoMessage = new curvyCorners(settings_dashlets, infoMessages[s]);
    	infoMessage.applyCornersToAll();
	    infoMessages[s].style.margin="0 0 26px 0";
    }
    
    //error message popups
    var errorMessages = getElementsByClass("ktError");
    for(var r = 0; r < errorMessages.length; r++){
    	var errorBlock = new curvyCorners(settings_dashlets, errorMessages[r]);
    	errorBlock.applyCornersToAll();
	    errorMessages[r].style.margin="0 0 26px 0";
    }
    
    //portlets
	    var portlets = getElementsByClass("portlet");
	    for(var t = 0; t < portlets.length; t++){
	    	var portletBlock = new curvyCorners(settings_dashlets, portlets[t]);
	    	portletBlock.applyCornersToAll();
		    portlets[t].style.margin="0 0 26px 0";
	    }
    
    //portlets
	    var exp_portlets = getElementsByClass("portlet expanded");
	    for(var u = 0; u < exp_portlets.length; u++){
	    	var exp_portletBlock = new curvyCorners(settings_dashlets, exp_portlets[u]);
	    	exp_portletBlock.applyCornersToAll();
		    exp_portlets[u].style.margin="0 0 26px 0";
	    }
  }