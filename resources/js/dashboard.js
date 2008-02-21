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
        if(event) {
            event.stop();
        }
    },

    'toggleClose' : function(event) {
        toggleElementClass('closed', this.elm);
        if(this.getStatus() == KTDashboard.OPEN || this.getStatus() == KTDashboard.ROLLEDUP) {
            this.setStatus(KTDashboard.CLOSED);
        } else {
            this.setStatus(KTDashboard.OPEN);
        }
        if(event) {
            event.stop();
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
KTDashboard.columns = Set('left', 'right');


KTDashboard.prototype = {
    'initialize' : function(dashboard) {
        this.element = dashboard;

        this.initializeDraggables();

        var self = this;
        map(function(e) {
                if(hasElementClass(e, 'empty')) {
                    return;
                }
                var d = new KTDashlet();
                d.initialize(e, self);
                self.dashlets[e.id] = { 'object' : d, 'state' : KTDashboard.OPEN };
            }, this.getDashletBlocks());

        // make add button
        var breadcrumbs = $('add-dashlet');
        this.addButton = INPUT({'id':'add_dashlet', 'type':'submit', 'value':_('Add Dashlet')});
        breadcrumbs.insertBefore(this.addButton, breadcrumbs.firstChild);
        this.hideAddButton();

        connect(this.addButton, 'onclick', this, 'onclickAdd');
        connect(window, 'onbeforeunload', this, 'pushState');
    },

    'mochikitInitializeDraggables' : function() {
        var dashOpts = {
            'tag':'div',
            'constraint': false,
            'tree':true,
            'only' : ['dashboard_block'],
            'handle' : 'dashboard_block_handle'
        };
        MochiKit.Sortable.Sortable.create(this.element, dashOpts);
    },

    'initializeDraggables' : function() {
        map(function(e) {
                if(e.id) {
                    new YAHOO.example.DDList(e.id);
                    //new YAHOO.util.DD(e.id);
                }
            }, this.getDashletBlocks());
        map(function(e) {
                if(e.id) {
                    new YAHOO.example.DDList(e.id);
                    //new YAHOO.util.DD(e.id);
                }
            }, this.getDashletBlockStoppers());
        new YAHOO.example.DDListBoundary('copyrightbar');
        new YAHOO.example.DDListBoundary('breadcrumbs');
        new YAHOO.example.DDListBoundary('bodyLeftRepeat');
        new YAHOO.example.DDListBoundary('bodyRightRepeat');
        YAHOO.util.DDM.mode = 1;
    },

    'getDashletBlocks' : function() {
        return getElementsByTagAndClassName('*', 'dashboard_block', this.element);
    },

    'getDashletBlockStoppers' : function(){
    	return getElementsByTagAndClassName('*', 'dashboard_block_empty', this.element);
    },

    'hideAddButton' : function() {
        hideElement(this.addButton);
    },

    'showAddButton' : function() {
        showElement(this.addButton);
    },

    'statusChange' : function(status) {
        var s = 0;
        if(status == KTDashboard.CLOSED) {
            s = 'closed';
        } else if (status == KTDashboard.OPEN) {
            s = 'open';
        } else if (status == KTDashboard.ROLLEDUP) {
            s = 'rolled';
        } else {
            s = 'undefined';
        }
        if(status == KTDashboard.CLOSED) {
            this.showAddButton();
        } else if(status == KTDashboard.OPEN || status == KTDashboard.ROLLEDUP) {
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
                                if(hasElementClass(dashlet.elm, 'rolled-up')) {
                                    self.setStatus(id, KTDashboard.ROLLEDUP);
                                }
                                event.stop();
                            });
                    appendChildNodes(dashletList, linkli);
                });
        appendChildNodes(addDialog, H2(null, _('Add Dashlets')));
        appendChildNodes(addDialog, dashletList);

        var closeLink = A({'class':'closeLink','href':'#'}, _('close'));
        connect(closeLink, 'onclick', function(event) { removeElement(addDialogScreen); });

        appendChildNodes(addDialog, closeLink);
        appendChildNodes(addDialogScreen, addDialog);
        appendChildNodes(document.body, addDialogScreen);

        event.stop();
    },

    'getColumn' : function(which) {
        return $('dashboard-container-' + which);
    },

    'serialize' : function() {
        var self = this;
        var ret = {};

        for(var col in KTDashboard.columns) {
            ret[col] = [];
            var container = this.getColumn(col);
            forEach(getElementsByTagAndClassName('*', 'dashboard_block', container), function(e) {
                        if(e.id) {
                            try {
                                ret[col].push({'id':e.id, 'state':self.dashlets[e.id]['state']});
                            } catch(e) {
                            };
                        }
                    });
        }
        return ret;
    },

    'unserialize' : function(state) {
        var bucket = DIV({'style':'display:none'});
        appendChildNodes(document.body, bucket);
        forEach(this.getDashletBlocks(), function(d) {
                    if(d.id && !hasElementClass(d, 'empty')) {
                        appendChildNodes(bucket, d);
                    }});

        var hasClosed = false;

        for(var col in KTDashboard.columns) {
            var container = this.getColumn(col);
            for(var i=0; i<state[col].length; i++) {
                var dashlet = state[col][i];
                var elm = $(dashlet['id']);
                var dstate = dashlet['state'];
                var dobj = this.dashlets[dashlet['id']];

                if(dstate == KTDashboard.ROLLEDUP) {
                    dobj.object.toggleRollup();
                } else if (dstate == KTDashboard.CLOSED) {
                    dobj.object.toggleClose();
                    hasClosed = true;
                }

                appendChildNodes(container, elm);
            }
            appendChildNodes(container, $('end-'+col));
        }
        if(hasClosed) {
            this.showAddButton();
        }
    },

    'getPushLocation' : function() {
        var l = window.location;
        return l.protocol + '//' + l.host + l.pathname;
    },

    'pushState' : function(event) {
        var args = {'action' : 'json',
                    'json_action' : 'saveDashboardState',
                    'state' : serializeJSON(this.serialize())  };
        var xmlreq = getXMLHttpRequest();
        xmlreq.open('GET', this.getPushLocation() + '?' + queryString(args), false);
        xmlreq.send(null);
    }

};



addLoadEvent(
    function() {
        var browser = navigator.appName;
        if(0 && browser == 'Microsoft Internet Explorer') {
            alert("For this technology preview, the draggable dashboard does not work in Internet Explorer. Please try with Firefox.");
            var location = window.location.href.toString().replace(/dashboard2/gi, 'dashboard');
            window.location = location;
        } else {
            var dashboard = new KTDashboard();
            dashboard.initialize($('content'));
            if(savedState) {
                dashboard.unserialize(savedState);
            }
        }
    });


