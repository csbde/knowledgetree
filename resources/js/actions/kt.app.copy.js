if (typeof(kt.app) == 'undefined') { kt.app = {}; }
if (typeof(kt.api) == 'undefined') { kt.api = {}; }

/**
 * Modal dialog for copying / moving documents / folders
 */
kt.app.copy = new function() {

	// contains a list of fragments that will get preloaded
    var fragments = this.fragments = [];
    var fragmentPackage = this.fragmentPackage = []

    // contains a list of executable fragments that will get preloaded
    var execs = this.execs = ['actions/copy.dialog'];
    var execPackage = this.execPackage = [execs];

    // scope protector. inside this object referrals to self happen via 'self' rather than 'this'
    // to make sure we call the functionality within the right scope.
    var self = this;

    var elems = this.elems = {};

    this.init = function() {
        kt.api.preload(fragmentPackage, execPackage, true);
    }

    // Container for the EXTJS window
    this.copyWindow = null;

    // ENTRY POINT: Calling this function will set up the environment, display the dialog,
    //              and hook up the AjaxUploader callbacks to the correct functions.
    // objectId, if set, identifies a share with a non-licensed user for a selected object (folder or document)
    this.showCopyWindow = function() {
        var copyWin = new Ext.Window({
            id              : 'extcopywindow',
            layout          : 'fit',
            width           : 500,
            resizable       : false,
            closable        : true,
            closeAction     : 'destroy',
            y               : 50,
            autoScroll      : false,
            bodyCssClass    : 'ul_win_body',
            cls             : 'ul_win',
            shadow          : true,
            modal           : true,
            title           : 'Copy',
            html            : kt.api.execFragment('actions/copy.dialog')
        });

        copyWin.addListener('show', function() {
            self.tree();
        });

        self.copyWindow = copyWin;
        copyWin.show();
    }

    this.closeWindow = function() {
        copyWindow = Ext.getCmp('extcopywindow');
        copyWindow.destroy();
    }

    this.tree = function() {
        jQuery("#demo")
//            .bind("open_node.jstree close_node.jstree", function (e) {
//            	jQuery("#log1").html("Last operation: " + e.type);
//            })
            .jstree({
                "core" : {
                    "animation": 0,
                    "strings": {"loading": "Fetching data...", "new_node": "New Folder"}
                },
                "json_data" : {
        			"data" : self.getNodes()
        		},
                "plugins" : [ "themes", "json_data" ]
            });
	}

	this.getNodes = function() {
	    var func = 'siteapi.getFolderStructure';
	    var synchronous = true;
	    var params = {};
	    params.id = 1;
	    var data = ktjapi.retrieve(func, params, 300);
	    var response = data.data.nodes;
        var nodes = jQuery.parseJSON(response);

        console.log(nodes);
	    return nodes;
	}

    this.init();
}