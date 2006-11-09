function KTDashlet() {
}

KTDashlet.prototype = {
    'initialize' : function(e, dashboard) {
        bindMethods(this);
        this.dashboard = dashboard;
        this.id = e.id;
        this.elm = e;
        this.body = getElementsByTagAndClassName('*', 'dashboard_block_body', e)[0];
        connect(getElementsByTagAndClassName('*', 'action_rollup', e)[0], 'onclick', this, 'toggleRollup');
        connect(getElementsByTagAndClassName('*', 'action_close', e)[0], 'onclick', this, 'toggleClose');
    },

    'toggleRollup' : function(event) {
        toggleElementClass('rolled-up', this.elm);
        if(this.getStatus() == KTDashboard.OPEN) {
            this.setStatus(KTDashboard.ROLLEDUP);
        } else {
            this.setStatus(KTDashboard.OPEN);
        }

        event.stop();
    },

    'toggleClose' : function(event) {
        toggleElementClass('closed', this.elm);
        if(this.getStatus() == KTDashboard.OPEN) {
            this.setStatus(KTDashboard.CLOSED);
        } else {
            this.setStatus(KTDashboard.OPEN);
        }
    },

    'setStatus' : function(status) {
        this.dashboard.setStatus(this.id, status);
    },

    'getStatus' : function() {
        return this.dashboard.getStatus(this.id);
    }
        
};


function KTDashboard() {
    this.dashlets = {};
}

KTDashboard.OPEN = 0;
KTDashboard.ROLLEDUP = 1;
KTDashboard.CLOSED = 2;


KTDashboard.prototype = {
    'initialize' : function(dashboard) {
        this.element = dashboard;

        var dashOpts = {
            'tag':'div',
            'dropOnEmpty':true,
            'constraint': false,  
            'tree':true,
            'only' : ['dashboard_block'],
            'handle' : 'dashboard_block_handle'
        };
        MochiKit.Sortable.Sortable.create(this.element, dashOpts);

        var self = this;
        map(function(e) {
                if(hasElementClass(e, 'empty')) {
                    return;
                }
                var d = new KTDashlet();
                d.initialize(e, self);
                self.dashlets[e.id] = { 'object' : d, 'state' : KTDashboard.OPEN };
            }, getElementsByTagAndClassName('*', 'dashboard_block', this.element));

        this.addButton = $('add_dashlet');
        connect(this.addButton, 'onclick', this, 'onclickAdd');
    },

    'statusChange' : function(status) {
        if(status == KTDashboard.CLOSED) {
            showElement(this.addButton);
        } else if(status == KTDashboard.OPEN) {
            var closed = this.getDashletsInState(KTDashboard.CLOSED);
            if(closed.length === 0) {
                hideElement(this.addButton);
            }
        }
    },
                

    'setStatus' : function(id, status) {
        this.dashlets[id]['state'] = status;
        this.statusChange(status);
    },

    'getStatus' : function(id) {
        return this.dashlets[id]['state'];
    },

    'getDashletsInState' : function(state) { 
        var ret = [];
        for(var i in this.dashlets) {
            if(this.dashlets[i]['state'] == state) {
                ret.push(i);
            }
        }
        return ret;
    },        

    'getDashletTitle' : function(elm) {
        var h2 = getElementsByTagAndClassName('H2', null, elm);
        if(h2.length) {
            return h2[0].innerHTML;
        } else {
            return null;
        }
    },

    'onclickAdd': function(event) {
        var closed = this.getDashletsInState(KTDashboard.CLOSED);
        var self = this;
        var addDialogScreen = DIV({'class':'addDialogScreen'});
        var addDialog = DIV({'class':'addDialog'});
        var dashletList = UL(null);

        forEach(closed, function(id) {
                    var dashletelm = $(id);
                    var dashlet = self.dashlets[id]['object'];

                    var link = A({'class':'dashletLink', 'href':'#'}, self.getDashletTitle(dashletelm));                    
                    var linkli = LI(null, link);

                    connect(link, 'onclick', function(event) {
                                removeElement(linkli);
                                dashlet.toggleClose(event);
                            });
                    appendChildNodes(dashletList, linkli);
                });
        appendChildNodes(addDialog, H2(null, 'Add Dashlets'));
        appendChildNodes(addDialog, dashletList);
        
        var closeLink = A({'class':'closeLink','href':'#'}, 'close');
        connect(closeLink, 'onclick', function(event) { removeElement(addDialogScreen); });

        appendChildNodes(addDialog, closeLink);
        appendChildNodes(addDialogScreen, addDialog);
        appendChildNodes(document.body, addDialogScreen);
    },

    'serialize' : function() {
        var self = this;
        var cols = Set('left', 'right');
        var ret = {};

        for(var col in cols) {
            ret[col] = [];
            var container = $('dashboard-container-' + col);
            forEach(getElementsByTagAndClassName('*', 'dashboard_block', container), function(e) {
                        if(e.id != '') {                        
                            ret[col].push({'id':e.id, 'state':self.dashlets[e.id]['state']});
                        }
                    });
        }
        
        alert(ret);                        
        
    }
        

}



addLoadEvent(
    function() {
        var browser = navigator.appName;
        if(browser == 'Microsoft Internet Explorer') {
            alert("For this technology preview, the draggable dashboard does not work in Internet Explorer. Please try with Firefox.");
            var location = window.location.href.toString().replace(/dashboard2/gi, 'dashboard');
            window.location = location;
        } else {
            var dashboard = new KTDashboard();
            dashboard.initialize($('content'));
            dashboard.serialize();
        }
    });

               