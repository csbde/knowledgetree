Ext.onReady(function(){

Ext.BLANK_IMAGE_URL = 'thirdpartyjs/extjs/resources/images/default/s.gif';

var bSearchOptionMetadataAndContent = true;

function doAdvancedSearch()
{
	document.location=rootURL + "/search2.php?action=guiBuilder";
}

function doViewPreviousSearchResults()
{
	document.location=rootURL + "/search2.php?action=searchResults";
}

function onMetadataAndContentClick()
{
	bSearchOptionMetadataAndContent = true;
	//Ext.example.msg(sSearchTranslations[0], sSearchTranslations[1]); /* Quick Search Options, Searches will now search both content and metadata   */
}

function onMetadataClick()
{
	bSearchOptionMetadataAndContent = false;
	//Ext.example.msg(sSearchTranslations[0], sSearchTranslations[2]); /* Quick Search Options, Searches will now only search metadata */
}

function onSearchEngineFormatClick()
{
    bResultsFormatSearchEngine = true;
    document.location=rootURL + "/search2.php?action=searchResults&format=searchengine";
}

function onBrowseFormatClick()
{
    bResultsFormatSearchEngine = false;
    document.location=rootURL + "/search2.php?action=searchResults&format=browseview";
}

function onSavedSearchClick(item)
{
	id = item.id.substr(11);
	document.location=rootURL + "/search2.php?action=processSaved&fSavedSearchId=" + id;
}

function onSearchClick(sender)
{

	var suffix = sender.id.substring(12);

	var text = Ext.get('txtSearchBar' + suffix).getValue(false).trim();

	if (text == sSearchTranslations[12] || text == '')
	{
	    Ext.getCmp('txtSearchBar' + suffix).setValue('');
//		Ext.example.msg(sSearchTranslations[3], sSearchTranslations[4]); /* Hint, Please enter some search criteria!  */
		return;
	}

	var sq = document.getElementById('txtQuery');
	var qg = document.getElementById('cbQuickGeneral');

	text = text.replace(/\"/g, "'");

	if (bSearchOptionMetadataAndContent)
	{
		sq.value = '(GeneralText contains "' + text + '")';
	}
	else
	{
		sq.value = '(Metadata contains "' + text + '")';
	}
	qg.value = bSearchOptionMetadataAndContent?1:0;

	var frm = document.getElementById('frmQuickSearch');
	frm.submit();

}

function populateSavedSearch(menu)
{
	if (aSavedSearches.length == 0)
	{
		return;
	}
	var item = menu.addMenuItem({
		text: sSearchTranslations[5], /*Saved Searches*/
		menu:	{
		    shadow: false,
		    minWidth: '150px',
			items: []
		}
	});

	for(i = 0; i < aSavedSearches.length; i++)
	{
		var search = aSavedSearches[i];
		var name = search.name;

		item.menu.addMenuItem({
								text: name,
								id: 'miSavedItem' + search.id,
								handler: onSavedSearchClick
		});
	}
}

function createSearchBar(div, suffix)
{
	var x = Ext.get(div);
	if (x == null)
	{
		return;
	}

	var button;

	if (suffix == 1)
	{
		var menu = new Ext.menu.Menu({
		    shadow: false,
		    minWidth: '200px',
			items: [
				{
					text: sSearchTranslations[6], /* Advanced Search */
					handler: doAdvancedSearch
				},
				{
					text: sSearchTranslations[7], /* Previous Search Results */
					handler: doViewPreviousSearchResults
				},
				{
					text: sSearchTranslations[8] , /*Quick Search Options*/
					menu: {
					    shadow: false,
					    minWidth: '150px',
						items: [
							new Ext.menu.CheckItem({
								text: sSearchTranslations[9], /* content and metadata */
								id: 'cbSearchOptionContentMetadata' + suffix,
								checked: bSearchOptionMetadataAndContent,
								group: 'options',
								handler: onMetadataAndContentClick
							}),
							new Ext.menu.CheckItem({
								text: sSearchTranslations[10], /* metadata */
								checked: !bSearchOptionMetadataAndContent,
								id: 'cbSearchOptionMetadata' +  suffix,
								group: 'options',
								handler: onMetadataClick
							})
						]
					}
				},
				{
					text: sSearchTranslations[13] , /*Toggle results format*/
					menu: {
					    shadow: false,
					    minWidth: '150px',
						items: [
							new Ext.menu.CheckItem({
								text: sSearchTranslations[14], /* search engine format */
								id: 'cbResultsFormatSearchEngine' + suffix,
								checked: bResultsFormatSearchEngine,
								group: 'format',
								handler: onSearchEngineFormatClick
							}),
							new Ext.menu.CheckItem({
								text: sSearchTranslations[15], /* browse view format */
								id: 'cbBrowseSearchEngine' +  suffix,
								checked: !bResultsFormatSearchEngine,
								group: 'format',
								handler: onBrowseFormatClick
							})
						]
					}
				}
			]
		});

		button = new Ext.Toolbar.MenuButton({
			text: sSearchTranslations[11], /* search */
			handler: onSearchClick,
			id: 'searchButton' + suffix,
			//cls: 'x-btn-text-icon blist',
			menu : menu
		});

		populateSavedSearch(menu);

	}
	else
	{
		menu = null;
		 button = new Ext.Toolbar.Button({
			text: sSearchTranslations[11], /* search */
			pressed: true,
			handler: onSearchClick,
			id: 'searchButton' + suffix
			//cls: 'x-btn-text-icon blist',

		});
	}

	var tb = new Ext.Toolbar(div);

	tb.add(
	new Ext.form.TextField({
			emptyText: sSearchTranslations[12], /* Enter search criteria... */
			value: quickQuery,
			selectOnFocus:true,
			id:'txtSearchBar' + suffix,
			width: (suffix == 1) ? 180 : 110
		}),
		button);

	var map = new Ext.KeyMap("txtSearchBar" + suffix,
				{
					key: Ext.EventObject.ENTER,
					fn: function() {
						onSearchClick(Ext.get('txtSearchBar' + suffix));
					}
				});

	var el = Ext.get(div);
	if (suffix == 1)
	{
		el.applyStyles('position:relative; margin-right: 15px');
	}
	else
	{
		el.applyStyles('position:relative; left: 20px; top: 0px;');
	}

	return menu;
}

/* create the top search widget */
var menu = createSearchBar('newSearchQuery',1);

/* create the search portlet if possible */
createSearchBar('searchPortletCriteria',2);

});