/*****************************************************************************
 * jQuery Finder v0.7 - Makes lists into a Finder, similar to Mac OS X
 * 
 * @date		$Date: 2009-02-27 13:53:16 +0200 (Fri, 27 Feb 2009) $
 * @revision 	$Rev: 11 $
 * @copy		(c) Copyright 2009 Nicolas Rudas. All Rights Reserved.
 * @licence		MIT Licensed
 * @discuss		groups.google.com/group/jquery-en/browse_thread/thread/480bb6f722b66345
 * @issues		code.google.com/p/jqueryfinder/issues/
 * @latest		code.google.com/p/jqueryfinder/source
 * @demo		nicolas.rudas.info/jquery/finder
 * 
 *****************************************************************************
 * Syntax:
 *		$(selector).finder()					Create a new finder with default options
 *
 *		$(selector).finder(options)				Create a new finder with additional options
 *
 *		$(selector).finder(method,[arguments])	Execute a method on an existing finder
 *												-	select			Select item
 *												-	refresh			Reload currently selected item (cache is ignored)
 *												-	destroy			Completely remove finder

 *												
 **/
;(function($){

$.fn.finder = function(o,m){
// Default options
	var defaults = {
		title : '',
		url : false,
		onInit : function(finderObj) {},
		onRootInit  : function(rootList,finderObj){},
		onRootReady  : function(newColumn,finderObj){},
		onItemSelect : function(listItem,eventTarget,finderObject){
			return false;
		},	
		onItemOpen : function(listItem,newColumn,self){},
		onFolderSelect : function(listItem,eventTarget,finderObject){},
		onFolderOpen : function(listItem,newColumn,self){},
		processData : function(responseText) {
			return $('<div class="ui-finder-content"/>').append(responseText);
		},
		animate : true,
		cache : false,
		ajax : { cache : false },
		listSelector : false,
		maxWidth : 450,
		classNames : {
			'ui-finder' : 'ui-widget ui-widget-header',
			'ui-finder-wrapper' : 'ui-widget-content',
			'ui-finder-header' : undefined,
			'ui-finder-title' : undefined,
			'ui-finder-list-item-active' : 'ui-state-default',
			'ui-finder-list-item-activeNow' : 'ui-state-hover',
			'ui-finder-list-item-file' : 'ui-icon-document',
			'ui-finder-list-item-folder' : 'ui-icon-folder-collapsed',
			'ui-finder-icon-folder-arrow' : 'ui-icon ui-icon-triangle-1-e'
			
		},
		toolbarActions : function() { return ''; }

	};
	
	
// Keep a reference to all finders created
	var Finders = $.Finders = $.Finders || {};
	
// Return a new timestamp
// Usually used for caching URLs, or creating unique identifiers e.g. Finders[ timestamp() ] = new Finder();
	var timestamp = function() {  return parseInt(new Date().valueOf(),10); };
	
// Check if scrollTo Plugin exists
	var scrollToPlugin = $.scrollTo || false;
	if(typeof scrollToPlugin == 'function') {
		scrollToPlugin = true;
		$.scrollTo.defaults.axis = 'xy';
		$.scrollTo.defaults.duration = 900;
	} 

// Set some variables (know what we are dealing with)
	var method, opts,
		url = (typeof m == 'string') ? m : null,
		func = (typeof m == 'function') ? m : null,
		_args = arguments;
		
	if(typeof o == 'string') { method = o; }
	else if (typeof o == 'object')  { opts = o; }
		
	if(opts) {
		if(opts.classNames) {
			opts.classNames = jQuery.extend(defaults.classNames, opts.classNames); }
		opts = jQuery.extend(defaults, opts);}
	else { opts = defaults;}
	
	/**
	 * Finder Constructor
	 * 
	 *
	 **/
	function Finder(element,finderId){
		var self = this;
		
		this.cache = {};
		
		this._queue = [];
		
		this.settings = {};
		
		this.id = finderId;
		
	// Reference to initial element - used when destroying Finder
		this.initial = $(element).clone(true);
		
	// Reference to element, used throughout
		this.element = $(element);
		this.element.attr('data-finder-ts',this.id);

	// make options internal properties
		for(var i in opts){
			self.settings[i] = opts[i];	}
				
		return this;
	};
	
	/**
	 * Initialise Finder
	 * Append necessary HTML, bind events etc
	 *
	 **/
	 Finder.prototype.init = function(){
		var self = this,
			toolbarActions = this.settings.toolbarActions.apply(this.element) || '',
			classes = this.settings.classNames,
			uiFinder = classes['ui-finder'] || '',
			uiFinderWrapper = classes['ui-finder-wrapper'] || '',
			uiFinderHeader = classes['ui-finder-header'] || '',
			uiFinderTitle = classes['ui-finder-title'] || '';
				
	// Wrap list to finder-wrapper
		this.element
			.wrap('<div class="ui-finder '+uiFinder+'"/>')
			.wrap('<div class="ui-finder-wrapper '+uiFinderWrapper+'"/>')
			.wrap('<div class="ui-finder-container"/>');
			
		this.wrapper = this.element.parents('.ui-finder-container');
		
		this.wrapper.parents('.ui-finder')
			.prepend('<div class="ui-finder-header '+uiFinderHeader+'">'+toolbarActions+'</div>')
			.prepend('<div class="ui-finder-title '+uiFinderTitle+'">'+this.settings.title+'</div>');
		
		this.widget = this.wrapper.parents('.ui-finder');
		
		this._toolbar = $('div.ui-finder-header',this.widget);
		
		this._title = $('div.ui-finder-title',this.widget);
		
	// Bind click events to wrapper so that only one event per finder is specified
	// Click event to handle showing of columns etc
		this.wrapper
			.unbind('click.FinderSelect')
			.bind('click.FinderSelect',function(e){
				var event_target = e.target,
					$event_target = $(event_target);
								
				if(!$event_target.closest('li.ui-finder-list-item').length
					&& !$event_target.is('> li.ui-finder-list-item').length
					|| $event_target.parents('.ui-finder-column').length === 0 ) {
						return;
				}
			
			// Otherwise 'register' this action in queue 
				self.queue($event_target);
								
			// And prevent any other browser actions
				return Boolean(self.lastSelectCallbackReturned);
			});
			
		setTimeout(function() {
			self.settings.onInit.apply(self.element,[self]);

			self.settings.listItemBorderColour = $('.ui-widget-header').css('backgroundColor');
		
		// Initialise root list
			self.selectItem('root');
		},0);
		
		return this;
	};
	
	Finder.prototype.title = function(val) {
		this._title.html(val);
		return this;
	};
	
	Finder.prototype.toolbar = function(val) {
		this._toolbar.html(val);
		return this;
	};
	
	/**
	 * Queue - Following a click event on a list item or anchor, the queue function is called
	 * It stores info about click events so that the script can handle click events
	 * on a first-come first-served basis.
	 *
	 * @param	noCache	- True when queue function called via 'refresh' API
	 *						i.e. caching is false when refreshing 
	 * @param	actionType	- Either 'select' or 'open', specified only if queue fn
	 *							called via API (ie. selector.finder('select', ... ))
	 **/
	Finder.prototype.queue = function(target,noCache,actionType /* select or open */){
		var	self = this,
			wrapper = this.wrapper;
				
		this._queue.push( [target,noCache,actionType] );
		
	// isProcessing is set to true when the Finder is currently 'doing stuff'
	// and set to false when not. So, if its not doing anything right now,
	// continue to process this event
		if(!self.isProcessing) { self.preSelect(); }
		
		
		return this;
	};
	
	/**
	 * preSelect - Simple function to determine which item to select
	 * based on the current queue => Always first item in queue
	 * (first-come, first-served)
	 **/
	Finder.prototype.preSelect = function(){
		var self = this,
			q = this._queue;
		
		if(q.length==0) { return;}
		
		self.select.apply(self,q[0]);
		
		return this;
	};
	
	/**
	 * Select - Considering the target of a click event, this function determines
	 * what to do next by taking into consideration if target was anchor, or list item,
	 * and if target was a file or a folder.
	 * 
	 * Note:	- Cannot select an item which is not in page (i.e. in sublevels)
	 *			- When selecting item via API, not selecting levels properly
	 **/
	Finder.prototype.select = function(target,noCache,actionType) {
		var self = this,
			wrapper = this.wrapper,
			targetElement = (typeof target == 'string')
								? $('a[rel="'+target+'"]',wrapper) :  $(target),
			eventTarget = targetElement;

		if(typeof target.length != 'number') {
			throw 'jQuery Finder: Target must be either a URL or a jQuery/DOM element'; return this; }
			
		if(!targetElement[0]) {
			throw 'jQuery Finder: Target element does not exist'; return this; }
		
		this.isProcessing = true;
				
		var targetList = targetElement.closest('li.ui-finder-list-item'),
			targetA = $('> a:first',targetList),
			targetContainer =  targetList.parents('div[data-finder-list-level]:first'),
			targetLevel = targetContainer.attr('data-finder-list-level'),
			type = (targetList.hasClass('ui-finder-file')) ? 'file' : 'folder',
			url = targetA.attr('rel'),
			wrapperLists = $('div.ui-finder-column:visible',wrapper),
			classes = this.settings.classNames,
			classesActive = classes['ui-finder-list-item-active'] || '',
			classesActiveNow = classes['ui-finder-list-item-activeNow'] || '';
				
	// Fix event's target element. Only list and anchor elements make sense
		targetElement = (targetElement[0] !== targetList[0] && targetElement[0] !==  targetA[0])
							? targetList
							: targetElement;
				
	// If select was triggered via API and target was a URL (e.g. finder('select',url))
	// then target is considered to be the list item so as to select item and not open it.
	// This allows user to select an item by providing the URL of an anchor element
	// which would otherwise open the item
		if(actionType == 'select' /*&& typeof target == 'string' *//*&& type == 'file'*/) {
			eventTarget = targetElement = targetList;	}		
		
	// Currently selected item will no longer be active
		$('.ui-finder-list-item.ui-finder-list-item-activeNow',wrapper)
			.removeClass('ui-finder-list-item-activeNow ' + classesActiveNow  );
	
	// Remove visible lists which should not be visible anymore
		wrapperLists.each(function(){
			var finderListWrapper = $(this),
				finderListLevel = finderListWrapper.attr('data-finder-list-level');
		
			if( finderListLevel >= targetLevel ) {				
				$('.ui-finder-list-item.ui-finder-list-item-active',finderListWrapper)
					.removeClass('ui-finder-list-item-active ' + classesActive ); 	}
			
			if( finderListLevel > targetLevel ) {
				finderListWrapper.remove();	}
		});
				
	// Style selected list item
		// active refers to all previously selected list items
		// activeNow refers to the currently active list item
		targetList
			.addClass('ui-finder-list-item-active ' + classesActive)
			.addClass('ui-finder-list-item-activeNow ' + classesActiveNow);
	
	// Scroll to selected item
	// Mostly useful if item not selected following direct user action (e.g. click event) 
		if(scrollToPlugin){
			setTimeout(function() {
				targetContainer.scrollTo(targetList);	}, 0); }
	
	// Call onSelectItem or onSelectFolder callbacks
	// If callback does not return false,
	// proceed to display item/folder in new column
		var selectCallback, callbackArgs = [targetList,eventTarget,self];
		
		if (type == 'file') {
			selectCallback = self.settings.onItemSelect.apply(self.element,callbackArgs); }
		else {
			selectCallback = self.settings.onFolderSelect.apply(self.element,callbackArgs); }		
		
		this.lastSelectCallbackReturned = selectCallback;
	
	// If callback returns false, no new column is added
	// If callback returns true, default browser action is taken (i.e. url followed)
		if( selectCallback !== false && selectCallback !== true) {
		// Notify user of loading action	
			targetList.addClass('ui-finder-loading');
			
		// Select item	
			self.selectItem(url,noCache,[targetElement,targetList]);
		
			return this; }
				
	// Script will only reach this point when select callbacks return false	or true
				
	// Adjust the width of the current columns
	// true param needed so that adjustWidth knows that
	// there are no new columns being added	
		this.adjustWidth(true);

	// Finalise process (move on with queue etc)
		this.finalise();
	
		return this;
	};
	
	/**
	 * Select Item	
	 * 
	 * This function retrieves the data to be shown to the user after a click event
	 * Finder will only reach this point when select callbacks do not return false
	 **/

	Finder.prototype.selectItem = function(url,noCache,targets){
		var self = this,
			settings = self.settings,
			target = (targets) ? targets[0] : null,
			listItem = (targets) ? targets[1] : null,
			type = (listItem) ? listItem[0].className.match(/(file|folder)/)[0] : 'folder',
			data = (url == 'root')
						? (settings.url) ? null : this.element
						: $('> ul, > ol, > div',listItem).eq(0).clone(),
			url = (url == 'root' && typeof settings.url === 'string') ? settings.url : url;
						
	// Process data before appending new column		
		var proceed = function(){
			var processData = settings.processData,
				tmp_data = data;
				
			if($.isFunction(processData)) {
				data = processData(data);
				if(!data.length) {	data = tmp_data;} }
			else {
				data = $(data); }
		
		// Store data in cache
			self.cache[url] = {
				'url':url, 'data' : data, 'response': tmp_data,
				'date': new Date().valueOf(), 'status' : 'success'	};			
				
		// If just loaded root, call on root init callback	
			if(url == settings.url && typeof settings.onRootInit === 'function') {
				settings.onRootInit.apply(self.element,[data,self]); }
			
			if(type == 'folder') {
			// Process data. Find list items and add necessary classes and icons
				$('> ul, > ol',data).eq(0).find('> li').each(function(){
					var $this = $(this),
						thisType,thisTypeClass;
				
				// Get the type of this list item (file or folder)
					if($this.hasClass('ui-finder-folder')) {
						thisType = 'folder'; }
					else if ($this.hasClass('ui-finder-file')) {
						thisType = 'file'; }
						
				// If type (file or folder) is not specified try to figure it out
					else {
						if($this.children('ul,ol').length) {
							$this.addClass('ui-finder-folder');
							thisType = 'folder'; }
						else { // default type is file
							$this.addClass('ui-finder-file');
							thisType = 'file';  }
					}
					
				// Set class for icon
					thisTypeClass = (thisType == 'file')
									? settings.classNames['ui-finder-list-item-file']
									: settings.classNames['ui-finder-list-item-folder'];
				
					$this
						.addClass('ui-finder-list-item')
						.css('borderColor',settings.listItemBorderColour)
						.append('<span class="'+settings.classNames['ui-finder-icon-folder-arrow']+'\
									ui-finder-icon ui-finder-icon-arrow"/>');
				
				// Remove links
					var anch = $('> a',this),
						anchHref = anch.attr('href') || '_blank'+ new Date().valueOf(),
						anchTitle = anch.attr('title') || '';
				
					if(anch.attr('rel') == anchHref.substring(1) || !anchHref.length) {	return;}
				
					anch
						.attr('rel',anchHref)
						.attr('href',anchHref)
						.append('<span class="ui-icon '+thisTypeClass+' ui-finder-icon ui-finder-icon-'+thisType+'"/>');

					if(anchTitle.length == 0) {anch.attr('title',anchHref);}	
				});
			}			
			
		// Append the new data
			self.appendNewColumn(url,data,[target,listItem],type);		
		};

	// Folder contents exist and no refresh
		if(data && data.length && !noCache) { proceed(); }
		
	// Folder content exist, and refresh, but no URL
		else if(data && data.length && noCache && url.match(/_blank\d+/)) { proceed(); }
	
	// Caching and data somewhere in cache
		else if( typeof this.cache[url] == 'object' && this.settings.cache && !noCache) {
			if(this.cache[url].status == 'success' ) {
				data = this.cache[url].data;
				proceed();
			}
		}
	// No data yet, so retrieve from URL	
		else if(!url.match(/_blank\d+/)) {
			$.ajax({
				url : url, cache : self.settings.ajax.cache,
				success: function(response){
					data = response;
				},
				error: function(xhr,response){
					data = response;
				},
				complete : function(){
					proceed();
				}
			});
		}

		return this;
	};	

	/***
 	 * Append new Column - Function to append a new column to finder
	 * called from selectItem function
	 * 
	 * Triggers Callback functions for OpenItem or OpenFolder !
	 ***/	
	Finder.prototype.appendNewColumn = function(url,data,targets,type){
		var self = this,
			target = (targets) ? targets[0] : null,
			listItem = (targets) ? targets[1] : null,
			targetParent = (listItem)
								? listItem.parents('div[data-finder-list-level]:first') : null,
			columnId = url.replace(/[\W\s]*/g,''),
			columnLevel = (function(){
				if (url == self.settings.url || url == 'root') { return 0; }
				return parseInt(targetParent.attr('data-finder-list-level'),10) + 1;
			})();
			
	// If column already exists, remove it
		var newColumn = $('div[data-finder-list-id="'+columnId+'"]');
		if(newColumn.length > 0) { newColumn[0].parentNode.removeChild(newColumn[0]); }
		
	// Specify new column, and add necessary attributes
		newColumn = $('<div class="ui-finder-column ui-widget-content ui-finder-new-col"/>')
		// Avoid showing the column when it's not yet ready
		// Also, setting display to none makes DOM manipulation a bit faster
			.css('display','none')
			.attr('data-finder-list-id',columnId)
			.attr('data-finder-list-source',url)
			.attr('data-finder-list-level',columnLevel)
			.css('z-index',0);	// Keep beneath other columns
		
	// Append new column
	// Plain DOM scripting used as opposed to jQuery as it's faster
		self.wrapper[0].appendChild(newColumn[0]);
		
		newColumn[0].appendChild($(data)[0]);
				
	// Adjust the width of the Finder
	// but make sure that column is appended & parsed (timeout = 0)		
		setTimeout(function(){
			self.adjustWidth(false,newColumn,url);},0);

	// Call onOpenItem or onOpenFolder callback if target was anchor
	// Note: target check necessary, root list has no target
		if(target && target[0] && target.is('a')) {
			var callbackArgs = [listItem,newColumn,self];
			if(type == 'file') { self.settings.onItemOpen.apply(self.element,callbackArgs); }
			else { self.settings.onFolderOpen.apply(self.element,callbackArgs); }
		}
		
		return this;
	};
	
	/***
 	 * Adjust Width - Adjust the width of the columns and the wrapper element
	 * param ignoreNew is true when select callbacks return false
	 ***/	
	Finder.prototype.adjustWidth = function(ignoreNew,newColumn,url){
		var self = this,
			wrapper = this.wrapper;
		
		newColumn = newColumn || $('div[data-finder-list-id]:visible:last',wrapper);
				
	// Get all siblings of the new column
	// i.e those visible and not the last, as new column is always last 	
		var columns = (!ignoreNew)
						? wrapper.children('div[data-finder-list-id]:not(.ui-finder-new-col):visible')
						: wrapper.children('div[data-finder-list-id]:visible:not(:last)'),
			width = 0;
		
		newColumn.removeClass('ui-finder-new-col');	
		
	// Prevent previous columns from taking up all the space (width)
		columns.css('right','auto');

	// Calculate the space taken by the visible columns
	// The total width  of these columns will be set as 
	// the left property of the new column (so that each column appears next to each other)
		columns.each(function() {
			$(this)
				.width('auto')					
			// Explicitly setting the width of the column fixes some issues in IE.
			// The 20px padding is needed for Webkit browsers (don't know why)
				.width( $(this).outerWidth() + 20 );
				
			width += $(this).width();});
	
	// Account for Y axis scrollbar	(only if it wasn't accounted before)
	// The value specified will be added to the new column's width
		var yScroll = 10,// ($.browser.msie && $.browser.version < 8) ? 10 : 5,
			accountYScroll = ( !newColumn.data('yscroll') ) ? yScroll : 0;
		
	// Need to know the width of the new column (newColumnWidth),
	// the total width of all columns (newWidth),
	// the current width of the wrapper element (currentWidth),
	// and the available width (specified in wrapper's parent)
		var _tmpNewColumnWidth = newColumn.width(),
			newColumnWidth = (self.settings.maxWidth && _tmpNewColumnWidth > self.settings.maxWidth)
								? self.settings.maxWidth : _tmpNewColumnWidth + accountYScroll,
			newWidth =  width + newColumnWidth,
			currentWidth = wrapper.width(),
			availableWidth = wrapper.parent().width();
	
	// Mark column as y scrollbar fixed
		newColumn.data('yscroll',true);
		
	// Adjust the width of the wrapper element. As columns as absolutely positioned
	// no horizontal scrollbars appear if the total width of the columns exceeds the space available.
	// By setting the width of the wrapper element to that of the columns, a horizontal scrollbar appears.
		if ( newWidth > availableWidth || newWidth < currentWidth
			&& currentWidth > availableWidth && newWidth != currentWidth) {
			
		// If going from multiple levels down (ie. many columns) to a higher level
		// (ie. to few columns) the new width will be less than available.
		// Also if theres only one column visible (ie. root) newWidth will equal newColumnWidth.
		// In these cases make sure Finder takes up all available space.			
			if(newColumnWidth == newWidth || newWidth < availableWidth) { newWidth = 'auto'; }
			
		// Account for Y axis scrollbar 
		// This adds the necessary width when moving backwards
			accountYScroll = (  newWidth != 'auto' && newWidth != currentWidth ) ? yScroll : '';
			
		// Set width to new
			wrapper.width( newWidth + accountYScroll);
		}
		
	// As the column is absolutely positioned, its left property
	// must be specified	
		newColumn.css('left',width);
	
	// Make the new column take up all available space
	// this must be set AFTER new column's width has been retrieved
	// otherwise the value is not true
		newColumn.css('right',0);
			
	// By setting the z-index of the new column to '2'
	// it prevents subsequent columns from being above it
	// whilst their css properties (left & right) are being set.
	// For this to be effective columns must have a background specified
	// (CSS class: .ui-finder-column)
		newColumn.css('z-index',2);
		
	// Set display to block so that we can scroll to the new column
	// Set visibility to hidden to avoid flicker if animation is required
		newColumn.css({'display':'block','visibility':'hidden'});
		
	// Scroll to new column
		if(newColumn && scrollToPlugin){
			this.wrapper.parent().scrollTo(newColumn); }
		
	// ignoreNew exists when select callbacks return FALSE
	// i.e. no new column was appended, but width of existing columns
	// and wrapper still need fixing
		if(!ignoreNew && this.settings.animate) { 
		// Animate column if desired
			var duration = (!isNaN(this.settings.animate)) ? this.settings.animate : 500; 
		// To animate the column we cannot use its width value (its 0)
		// but we can use its left property to calculate the width it currently occupies.
	 	// Pixels from left - total pixels = pixels available for the column (i.e. width)
			var fromLeft = newColumn.css('left').replace(/\D/g,''),
				fromRight = wrapper.width() - fromLeft;

		// So by setting the column's right property to the calculated value
		// and keeping its left property, the column becomes insivible
		// The animation then decreases the right property gradually to zero
		// to make the column visible
			newColumn
				.css('overflow-y','hidden') // avoid showing a scroll bar whilst animating
				.css('right',fromRight)
				.css('visibility','visible')
				.animate({'right':0 },{
					duration:duration,
					complete:function(){
						newColumn.css('overflow-y','scroll');
						self.finalise(newColumn,url); }
				});
		}
		// No animation, just show the new column
		else {
			newColumn.css('visibility','visible');
			self.finalise(newColumn,url); }

		return this;
	};
	
	Finder.prototype.finalise = function(newColumn,url){
	// Remove any loading classes	
		$('div.ui-finder-column .ui-finder-list-item.ui-finder-loading',this.wrapper)
			.removeClass('ui-finder-loading');
		
	// Specify that script is done processing (used in queing) 
		this.isProcessing = false;
				
	// Remove last item from queue
	// and if there are more items, move on
		this._queue.shift();
		if(this._queue.length > 0) {
			this.preSelect(); }
	
	// If just loaded root, call on root ready callback	
		if(url == this.settings.url && typeof this.settings.onRootReady === 'function') {
			this.settings.onRootReady.apply(this.element,[newColumn,this]); }	
		
		return this;
	};
	
	Finder.prototype.destroy = function(){
	// Unbind events	
		this.wrapper
			.unbind('click.FinderSelect');
	
	// Remove Finder's HTML, append initial element
		this.element.parents('.ui-finder').replaceWith(this.initial);
	
	// Delete reference to Finder
		delete Finders[this.id];
		
		return this;
	};
	
	Finder.prototype.current = function(){
		var current = $('.ui-state-hover',this.wrapper).find('a:first');
		return (current.length>0) ? current : null;
	};

	Finder.prototype.refresh = function(){
		var current = this.current();
		
		if(current) {	this.queue(current,true);	}
		else {	this.selectItem('root',true);	}
		
		return this;
	};
	
	var _finder = Finders[ $(this).eq(0).attr('data-finder-ts') ];
	
	if(method == 'current' && _finder) { return _finder.current();	}
	else if(method == 'get' && _finder) { return _finder; }
	
	return this.each(function(){
		var finderId = $(this).attr('data-finder-ts') || null,
			timeStamp = new Date().valueOf();
			
	// If name of method provided
	// execute method
		if(finderId && method) {
			var finder = Finders[finderId];
			
		// Access private methods	
			if(method == 'select' && m) {
				if(m.constructor == Array) {
					m = m.reverse();
					for (var i = m.length - 1; i >= 0; i--){
						finder.queue(m[i],false,method); }
				}
				else { finder.queue(m,false,method); }
			}
			else if(method == 'title') {	finder.title(m);	}
			else if(method == 'toolbar') {	finder.toolbar(m);	}
			else if(method == 'destroy') {	finder.destroy();	}
			else if(method == 'refresh') {	finder.refresh();	}
		}
	// If no method provided new finder is created
		else if (!method) {	Finders[timeStamp] = new Finder(this,timeStamp).init();	} 
		else if (!finderId && method) {	throw 'jQuery Finder: Element is not a finder';	}
	});
};})(jQuery);