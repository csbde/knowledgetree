var _table = null;

function PermissionsTable() {
}

PermissionsTable.prototype = {
    'initialize' : function(sLookupName, sEntityName, aPermissions) {
	bindMethods(this);

	this.aPermissions = aPermissions;

	this.oLookup = getJSONLookupWidget(sLookupName);
	this.oLookup.sAction += '&' + queryString({'permissions':map(function(a){return a['id'];}, aPermissions)});
	this.oLookup.addTrigger('add', this.addRow);
	this.oLookup.addTrigger('remove', this.removeRow);
	this.oLookup.addTrigger('postInitialize', this.enableForm);

	this.aRows = {};

	this.dContainer = $('permissions_table_container');
	this.dTHead = THEAD(null,
			    TR(null,
			       TH({'width':'40%'}, sEntityName),
			       map(function(aPerm) {
				       return TH({'class':'centered'}, aPerm['name']);
				   }, aPermissions)));
	this.dTBody = TBODY(null);
	this.dTable = TABLE({'class':'kt_collection'}, this.dTHead, this.dTBody);

	this.dSubmit = $('submitButtons');
	hideElement(this.dSubmit);
	replaceChildNodes(this.dContainer, this.dTable);
    },

    '_getARow' : function(oRow) {
	var aKeys = keys(this.aRows);
	var found = false;
	for(var i=0; i<aKeys.length; i++) {
	    var r = this.aRows[aKeys[i]];	    
	    if(r['row_type'] == oRow['type'] && r['row_id'] == oRow['id']) {
		return r;
	    }
	}
	return false;
    },
 
    '_removeARow' : function(oRow) {
	var aKeys = keys(this.aRows);
	var aNewRows = {};
	for(var i=0; i<aKeys.length; i++) {
	    var r = this.aRows[aKeys[i]] 
	    if(!(r['row_type'] == oRow['type'] && r['row_id'] == oRow['id'])) {
		aNewRows[aKeys[i]] = r;
	    }
	}
	this.aRows = aNewRows;
    },	

    'enableForm' : function() {
	showElement(this.dSubmit);
    },
	

    'addRow' : function(oRow) {	
	if(this._getARow(oRow)) {
	    return;
	}
	
	var dRow = TR(null, 
		      TD(null, SPAN({'class':'descriptiveText'}, oRow['type'].substring(0,1).toUpperCase() + oRow['type'].substring(1) + ': '), oRow['name']),
		      map(function(aPerm) {
			      var aProps = { 'type':'checkbox', 
					     'name':'foo['+aPerm['id']+']['+oRow['type']+'][]',
					     'value':oRow['id'] 
					   };

			      var found = false;
			      for(var j=0;j<oRow['permissions'].length;j++) {
				  if(oRow['permissions'][j] == aPerm['id']) { found = true; break; }
			      }
			      if(found) {
				  aProps['checked'] = 'checked'; 
			      }
			      return TD({'class':'centered'}, INPUT(aProps));
			  }, this.aPermissions));

	dRow.row_type = oRow['type'];
	dRow.row_id = oRow['id'];
	appendChildNodes(this.dTBody, dRow); 
	this.aRows[oRow['type'].substring(0,1) + oRow['id']] = dRow;
    },

    'removeRow' : function(oRow) {
	var oExistingRow = this._getARow(oRow);
	log(oExistingRow);
	if(!oExistingRow) 
	    return;
	removeElement(oExistingRow);
	this._removeARow(oRow);
    }
}

function initializeTable(aPermissions) {
    _table = new PermissionsTable;
    _table.initialize('entities',
		      _('Role or Group'),
		      aPermissions);
};

function initializePermissions(sName, sAction, aPermissions) {
    addLoadEvent(function() {
		     initJSONLookup(sName, sAction)();
		     initializeTable(aPermissions);
		 });
}
    
