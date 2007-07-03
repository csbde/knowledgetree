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
  }