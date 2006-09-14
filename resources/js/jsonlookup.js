var _aLookupWidgets = {};

function getJSONLookupWidget(name) {
    if(!isUndefinedOrNull(_aLookupWidgets[name])) {
    return _aLookupWidgets[name];
    } else {
    return false;
    }
}

function JSONLookupWidget() {
}

JSONLookupWidget.prototype = {
    
    /* bind_add and bind_remove are functions to be called with the key:value's of selected items */

    'initialize' : function(name, action) {
    bindMethods(this);

    this.sName = name;
    this.sAction = action;

    
    this.oSelectAvail = $('select_' + name + '_avail');
    this.oSelectAssigned = $('select_' + name + '_assigned');
    this.oFilterAvail = $('filter_' + name + '_avail');
    this.oFilterAssigned = $('filter_' + name + '_assigned');

    this.savedFilter = this.oFilterAvail.value;
    this.savedSelector = this.oFilterAssigned.value;
    this.filterTimer = null;

    this.aItemsAdded = [];
    this.aItemsRemoved = [];

    connect(this.oFilterAvail, 'onkeyup', this, 'onchangeFilter');    
    connect(this.oFilterAssigned, 'onkeyup', this, 'onchangeSelector');    
    connect(name + '_add', 'onclick', this, 'onclickAdd');
    connect(name + '_remove', 'onclick', this, 'onclickRemove');
    connect(name + '_show_all', 'onclick', this, 'onclickShowAll');
    
    this.triggers = {};
    this.triggers['add'] = null;
    this.triggers['remove'] = null;

    this.initialValuesLoaded = false;
        var d = this.getValues();
        d.addCallback(this.postInitialize);
    },

    'addTrigger' : function(event, func) {
        this.triggers[event] = func;
    },


    // values handling

    'getValues' : function(all) {
        var act = this.sAction;
        if(!isUndefinedOrNull(all)) {
            act += '&' + queryString({'filter' : '%'});
        } else if(this.savedFilter) {
            act += '&' + queryString({'filter' : this.savedFilter});
        } else if(!this.initialValuesLoaded) {
            act += '&' + queryString({'selected' : '1'});
        }
    
        var d = loadJSONDoc(act);
        d.addErrback(this.errGetValues);
        d.addCallback(checkKTError);
        d.addCallback(this.saveValues);
        d.addCallback(this.renderValues);
        return d;
    },

    'errGetValues' : function(res) {
        alert('There was an error retrieving data. Please check connectivity and try again.');
        this.oValues = {'off':'-- Error fetching values --'};
        this.renderValues();
    },

    'saveValues' : function(res) {
        this.oValues = res;
        return res;
    },

    'renderValues' : function() {
        var aOptions = [];
        var bSelFound = false;
        for(var k in this.oValues) {
            var found = false;
            for(var i=0; i<this.oSelectAssigned.options.length; i++) {
                if(this.oSelectAssigned.options[i].value == k) {
                    found = true; break;
                }
            }
    
            if(found) { 
                continue; 
            }
    
                
            var aParam = {'value':k};
            if(k == 'off') {
                aParam['disabled'] = 'disabled';
            }
    
            var val = this.oValues[k];
            var sDisp = val;
            
            if(!isUndefinedOrNull(val['display'])) {
                var sDisp = val['display'];
                if(!isUndefinedOrNull(val['selected']) && val['selected'] === true) {
                    val['selected'] = undefined;
                    aParam['selected'] = true;
                    bSelFound = true;
                    aParam['value'] = k;
                }
            }
            var oO = OPTION(aParam, sDisp);
            aOptions.push(oO);
        }
    
        replaceChildNodes(this.oSelectAvail, aOptions);
        if(bSelFound) { 
            this.onclickAdd();
        }
    },

    'postInitialize' : function(res) {
        if(!isUndefinedOrNull(this.triggers['postInitialize'])) {
            this.triggers['postInitialize']();
        }
    }, 
    

    'modItems' : function(type, value) {
        var aTarget = (type == 'add') ? 'aItemsAdded' : 'aItemsRemoved';
        var aOtherTarget = (type == 'remove') ? 'aItemsAdded' : 'aItemsRemoved';
    
        // check against other - if other has value, remove it from other, skip next bit
        var aNewOther = [];
        var exists = false;
        var i = 0;
        for(i=0; i<this[aOtherTarget].length; i++) {
            if(this[aOtherTarget][i]!=value) {
            aNewOther.push(this[aOtherTarget][i]);
            } else {
            exists = true;
            }
        }
        if(exists) {
            this[aOtherTarget] = aNewOther;
            var sHidden  = this.sName + '_items_' + ((type == 'remove') ? 'added' : 'removed');
            $(sHidden).value = this[aOtherTarget].join(",");
            return;
        }
    
        exists = false;
        for(i=0; i<this[aTarget].length; i++) {
            if(this[aTarget][i] == value) {
            exists = true;
            break;
            }
        }
        
        if(!exists) {
            this[aTarget].push(value);
            var sHidden  = this.sName + '_items_' + ((type == 'add') ? 'added' : 'removed');
            $(sHidden).value = this[aTarget].join(",");
        }
    
    },
    
    
    // signals handling

    'onchangeFilter' : function(e) {    
    if(this.savedFilter != this.oFilterAvail.value) {
        this.savedFilter = this.oFilterAvail.value;
        if(!isUndefinedOrNull(this.filterTimer)) {
        this.filterTimer.canceller();
        }
        this.filterTimer = callLater(0.2, this.getValues);
    }
    return true;
    },

    'onchangeSelector' : function(e) {
    if(this.savedSelector != this.oFilterAssigned.value) {
        this.savedSelector = this.oFilterAssigned.value;        
        forEach(this.oSelectAssigned.options, bind(function(o) {
        if(!this.savedSelector.length) { 
            o.selected = false;
        } else {
            if(o.innerHTML.toLowerCase().search(this.savedSelector) != -1) {
            o.selected = true;
            } else {
            o.selected = false;
            }
        }
        }, this));
    }
    },

    '_moveOptions' : function(dir) {
    },

    'onclickAdd' : function(e) {
    var aCurOptions = extend([], this.oSelectAssigned.options);
    forEach(this.oSelectAvail.options, bind(
            function(o) {
            try {
                if(o.value == 'off') return;
                var a = o.selected;                            
                if(a == 'selected' || a === true) {
                this.modItems('add', o.value);
                try {
                    o.selected = false;
                } catch(e) {
                    o.setAttribute('selected', false);
                }
                aCurOptions.push(o);
                
                if(!isUndefinedOrNull(this.triggers['add'])) {
                    this.triggers['add'](this.oValues[o.value]);
                }
                }
            } catch(e) {
                log('exception');
                // forEach(keys(e), function(k) { log(k,':', e[k]); });
            }
    }, this));
    
    aCurOptions.sort(keyComparator('innerHTML'));
    replaceChildNodes(this.oSelectAssigned, aCurOptions);

    },

    'onclickRemove' : function(e) {
    var aOptions = [];
    forEach(this.oSelectAssigned.options, bind(function(o) {
        if(o.selected == 'selected' || o.selected === true) {
        this.modItems('remove', o.value);
        if(!isUndefinedOrNull(this.triggers['remove'])) {
            var obj = { 'type' : (o.value.substring(0,1) == 'g') ? 'group' : 'role',
                'id' : o.value.substring(1) };
            this.triggers['remove'](obj);
        }
        } else {
        aOptions.push(o);
        }
    }, this));
    replaceChildNodes(this.oSelectAssigned, aOptions);
    this.renderValues();
    },

    'onclickShowAll' : function(e) {
    this.oFilterAvail.value = '';
    this.savedFilter = '';
    this.getValues(true);
    e.stop();
    }
}

function initJSONLookup(name, action) {
    return function() {
    _aLookupWidgets[name] = new JSONLookupWidget();
    _aLookupWidgets[name].initialize(name, action);
    }
}


             
