function AdminVersionDashlet() {
}

AdminVersionDashlet.prototype = {
    'initialize' : function() {
	this.dashlet = $('admin_version_dashlet');
	this.span_newVersion = $('new_version');	
	this.currentVersions = _KT_VERSIONS;
	this.check();
    },

    'check' : function() {
	var res = loadJSONDoc(_KT_VERSIONS_URL);
	res.addCallback(bind(this.callbackCheck, this));
    },

    'callbackCheck' : function(res) {
	var updates = 0;

	for(var k in this.currentVersions) {
	    if(res[k]!=this.currentVersions[k]) {
		updates ++;
		appendChildNodes('up_upgrades', SPAN({'class':'up_new_version'}, k + ': ' + res[k]), BR(null));
	    }
	}

	if(updates == 0) {
            // next line is cause I thought this dashlet was causing orphaned 
            // dashlet buttons. it doesn't seem to be, but this may be needed.
            // more investigation necessary.
            //            this.dashlet.style.display = 'none';
	} else if(updates == 1) {
	    $('up_single').style.display = 'block';
	    this.dashlet.style.display = 'block';
	} else {
	    $('up_multi').style.display = 'block';
	    this.dashlet.style.display = 'block';
	}	    
    }
}

    

addLoadEvent(function() {
		 var d = new AdminVersionDashlet();
		 d.initialize();
	     });
