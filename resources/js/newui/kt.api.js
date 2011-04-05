kt.api = new function() {
	this.cacheTimeout = 20;
	this.persistentDataCacheTimeout = 30000;

	/* Upload related functions */

	this.addDocuments = function(documents, callback, errorCallback, customTimeout) {
		var params = {};
		params.documents = documents;
		var synchronous = false;
		var func = 'siteapi.uploadFile';

		if (callback) {
			ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.persistentDataCacheTimeout, customTimeout);
			return;
		} else {
			var data = ktjapi.retrieve(func, params, this.persistentDataCacheTimeout);
			return data.data;
		}
	};

	this.docTypeRequiredFields = function(docTypeId) {
		var params = {};
		params.type = docTypeId;
		var synchronous = false;
		var func = 'siteapi.docTypeRequiredFields';
		var data = ktjapi.retrieve(func, params, this.persistentDataCacheTimeout);
		return data.data;

	};

	this.docTypeFields = function(docTypeId) {
		var params = {};
		params.type = docTypeId;
		var synchronous = false;
		var func = 'siteapi.docTypeFields';
		var data = ktjapi.retrieve(func, params, this.persistentDataCacheTimeout);
		return data.data;

	};

	this.docTypeHasRequiredFields = function(docType, callback, errorCallback) {
		var params = {};
		var synchronous = false;
		var func = 'siteapi.docTypeHasRequiredFields';
		params.docType = docType;
		if (callback === true) {
			var data = ktjapi.retrieve(func, params, this.cacheTimeout);
			return data;
		} else {
			ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.cacheTimeout);
			return;
		}
	};

	this.getDocTypeForm = function(docType, callback, errorCallback) {
		if (typeof(docType) == 'undefined') {
			docType = 'default';
		}

		var params = {};
		var synchronous = false;
		var func = 'siteapi.getDocTypeForm';
		params.docType = docType;

		if (callback === true) {
			var data = ktjapi.retrieve(func, params, this.cacheTimeout);
			return data;
		} else {
			ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.cacheTimeout);
			return;
		}
	};

	this.getSubFolders = function(folderId, callback, errorCallback) {
		var params = {};
		var synchronous = false;
		var func = 'siteapi.getSubFolders';
		params.folderId = folderId;

		if (callback === true) {
			var data = ktjapi.retrieve(func, params, this.cacheTimeout);
			return data;
		} else {
			ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.cacheTimeout);
			return;
		}
	};

	this.getFolderHierarchy = function(folderId, callback, errorCallback) {
		var params = {};
		var synchronous = false;
		var func = 'siteapi.getFolderHierarchy';
		params.folderId = folderId;

		if (callback === true) {
			var data = ktjapi.retrieve(func, params, this.cacheTimeout);
			return data;
		} else {
			ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.cacheTimeout);
			return;
		}
	};

    /* Invite users related functionality */

	this.inviteUsers = function(addresses, group, userType, sharedData, callback, errorCallback) {
		var params = {};
		params.addresses = encodeURIComponent(addresses);
		params.group = group;
		params.userType = userType;
		params.sharedData = sharedData;
		var synchronous = false;
		var func = 'siteapi.inviteUsers';
		if (callback === true) {
			var data = ktjapi.retrieve(func, params, 200);
			return data;
		} else {
			ktjapi.callMethod(func, params, callback, synchronous, errorCallback, 200);
			return;
		}
	};

	this.shareUsers = function(addresses, userType, sharedData, callback, errorCallback) {
		var params = {};
		params.addresses = encodeURIComponent(addresses);
		params.userType = userType;
		params.sharedData = sharedData;
		var synchronous = false;
		var func = 'siteapi.inviteUsers';
		if (callback === true) {
			var data = ktjapi.retrieve(func, params, 200);
			return data;
		} else {
			ktjapi.callMethod(func, params, callback, synchronous, errorCallback, 200);
			return;
		}
	};

	this.isShared = function(callback, errorCallback) {
		var params = {};
		var synchronous = false;
		var func = 'siteapi.hasWrite';
		if (callback === true) {
			var data = ktjapi.retrieve(func, params, this.cacheTimeout);
			return data;
		} else {
			ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.cacheTimeout);
			return;
		}
	};

	this.getUserType = function() {
		params = {};
		var func = 'siteapi.getUserType';
		var ret = ktjapi.retrieve(func, params, 30000);

		return ret.data.usertype;
	};
	
	/* Metadata functions */
	this.changeDocumentType = function(documentID, documentTypeID, callback, errorCallback) {
		console.log('changeDocumentType '+documentID+' '+documentTypeID);
		params = {};
		params.documentID = documentID;
		params.documentTypeID = documentTypeID;
		
		var func = 'siteapi.changeDocumentType';
		
		if (callback) {
			console.log('callback');
			ktjapi.callMethod(func, params, callback, synchronous, errorCallback, this.persistentDataCacheTimeout);
			return;
		} else {
			console.log('no callback');
			var data = ktjapi.retrieve(func, params, this.persistentDataCacheTimeout);
			console.dir(data);
			return data.data;
		}
	};

	/* Template related functions */

	this.preloadFragment = function(fragName, params) {
		if (!kt.lib.Object.is_object(params)) { params = {}; }
		params = kt.lib.Object.extend({name:fragName}, params);
		var func = 'template.getFragment';
		var ret = ktjapi.callMethod(func, params, function() {}, false, function() {}, 30000);
	};

	this.preloadExecutable = function(fragName, params) {
		if (!kt.lib.Object.is_object(params)) { params = {}; }
		params = kt.lib.Object.extend({name:fragName}, params);
		var func = 'template.execFragment';
		var ret = ktjapi.callMethod(func, params, function() {}, false, function() {}, 30000);
	};

	this.getFragment = function(fragName, params) {
		if (!kt.lib.Object.is_object(params)) { params = {}; }
		params = kt.lib.Object.extend({name:fragName}, params);
		var func = 'template.getFragment';

		var ret = ktjapi.retrieve(func, params, 30000);
		return ret.data.fragment;
	};

	this.parseFragment = function(fragName, data) {
		var params = {};
		params.name = fragName;
		params.data = data;
		var func = 'template.parseFragment';

		ret = ktjapi.retrieve(func, params, 30000);
		return ret.data.parsed;
	};

	this.execFragment = function(fragName, data) {
		var params = {};
		params.name = fragName;
		params.data = data;
		var func = 'template.execFragment';

		ret = ktjapi.retrieve(func, params, 30000);
		return ret.data.fragment;
	};

}
