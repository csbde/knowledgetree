/*
    Create the preview / info box using an ExtJS Dialog window
*/
var showInfo = function(iDocId, sUrl, sDir, loading, mwidth){

    // Create the info box container div
    createPanel();

    showIcon = Ext.get('box_'+iDocId);
    dialog = new Ext.Window({
            el: 'info-dlg',
            closeAction: 'destroy',
            layout: 'fit',
            shadow: false,
            modal: true,
            plain: false,
            width: mwidth,
            height: 360,
            minWidth: 300,
            minHeight: 250
    });
    dialog.show(showIcon.dom);

    var info = document.getElementById('info-preview');
    info.innerHTML = loading;

    Ext.Ajax.request({
        url: sUrl,
        success: function(response) {
            info.innerHTML = response.responseText;
        },
        failure: function(response) {
            alert('Error. Couldn\'t create info box.');
        },
        params: {
            fDocumentId: iDocId,
            kt_dir: sDir
        }
    });
}

/*
    Create the container div's in which the info box will be created.
    Add the div's required by the ExtJS dialog box.
*/
var createPanel = function() {

    if(document.getElementById('info-panel')){
        destroyPanel();
        p = document.getElementById('info-panel');
        p.style.display = 'block';
    }else{
        p = document.getElementById('pageBody').appendChild(document.createElement('div'));
        p.id = 'info-panel';
    }

    b = p.appendChild(document.createElement('div'));
    b.id = 'info-dlg';
    b.innerHTML = '<div class="x-window-header">Info Panel</div><div class="x-window-body"><div id="info-preview"></div></div>';
}

/*
    Set the container div to empty.
    The display must be set to none for IE, otherwise the icons under the div aren't clickable.
*/
var destroyPanel = function() {
    if(dialog){
        dialog.destroy();
    }
    p = document.getElementById('info-panel');
    p.innerHTML = '';
    p.style.display = 'none';
}