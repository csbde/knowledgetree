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
    
    settings_popup = {
      tl: { radius: 8 },
      tr: { radius: 8 },
      bl: { radius: 8 },
      br: { radius: 8 },
      antiAlias: true,
      autoPad: false
    }
    
	document.getElementById('username').focus();
    
    if(document.getElementById("loginbox_outer")){
	    //login box
	    var loginBox = document.getElementById("loginbox_outer");
	   	var loginBlock = new curvyCorners(settings, loginBox);
	   	loginBlock.applyCornersToAll();
	}
	
	//error message popups
    var errorMessages = getElementsByClass("ktErrorMessage");
    for(var r = 0; r < errorMessages.length; r++){
    	var errorBlock = new curvyCorners(settings_popup, errorMessages[r]);
    	errorBlock.applyCornersToAll();
	    errorMessages[r].style.margin="0 0 26px 0";
    }
  }