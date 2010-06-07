/* Create the activation window using ExtJS */
var showActivation = function(sUrl, loading, mwidth){

    // Create the container div
    createActivationPanel();

    dialog = new Ext.Window({
            el: 'activation-dlg',
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
    dialog.show();

    var info = document.getElementById('activation-page');
    info.innerHTML = loading;

    Ext.Ajax.request({
        url: sUrl,
        success: function(response) {
            info.innerHTML = response.responseText;
        },
        failure: function(response) {
            alert('Error. Couldn\'t create activation box.');
        },
        params: {
        }
    });
}

/*
    Create the container div's in which the info box will be created.
    Add the div's required by the ExtJS dialog box.
*/
var createActivationPanel = function() {

    if(document.getElementById('activation-panel')){
        destroyActivationPanel();
        p = document.getElementById('activation-panel');
        p.style.display = 'block';
    }else{
        p = document.getElementById('pageBody').appendChild(document.createElement('div'));
        p.id = 'activation-panel';
    }

    b = p.appendChild(document.createElement('div'));
    b.id = 'activation-dlg';
    b.innerHTML = '<div class="x-window-header">KnowledgeTree | Activate</div><div class="x-window-body"><div id="activation-page"></div></div>';
}

/*
    Set the container div to empty.
    The display must be set to none for IE, otherwise the icons under the div aren't clickable.
*/
var destroyActivationPanel = function() {
    if(dialog){
        dialog.destroy();
    }
    p = document.getElementById('activation-panel');
    p.innerHTML = '';
    p.style.display = 'none';
}