var GSFN;
if(GSFN == undefined) {
  GSFN = {};
}
 
if(!GSFN.initialized) {
  
  GSFN.gId = function(id) {
    return document.getElementById(id);
  };

  GSFN.hasClassName = function(element, className) {
    var elementClassName = element.className;

    return (elementClassName.length > 0 && (elementClassName == className ||
      new RegExp("(^|\\s)" + className + "(\\s|$)").test(elementClassName)));
  };

  GSFN.addClassName = function(element, className) {
    if (!GSFN.hasClassName(element, className))
      element.className += (element.className ? ' ' : '') + className;
    return element;
  };

  GSFN.removeClassName = function(element, className) {
    var newClass = GSFN.strip(element.className.replace(new RegExp("(^|\\s+)" + className + "(\\s+|$)"), ' '));
    element.className = newClass;
    return element;
  };

  GSFN.strip = function(string) {
    return string.replace(/^\s+/, '').replace(/\s+$/, '');
  };
  
  GSFN.add_css = function(css_content) {
    var head = document.getElementsByTagName('head')[0];
    var style = document.createElement('style');
    style.type = 'text/css';
    
    if(style.styleSheet) {
      style.styleSheet.cssText = css_content;
    } else {
      rules = document.createTextNode(css_content);
      style.appendChild(rules);
    }
    head.appendChild(style);
  }

  GSFN.initialized = true;
}

GSFN.cookies = {
  set: function(name, value, daysToExpire) {
    var expire = '';
    if (daysToExpire != undefined) {
      var d = new Date();
      d.setTime(d.getTime() + (86400000 * parseFloat(daysToExpire)));
      expire = '; expires=' + d.toGMTString();
    }
    return (document.cookie = escape(name) + '=' + escape(value || '') + expire + "; path=/");
  },
  get: function(name) {
    var cookie = document.cookie.match(new RegExp('(^|;)\\s*' + escape(name) + '=([^;\\s]*)'));
    return (cookie ? unescape(cookie[2]) : null);
  },

  erase: function(name) {
    var cookie = Cookie.get(name) || true;
    Cookie.set(name, '', -1);
    return cookie;
  }
};


GSFN.safe_redirect = function(url) {
  window.location.href = url;
  return false;
};

GSFN.goto_gsfn = function() {
  if(GSFN.company_url) {
    GSFN.safe_redirect(GSFN.full_company_url());
  }
};

GSFN.redirect_back_to = function(url, company_name, fastpass_id) {
  GSFN.current_fastpass_id = fastpass_id
  
  if(this.cookies.get("gsfn:cancel_redirect") != GSFN.current_fastpass_id){ 
    this.show_overlay();
    GSFN.redirect_timeout = setTimeout(function(){
      GSFN.safe_redirect(GSFN.add_fastpass_query_to(url));
    }, 5000);
    
    company = document.getElementById("gsfn_company")
    company.innerHTML = company_name + "'s";
  }
};

GSFN.add_fastpass_query_to = function(url) {
  if(url.match(/\?(.+)/)) {
    return url + "&fastpass=" + encodeURIComponent(GSFN.fastpass_url)
  } else {
    return url + "?fastpass=" + encodeURIComponent(GSFN.fastpass_url)
  }
};

GSFN.full_company_url = function() {
  return GSFN.company_url + "?fastpass=" + encodeURIComponent(GSFN.fastpass_url);
}
  
GSFN.show_overlay = function() {
  this.add_stylesheet();
  var overlay_element = document.createElement("div")
  overlay_element.innerHTML = this.overlay_template;
  overlay_element.id = "gsfn_overlay"
  var embedChild = this.get_firstchild(document.body);
  this.countdown();
  if(embedChild){ document.body.insertBefore(overlay_element, embedChild); } 
};
  
GSFN.countdown = function() {
  setInterval(function() {
    var seconds = document.getElementById('signover_seconds');
    if (seconds.innerHTML > 0) {
      seconds.innerHTML = seconds.innerHTML-1
    }
  }, 1000);
};

GSFN.hide_overlay = function() {
  document.getElementById("gsfn_overlay").style.display = "none";
};
  
GSFN.cancel_signover = function() {
  this.cookies.set("gsfn:cancel_redirect", GSFN.current_fastpass_id);
  this.hide_overlay();
  clearTimeout(GSFN.redirect_timeout);
};

GSFN.get_firstchild = function(parent) {
  result = parent.firstChild;
  while(result.nodeType!=1) {
    result = result.nextSibling;
  }
  return result;
};
  
GSFN.add_stylesheet = function() {
  GSFN.add_css("#gsfn_overlay {\n  position: fixed;\n  z-index: 1000;\n  width: 100%;\n  height: 100%;\n  top: 0;\n  left: 0; }\n  #gsfn_overlay .bg {\n    opacity: 0.8;\n    -moz-opacity: 0.8;\n    filter: alpha(opacity=80);\n    z-index: 1000;\n    width: 100%;\n    height: 100%;\n    background-color: #CCC;\n    position: absolute;\n    top: 0;\n    left: 0; }\n\n#redirecting_back {\n  width: 340px;\n  border: solid 2px #838383;\n  background: white;\n  padding: 20px;\n  font-family: Arial, sans-serif;\n  z-index: 1001;\n  position: relative;\n  margin: 0 auto;\n  margin-top: 100px; }\n  #redirecting_back h2 {\n    font-family: Arial, sans-serif;\n    margin: 0 0 10px 0;\n    padding: 0;\n    font-size: 16px; }\n    #redirecting_back h2 img {\n      vertical-align: middle;\n      margin-right: 5px; }\n  #redirecting_back .cancel_link {\n    float: right; }\n  #redirecting_back .loading {\n    margin: 0 auto;\n    margin-top: 10px;\n    width: 220px; }\n  #redirecting_back .back_link {\n    text-align: center;\n    font-size: 13px; }\n  #redirecting_back p {\n    font-size: 13px; }\n    #redirecting_back p strong {\n      white-space: nowrap; }\n");
};

GSFN.rewrite_satisfaction_urls_if_needed = function() {
  if(GSFN.rewrite_urls == true) {
    GSFN.rewrite_satisfaction_urls();
  }
};

GSFN.rewrite_satisfaction_urls = function() {
  link_nodes = document.getElementsByTagName("a");
  link_array = [];
  for(i = 0; i < link_nodes.length; i++) {
    link = link_nodes.item(i);
    link_array.push(link);
  }
  
  if(GSFN.company_cname){
    GSFN.company_cname_regex = new RegExp("http(s?)://" + GSFN.company_cname);
  }
  
  setTimeout(function() {
    GSFN.do_rewrite_satisfaction_urls(link_array)
  }, 50);
};

GSFN.do_rewrite_satisfaction_urls = function(link_array) {
  link_batch = link_array.slice(0, 5);
  link_array = link_array.slice(5);
  
  for(i = 0; i < link_batch.length; i++) {
    link = link_batch[i];
    if(GSFN.link_has_fastpass(link)) { continue; }
    if(!GSFN.link_needs_fastpass(link)) { continue; }
    
    link.href = GSFN.add_fastpass_query_to(link.href);
  }
  
  if(link_array.length > 0) {
    setTimeout(function() {
      GSFN.do_rewrite_satisfaction_urls(link_array);
    }, 50);
  }
};

GSFN.link_has_fastpass = function(link) {
  var url = link.href;
  var matched;
  if(!(matched = url.match(/\?(.+)/))) { return false; }
  
  var query_string = matched[1];
  
  if(query_string.match(/fastpass=/)) {
    return true;
  } else {
    return false;
  }
};

GSFN.link_needs_fastpass = function(link) {
  var url = link.href;
  if(url.match(/http(s?):\/\/getsatisfaction.com/)) { return true; }
  if(GSFN.company_cname_regex && url.match(GSFN.company_cname_regex)) { return true; }
  
  return false;
};

GSFN.overlay_template = "\u003Cdiv id='redirecting_back'\u003E\n\u003Ca class=\"cancel_link\" href=\"#\" onclick=\"GSFN.cancel_signover();; return false;\"\u003E[x]\u003C/a\u003E\n\u003Ch2\u003E\n\u003Cimg alt=\"Gold_key\" src=\"http://getsatisfaction.com/images/gold_key.png\" /\u003E\nYour log in was successful!\n\u003C/h2\u003E\n\u003Cp\u003E\nYou will be returned to\n\u003Cspan id='gsfn_company'\u003Ethe\u003C/span\u003E\nsupport community in\n\u003Cstrong\u003E\n\u003Cspan id='signover_seconds'\u003E5\u003C/span\u003E\nseconds.\n\u003C/strong\u003E\nWe look forward to your participation!\n\u003C/p\u003E\n\u003Cdiv class='loading'\u003E\u003Cimg alt=\"Bar_spinner\" src=\"http://getsatisfaction.com/images/bar_spinner.gif\" /\u003E\u003C/div\u003E\n\u003Cdiv class='back_link'\u003E\u003Ca href=\"#\" id=\"gsfn_go_back_link\" onclick=\"GSFN.goto_gsfn(); return false;\"\u003ETake me there now!\u003C/a\u003E\u003C/div\u003E\n\u003C/div\u003E\n\u003Cdiv class='bg'\u003E\u003C/div\u003E\n";
GSFN.fastpass_common_loaded = true;

