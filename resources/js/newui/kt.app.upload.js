if (typeof(kt.app) == 'undefined') { kt.app = {}; }

/**
* The multi-file upload widget. This object contains all the code
* for the client-side management of single instance of the widget.
*/
kt.app.upload = new function() {

    var self = this;

    // Stores the objects that deal with the individual files being uploaded.
    // Elements in here are of type uploadStructure.
    var data = this.data = {};

    // Contains a list of fragments that will get preloaded.
    var fragments = this.fragments = [
                                'upload/upload.dialog',
                                'upload/upload.dialog.item',
                                'upload/upload.dialog.item.nobulk',
                                'upload/upload.metadata.fieldset'
                    ];
    var fragmentPackage = this.fragmentPackage = [fragments];

    // Contains a list of executable fragments that will get preloaded.
    var execs = this.execs = ['upload/upload.doctypes', 'upload/upload.metadata.dialog'];
    var execPackage = this.execPackage = [execs];

    // A storage container for various DOM elements that need to be accessed repeatedly.
    var domElements = this.domElements = {};

    // Container for qq.fileUploader (AjaxUploader2 code.)
    this.uploader = null;
    this.uploadfolder = null;
    this.uploadWindow = null;

    this.data.files = {};

    // Initializes the upload widget on creation. Currently does preloading of resources.
    this.init = function() {
        kt.api.preload(fragmentPackage, execPackage, true);
    }

    // TODO: removeItem also needs to remove it from the actual upload?

    // Add a file item to the list of files to upload and manage.
    // Must not be called directly, but as a result of adding a file using AjaxUploader)
    this.addUpload = function(fileName, docTypeHasRequiredFields)
    {
        var metadata = {};
        var docTypeId = 1;

        if (self.data['applyMetaDataToAll'] && (self.data['globalMetaData'] != undefined)) {
            metadata = self.data['globalMetaData']['metadata'];
            docTypeId = self.data['globalMetaData']['docTypeID'];
            docTypeHasRequiredFields = !self.data['globalMetaDataRequiredDone'];
        }

        var dialog = self.getDialog();

        jQuery(self.domElements.item_container).append(dialog);

        if (fileName.length > 50) {
            jQuery('.ul_filename').addClass('ellipsis');
        }

        var obj = new self.uploadStructure({
                                        'fileName': (fileName + ''),
                                        'elem': dialog,
                                        'metadata': metadata,
                                        'docTypeId': docTypeId,
                                        'has_required_metadata': docTypeHasRequiredFields,
                                        'required_metadata_done': !docTypeHasRequiredFields,
                                        'parent': self
        });
        kt.lib.meta.set(dialog[0], 'item', obj);
        self.data.files[fileName] = obj;
        obj.startUpload();

        // are we dealing with a possible bulk upload?
        var index = fileName.lastIndexOf('.');
        var ext = fileName.substr(index).toLowerCase();
        var e = kt.lib.meta.get(dialog[0], 'item');
        if (ext == '.gz' || ext == '.bz2') {
            var substr = fileName.substring(0, index);
            var subindex = substr.lastIndexOf('.');
            var subext = substr.substr(subindex).toLowerCase();
            if (subext != '.tar') {
                return obj;
            }
        }

        // do we need to suggest a bulk upload?
        if (self.isBulkExtension(ext)) {
            jQuery('#' + e.options.elem[0].id + ' .ul_bulk_checkbox').css('display', 'block');
        }

        return obj;
    }

    // Check which dialog to get.
    this.getDialog = function()
    {
        var usertype = kt.api.getUserType('upload/upload.dialog.item');

        if (usertype == 4) {
            var dialog = jQuery(kt.api.getFragment('upload/upload.dialog.item.nobulk'));
        }
        else {
            var dialog = jQuery(kt.api.getFragment('upload/upload.dialog.item'));
        }

        return dialog;
    }

    this.isBulkExtension = function(ext)
    {
        var bulkExtensions = new Array('.ar', '.bz', '.bz2', '.deb', '.gz', '.rar', '.tgz', '.tar', '.tbz', '.zip');

        for (var i = 0; i < bulkExtensions.length; ++i) {
            if (bulkExtensions[i] == ext) {
                return true;
            }
        }

        return false;
    }

    // A DOM helper function that will take elem as any dom element inside a file item fragment
    // and return the js object related to that element.
    this.getItem = function(elem)
    {
        var e = jQuery(elem).parents('.ul_item')[0];
        var meta = kt.lib.meta.get(e, 'item');
        return meta;
    }

    this.getMetaItem = function(elem)
    {
        var e = jQuery(elem).parents('.metadataTable')[0];
        var meta = kt.lib.meta.get(e, 'item');
        return meta;
    }

    // metadata is object in format {"docTypeID": docTypeID, "metadata": metadata}
    this.applyMetadataToAll = function(metadata, requiredDone)
    {
        self.data['applyMetaDataToAll'] = true;
        self.data['globalMetaData'] = metadata;

        // cycle through every file and apply the metadata!
        jQuery.each(self.data.files, function(key, file) {
            //first update the doc type of every file!
            file.options.docTypeId = metadata['docTypeID'];
            file.options.metadata = metadata['metadata'];
            file.options.required_metadata_done = requiredDone;
        });

        self.data['globalMetaDataRequiredDone'] = requiredDone;
    }

    this.setGlobalMetadataFalse = function()
    {
        self.data['applyMetaDataToAll'] = false;
        self.data['globalMetaData'] = {};

        self.data['globalMetaDataRequiredDone'] = requiredDone;
    }

    // Find the js object matching a given filename
    this.findItem = function(fileName)
    {
        if (typeof(self.data.files[fileName]) != 'undefined') {
            return self.data.files[fileName];
        }

        return null;
    }

    this.getNodeTxt = function(html)
    {
        if (strpos(html, '<') == false) {
            nodeText = trim(html);
        } else {
            nodeText = trim(html.substr(0, strpos(html, '<')));
        }

        return nodeText;
    }

    this.getNodePath = function(folderId)
    {
        nodeInTree = jQuery('ul#loadedpath li[folderid='+folderId+']');

        if (folderId == 1) {
            pathToItem = ' / (Root Directory)';
        } else {
            pathToItem = kt.app.upload.getNodeTxt(nodeInTree.html());

			nodeInTree.parentsUntil('#loadedpath').each(function(i) {
				if (jQuery(this).get(0).tagName == 'LI') {
					// console.log('Parent folder id '+jQuery(this).attr('folderid'));
					if (jQuery(this).attr('folderid') == 1) {
						pathToItem = '/'+pathToItem;
					} else {
						pathToItem = kt.app.upload.getNodeTxt(jQuery(this).html())+'/'+pathToItem;
					}
				}
            });
        }

        return pathToItem;
    }

    this.loadFolderPath = function(currentId)
    {
        html = '<ul id="currentPathStuff">';
        currentNode = jQuery('ul#loadedpath li[folderid=' + currentId + ']');

        if (currentId + '' != '1') {
            html += '<li class="folder_up" folderid="' + currentNode.parent().parent().attr('folderid') + '">[Folder Up]</li>';
        }

		if (currentNode.length == 0) {
			// NEED TO RELOAD TREE
			// console.log('NEED TO RELOAD TREE');
		} else {
			if (currentNode.hasClass('loadedchildren')) {
				childItems = jQuery('ul#loadedpath li[folderid='+currentId+']>ul');
				if (childItems.length == 0) {
					// do nothing
				} else {
					childItems.children().each(function(i) {
						child = jQuery(this);
						nodeText = kt.app.upload.getNodeTxt(child.html());
						html += '<li folderid="' + child.attr('folderid') + '">' + nodeText + '</li>';
					});
				}

				html += '</ul>';
				jQuery('#folderpathchooser').html(html);
			} else {
				jQuery('#folderpathchooser').html('<div class="loading"></div>');

				kt.api.getSubFolders(currentId, function(result) {
					if (result.data.children.length == 0) {
						// console.log('no children');
					} else {
						parentUl = jQuery('ul#loadedpath li[folderid=' + currentId + '] > ul');
						if (parentUl.length == 0) {
							parentUl = jQuery('ul#loadedpath li[folderid=' + currentId + ']').append('<ul></ul>');
						}

						jQuery.each(result.data.children, function(i, item) {
							if (jQuery('ul#loadedpath li[folderid=' + currentId + '] > ul > li[folderid=' + item.id + ']').length == 0) {
								jQuery('ul#loadedpath li[folderid=' + currentId + '] > ul').append('<li class="notloaded" folderid="' + item.id + '">' + item.name + '</li>');
							}

							html += '<li folderid="' + item.id + '">' + item.name + '</li>';
						});
					}

					jQuery('ul#loadedpath li[folderid=' + currentId + ']').removeClass('notloaded').addClass('loadedchildren');

					html += '</ul>';
					jQuery('#folderpathchooser').html(html);
                }, function() {});
            }
        }
    }

    // add the uploaded files to the repo
    this.addDocuments = function()
    {
        var onDashboardPage = window.location.pathname.indexOf('dashboard') > 0;
        var progressWidgetShown = false;

        this.hideWindow();

        filesToAdd = {};
        var i = 0;

        var atLeastOneSingle = false;
        var atLeastOneBulk = false;

        var folderID = jQuery("#currentPath").val();

        // iterate through files to see which are ready to be added
        jQuery.each(self.data.files, function(key, value) {
            if (!progressWidgetShown) {
                progressWidgetShown = true;
                kt.app.upload.unhideProgressWidget();
            }

            // create the array of files to be uploaded
            if (value.options.is_uploaded) {
                var fileName = value.options['fileName'];
				var doBulk = value.options.do_bulk_upload;

				if (doBulk) {
					atLeastOneBulk = true;
                } else {
                    atLeastOneSingle = true;
                }

                var docTypeID = value.options['docTypeId'];

                var metadata = {};
                var j = 0;
                jQuery.each(value.options['metadata'], function(key, value) {
                    metadata[j++] = {'id':key, 'value':value};
                });

                // NB: encode the filename!!
                fileName = encodeURIComponent(fileName);

                var tempFile = self.data['s3TempPath'] + fileName;

                filesToAdd[i++] = {
                    'baseFolderID': self.data['baseFolderID'],
                    'fileName': fileName,
                    'folderID': folderID,
                    'docTypeID': docTypeID,
                    'metadata': metadata,
                    's3TempFile': tempFile,
                    'doBulk': doBulk
                };
            }
        });

        kt.api.addDocuments(filesToAdd, function(data) {
            var hasError = false;
            // put this in a try...catch because error occurs if user browses away before the upload completes
			// BUT upload still does complete, error occurs because tries to add item to non-existent page
			try {
				jQuery.each(data.data.addedDocuments, function(key, value) {
					// get the response from the server
					var responseJSON = jQuery.parseJSON(value);

					if (responseJSON.error) {
						// console.log(responseJSON.error.message);
						hasError = true;
						// errorMessage += responseJSON.error.filename+': '+responseJSON.error.message+' ';
					} else if (responseJSON.success) {
						// delete the file from the array because we don't want to upload it again!
						delete self.data.files[responseJSON.success.filename];

						// don't display the item if it isn't the same folder or if you are on the dashboard
						if (!responseJSON.success.isBulk && !onDashboardPage && responseJSON.success.baseFolderID == folderID) {
							// now add the new item to the grid
							var item = {
								id: responseJSON.success.id,
								is_immutable: false,
								is_checkedout: false,
								filename: responseJSON.success.filename,
								filesize: responseJSON.success.filesize,
								document_url: responseJSON.success.document_url,
								title: responseJSON.success.title,
								owned_by: responseJSON.success.owned_by,
								created_by: responseJSON.success.created_by,
								created_date: responseJSON.success.created_date,
								modified_by: responseJSON.success.modified_by,
								modified_date: responseJSON.success.modified_date,
								mimeicon: responseJSON.success.mimeicon,
								allowdoczohoedit: responseJSON.success.allowdoczohoedit,
								isfinalize_document: responseJSON.success.isfinalize_document,
								user_id: responseJSON.success.user_id,
								item_type: responseJSON.success.item_type,
								thumbnail: '',
								thumbnailclass: 'nopreview'
							};

							// remove the "folder is empty" widget from the Browse View
							jQuery('.page .notification').remove();

							// now add the item to the Browse View
							kt.pages.browse.addDocumentItem(item);
						}
					}
				});

				kt.lib.setFooter();

				if (atLeastOneSingle && atLeastOneBulk) {
					var progressMessage = 'Files added. E-mail link to files sent.';
				} else if (atLeastOneSingle) {
					var progressMessage = 'Files added.';
				} else if (atLeastOneBulk) {
					progressMessage = ' E-mail link to files sent.';
				}

				kt.app.upload.updateProgress(progressMessage, false);
				kt.app.upload.fadeProgress(10000);

				if (hasError) {
					progressMessage = 'One or more files failed to upload.';

					kt.app.upload.updateProgress(progressMessage, true);

					jQuery('#uploadProgress').fadeIn();

					kt.app.upload.fadeProgress(10000);
				}
			} catch(e) {
				// console.dir(e);
				kt.app.upload.fadeProgress(10000);
			}
        }, function() {}, i * 30000);
        // 30 seconds for each file!

        this.closeWindow();
    }

    this.closeWindow = function()
    {
        uploadWindow = Ext.getCmp('extuploadwindow');
        self.data = {};
        self.data.files = {};
        uploadWindow.destroy();
    }

    this.hideWindow = function()
    {
        uploadWindow = Ext.getCmp('extuploadwindow');
        uploadWindow.hide();
    }

    this.enableAddButton = function()
    {
        var btn = jQuery('#ul_actions_upload_btn');
        btn.removeAttr('disabled');
    }

    this.disableAddButton = function()
    {
        var btn = jQuery('#ul_actions_upload_btn');
        btn.attr('disabled', 'true');
    }

    this.unhideProgressWidget = function()
    {
        var progress = jQuery('.uploadProgress');
        progress.removeClass('error');
        progress.text('Adding files ...');
        progress.css('display', 'block');
        progress.css('visibility', 'visible');
        progress.append('<img src="/resources/graphics/newui/large-loading.gif" style="float: right;"/>');
    }

    this.updateProgress = function(message, isError)
    {
        var progress = jQuery('.uploadProgress');
        if (progress != null) {
            if (isNaN(message)) {
                progress.text(message);
            } else if (message <= 100) {
				progress.text(message+"%");
			}
		}

		if (isError) {
            progress.addClass('error');
        } else {
            progress.removeClass('error');
        }
    }

    this.fadeProgress = function(time)
    {
        jQuery('#uploadProgress').fadeOut(time);
    }

    // iterates through all the files and checks whether they have been added to S3
    this.allFilesReadyForUpload = function()
    {
        var allReady = true;
        // check whether we can enable Upload button
        // iterate through all files and check whether all ready for upload
        jQuery.each(self.data.files, function(key, value) {
            if (!value.options.is_uploaded) {
                allReady = false;
				// return false;
			}
		});

        return allReady;
    }

    // ENTRY POINT: Calling this function will set up the environment, display the upload dialog,
    // and hook up the AjaxUploader callbacks to the correct functions.
    this.showUploadWindow = function()
    {
        var docTypeHasRequiredFields = false;

        self.data = {};
        self.data.files = {};

        // does the Default Doc Type have required fields?
		kt.api.docTypeHasRequiredFields("1", function(data) {
			// if so, we need to disable the Upload button
			docTypeHasRequiredFields = data.data.hasRequiredFields;
		});

		var uploadWin = new Ext.Window({
			id          : 'extuploadwindow',
			layout      : 'fit',
			width       : 520,
			resizable   : false,
			closable    : true,
			closeAction :'destroy',
			y           : 50,
			autoScroll  : false,
			bodyCssClass: 'ul_win_body',
			cls			: 'ul_win',
			shadow      : true,
			modal       : true,
			title       : 'Upload Files',
            html        : kt.api.getFragment('upload/upload.dialog')
        });

        uploadWin.addListener('show', function() {
            // disable the Add Documents button on show since won't be any to add yet!
            kt.app.upload.disableAddButton();
            self.domElements.item_container = jQuery('.uploadTable .ul_list')[0];
            self.domElements.qq = jQuery('#upload_add_file .qq-uploader')[0];

            self.uploader = new qq.FileUploader({
                element: document.getElementById('upload_add_file'),
                // action: 'test.php',
                params: {},
                buttonText: 'Choose File',
				allowedExtensions: [],
				sizeLimit: 0,
				// taken out multiple uploads until able to figure out how to make it work with S3
				// issue is that need to force handlerClass = 'UploadHandlerForm' (see below) so that it works with S3
				// BUT this breaks multiple uploads!
				multiple: false,
				onSubmit: function(id, fileName) {
					// remove the 'No Files Selected' message
					jQuery('.no_files_selected').css('display', 'none');
                    // disable the Upload button as can only upload once upload to S3 completes
                    kt.app.upload.disableAddButton();

                    self.addUpload(fileName, docTypeHasRequiredFields);
                },
                onComplete: function(id, fileName, responseJSON) {
                                    console.dir(responseJSON)
                    try {
                        self.findItem(fileName).completeUpload();
                    } catch(e) {
                        // do nothing
                    }
                },
				// TODO: need to implement this!
				/*onCancel: function(id,fileName) {
				console.log('onCancel '+fileName);
				},*/
				showMessage: function(message) {alert(message);}
			});

			if (jQuery("input[name='fFolderId']").length == 0) {
				jQuery("#currentPath").val(1);
			} else {
				jQuery("#currentPath").val(jQuery("input[name='fFolderId']").val());
			}

			kt.api.getFolderHierarchy(jQuery('#currentPath').val(), function(result) {
				// console.dir(result);
				if (jQuery('#currentPath').val() == 1) {
					jQuery('ul#loadedpath').append('<li class="loadedchildren" folderid="' + jQuery('#currentPath').val() + '">' + result.data.currentFolder.name + '</li>');
				} else {
					jQuery.each(result.data.parents, function(i, item) {
						// console.dir(item);
						if (item.parent_id == null) {
							jQuery('ul#loadedpath').append('<li class="notloaded" folderid="' + item.id + '">' + item.name + '</li>');
						} else {
							jQuery('ul#loadedpath li[folderid=' + item.parent_id + ']').append('<ul><li class="notloaded" folderid="' + item.id + '">' + item.name + '</li></ul>');
						}
					});

					jQuery('ul#loadedpath li[folderid=' + result.data.currentFolder.parent_id + ']').append('<ul><li class="loadedchildren" folderid="' + jQuery('#currentPath').val() + '">' + result.data.currentFolder.name + '</li></ul>');
				}

				parentNode = jQuery('ul#loadedpath li[folderid=' + jQuery('#currentPath').val() + '] ul');
				if (parentNode.length == 0) {
					parentNode = jQuery('ul#loadedpath li[folderid=' + jQuery('#currentPath').val() + ']').append('<ul></ul>');
				}

				jQuery.each(result.data.children, function(i, item) {
					jQuery('ul#loadedpath li[folderid=' + jQuery('#currentPath').val() + '] ul').append('<li class="notloaded" folderid="' + item.id + '">' + item.name + '</li>');
				});

				var path = kt.app.upload.getNodePath(jQuery('#currentPath').val());
				var limit = 45;
				if (path.length > limit) {
					var index = path.length - limit;
					path = '.../'+path.substr(index, limit);
				}

				jQuery('#uploadpathstring').html(path);
				jQuery('#changepathlink').show();

				// var uniqueFileName = result.data.amazoncreds.awstmppath+kt.app.upload.uniqueFileName();
				// console.log('uniqueFileName '+uniqueFileName);

				// console.log('random '+result.data.amazoncreds.randomfile);

				self.uploader.setParams({
					AWSAccessKeyId          : result.data.amazoncreds.AWSAccessKeyId,
					acl                     : result.data.amazoncreds.acl,
					key                     : result.data.amazoncreds.awstmppath+"${filename}",	// result.data.amazoncreds.awstmppath+result.data.amazoncreds.randomfile,
					policy                  : result.data.amazoncreds.policy,
					'Content-Type'          : "binary/octet-stream",
					signature               : result.data.amazoncreds.signature,
					success_action_redirect : result.data.amazoncreds.success_action_redirect
				});

				// get the S3 temp location where all the uploads will be stored
				self.data['s3TempPath'] = result.data.amazoncreds.awstmppath;	// +result.data.amazoncreds.randomfile;

				self.uploader._options.action = result.data.amazoncreds.formAction; // doesnt work
				self.uploader._handler._options.action = result.data.amazoncreds.formAction; // works
			}, function() {});

			jQuery("#changepathlink").live("click", function() {
				// console.log('changepathlink');
				jQuery('#folderpathchooser').toggle();

				if (jQuery('#folderpathchooser').css('display') == 'none') {
					jQuery('#changepathlink').html('Change');
				} else {
					jQuery('#changepathlink').html('Done');
					kt.app.upload.loadFolderPath(jQuery('#currentPath').val());
				}
			});

			jQuery("#folderpathchooser li").live("click", function() {
				node = jQuery(this);
				jQuery('#currentPath').val(node.attr('folderid'));
				jQuery('#uploadpathstring').html(kt.app.upload.getNodePath(node.attr('folderid')));
				kt.app.upload.loadFolderPath(node.attr('folderid'));
			});
		});

		self.uploadWindow = uploadWin;
		uploadWin.show();

		// set the folder id of the folder we are in
		self.data['baseFolderID'] = jQuery("#currentPath").val();
	}

	//  Call the initialization function at object instantiation.
	this.init();

}

/**
*
*/
kt.app.upload.uploadStructure = function(options) {

	var self = this;
	var options = self.options = kt.lib.Object.extend({
		is_uploaded					: false,
		has_required_metadata		: false,
		required_metadata_done		: false,
		do_bulk_upload				: false,
		elem						: null,
		docTypeId					: 1,
		docTypeFieldData			: null,
		metadata					: {},
		parent						: null,
		fields_required				: {}
	},options);

	this.init = function(options) {
		self.setFileName(self.options.fileName);
	}

	this.setFileName = function(text) {
		var e = jQuery('.ul_filename', self.options.elem);
		e.html(text);
	}

	this.setProgress = function(text, state) {
		var state = kt.lib.Object.ktenum(state, 'uploading,waiting,ui_meta,add_doc,done', 'waiting');

		var e = jQuery('.ul_progress', self.options.elem);
		e.html(text);

		if (state == 'uploading') {
			jQuery('.ul_progress_spinner', self.options.elem).css('visibility', 'visible');
		} else {
			jQuery('.ul_progress_spinner', self.options.elem).css('visibility', 'hidden');
		}

		// make the 'Enter metadata' progress message clickable!
		if (state == 'ui_meta') {
			jQuery(e).css('cursor', 'pointer');
			//unbind the event so that it doesn't get added multiple times!
			jQuery(e).unbind();
			jQuery(e).one('click', function() {
				self.showMetadataWindow();
			});
		} else {
			jQuery(e).css('cursor', 'default');
			jQuery(e).unbind();
		}

		jQuery(self.options.elem).removeClass('ul_f_uploading ul_f_waiting ul_f_ui_meta ul_f_add_doc ul_f_done').addClass('ul_f_' + state);
	}

	this.startUpload = function() {
		self.setProgress('Uploading', 'uploading');
	}

	this.completeUpload = function() {
		self.options.is_uploaded = true;

		// has all the required metadata for the doc been entered?
		if (self.options.has_required_metadata && !self.options.required_metadata_done) {
			self.setProgress('Edit properties', 'ui_meta');
		} else {
			self.setProgress('Ready to add', 'waiting');
			// iterate through all the files and check whether they have been uploaded!
			if (kt.app.upload.allFilesReadyForUpload()) {
				kt.app.upload.enableAddButton();
			} else {
				kt.app.upload.disableAddButton();
			}
		}
	}

	this.setDocType = function(docTypeId) {
		self.options.docTypeId=docTypeId;
		self.options.docTypeFieldData=kt.api.docTypeFields(docTypeId);
	}

	this.setMetaData = function(key,value) {
		// console.log('setMetaData for '+key+ ' to '+value);
		self.options.metadata[key]=value;
	};

	this.getMetaData = function(key) {
		value = self.options.metadata[key];
		// console.log('getMetaData '+value);
		return value;
	};

	// remove the upload from the file dialog AND from the list of files
	this.removeItem = function() {
		var id = self.options.elem[0].id;
		jQuery('#' + id).remove();
		// also remove it from the list
		delete self.options.parent.data.files[self.options.fileName];

		if (jQuery.isEmptyObject(self.options.parent.data.files)) {
			jQuery('.no_files_selected').css('display', 'block');
			kt.app.upload.disableAddButton();
		} else {
			if (kt.app.upload.allFilesReadyForUpload()) {
				kt.app.upload.enableAddButton();
			} else {
				kt.app.upload.disableAddButton();
			}
		}
	}

	// flags the upload as being a bulk upload
	this.setAsBulk = function() {
		if (jQuery('#' + self.options.elem[0].id + ' .ul_bulk_checkbox input#unzip_checkbox').attr('checked')) {
			self.options.do_bulk_upload = true;
		} else {
			self.options.do_bulk_upload = false;
		}
	}

	this.showMetadataWindow = function() {
		var metaWin = new Ext.Window({
			layout      : 'fit',
			width       : 400,
			resizable   : false,
			closable    : false,
			closeAction :'destroy',
			y           : 50,
			autoScroll  : false,
			bodyCssClass: 'ul_meta_body',
			cls			: 'ul_meta',
			shadow      : true,
			modal       : true,
			title       : 'Document Properties',
			html        : kt.api.execFragment('upload/upload.metadata.dialog')
		});

		metaWin.addListener('close', function() {
			// have all required metadata fields been completed?
            var requiredDone = self.checkRequiredFieldsCompleted();
            self.options.required_metadata_done = requiredDone;

            // is "Apply To All" checked?
            var el = jQuery('#ul_meta_actionbar_apply_to_all')[0];
            var applyMetaToAll = el.checked;
            if (applyMetaToAll) {
                var metadata = {'docTypeID': self.options.docTypeId, 'metadata': self.options.metadata};
                kt.app.upload.applyMetadataToAll(metadata, self.options.required_metadata_done);
            } else {
                kt.app.upload.setGlobalMetadataFalse();
            }

            var allRequiredMetadataDone = true;
            jQuery.each(self.options.parent.data.files, function(key, value) {
                if (value.options.has_required_metadata) {
                    if (!value.options.required_metadata_done) {
                        value.setProgress('Edit properties', 'ui_meta');
						allRequiredMetadataDone = false;
						return;
					} else {
						value.setProgress('Ready to add', 'waiting');
					}
				} else {
					// value.setProgress('Ready to upload', 'waiting');
					allRequiredMetadataDone = true;
				}
			});

			// enable/disable the "Add Documents" button as appropriate
			if (allRequiredMetadataDone) {
				kt.app.upload.enableAddButton();
			} else {
				kt.app.upload.disableAddButton();
			}

			// reset required fields as could change
			self.options.fields_required = {};
		});

		self.options.metaWindow = metaWin;
		metaWin.show();

		var e = jQuery('.metadataTable')[0];
		self.options.metaDataTable = e;
		kt.lib.meta.set(e, 'item', self);

		if (self.options.parent != null) {
			// do we need to check Apply To All?
			if (self.options.parent.data['applyMetaDataToAll'] && (self.options.parent.data['globalMetaData'] != undefined)) {
				var el = jQuery('#ul_meta_actionbar_apply_to_all')[0];
				el.checked = true;
			}
		}

		self.changeDocType(self.options.docTypeId ? self.options.docTypeId : 1);
	}

	this.registerRequiredFieldNotDone = function(key) {
		// console.log('registerRequiredFieldNotDone ' + key);
		self.options.fields_required[key] = false;
	}

	this.registerRequiredFieldDone = function(key) {
		// console.log('registerRequiredFieldDone ' + key);
		self.options.fields_required[key] = true;
	}

	this.checkRequiredFieldsCompleted = function() {
		// console.log('checking if required done');
		var requiredFieldsCompleted = true;
		// var perp = '';

		jQuery.each(self.options.fields_required, function(key, value) {
			if (!value) {
				requiredFieldsCompleted = false;
				// perp = key;
				return;
			}
		});

		return requiredFieldsCompleted;
	}

	// change the Document Type
	this.changeDocType = function(docType) {
		self.options.docTypeId=docType;
		// reset required fields
		self.options.fields_required = {};

		// does this Doc Type have required fields?
		kt.api.docTypeHasRequiredFields(docType, function(data) {
			// if so, we need to disable the Upload button
			docTypeHasRequiredFields = data.data.hasRequiredFields;
			// console.log('docTypeHasRequiredFields '+docTypeHasRequiredFields);
			self.options.has_required_metadata = docTypeHasRequiredFields;
		});

		try {
			var selectBox = jQuery('.ul_doctype', self.options.metaDataTable)[0];
			// for (var idx in selectBox.options) {
			for (var idx = 0; idx < selectBox.options.length; idx++) {
				if (selectBox.options[idx].value == docType) {
					selectBox.selectedIndex = idx;
				}
			}

			var data = kt.api.docTypeFields(docType);
			self.options.docTypeFieldData = data.fieldsets;
			var container=jQuery('.ul_metadata', self.options.metaDataTable);

			container.html('');

			// if the fieldsets come through as an array, then it is empty
			if (!(data.fieldsets instanceof Array)) {
				for (var idx in self.options.docTypeFieldData) {
					var fieldSet = self.options.docTypeFieldData[idx].properties;
					var fields = self.options.docTypeFieldData[idx].fields;
					var t_fieldSet = jQuery(kt.lib.String.parse(kt.api.getFragment('upload/upload.metadata.fieldset'), fieldSet));

					container.append(t_fieldSet);

					for (var fidx in fields) {
						var field = fields[fidx];
						var fieldType = self.getFieldType(field);
						var t_field_filename = 'upload/upload.metadata.field.' + fieldType;
						var t_field = jQuery(kt.lib.String.parse(kt.api.getFragment(t_field_filename), field));
						t_fieldSet.append(t_field);
					}
				}
			}
		} catch(e) {}
	};

	this.getFieldType = function(field) {
		var datatype = ('' + field.data_type).toLowerCase();

		if (datatype == 'string') {
			if (field.has_inetlookup == 1) { return field.inetlookup_type; }
			if (field.has_lookuptree == 1) { return 'tree'; }
			if (field.has_lookup == 1) { return 'lookup'; }
		}

		if (datatype == 'large text') {
			if (field.is_html == 1) { return 'large-html'; }
			return 'large-text';
		}

		return datatype;
	};

	this.init(options);

};

/**
* Functions from http://phpjs.org/
*
*/
function strpos (haystack, needle, offset) {
	// http://kevin.vanzonneveld.net
	var i = (haystack+'').indexOf(needle, (offset || 0));
	return i === -1 ? false : i;
}

function trim (str, charlist) {
	// http://kevin.vanzonneveld.net
	var whitespace, l = 0, i = 0;
	str += '';

	if (!charlist) {
		// default list
		whitespace = " \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000";
	} else {
		// preg_quote custom list
		charlist += '';
		whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
	}

	l = str.length;
	for (i = 0; i < l; i++) {
		if (whitespace.indexOf(str.charAt(i)) === -1) {
			str = str.substring(i);
			break;
		}
	}

	l = str.length;
	for (i = l - 1; i >= 0; i--) {
		if (whitespace.indexOf(str.charAt(i)) === -1) {
			str = str.substring(0, i + 1);
			break;
		}
	}

	return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
}
