var win;
var head;
var request;
var request_type;
var request_details;

/*
*    Create the electronic signature dialog
*/
var showSignatureForm = function(head, action, type, request, request_type, details){
    createSignature();

    var sUrl = rootURL + '/plugins/ktstandard/KTElectronicSignatures.php';

    if(details === undefined) details = '';
    if(request_type === undefined) request_type = 'submit';
    if(type === undefined) type = 'system';

    this.head = head;
    this.request = request;
    this.request_type = request_type;
    this.request_details = new Array();
    this.request_details[0] = action;
    this.request_details[1] = type;
    this.request_details[2] = details;

    // create the window
    this.win = new Ext.Window({
        applyTo     : 'signature',
        layout      : 'fit',
        width       : 360,
        height      : 265,
        closeAction :'destroy',
        y           : 150,
        shadow: false,
        modal: true
    });
    this.win.show();

    var sUrl = rootURL + '/plugins/ktstandard/KTElectronicSignatures.php';
    var info = document.getElementById('sign_here');

    Ext.Ajax.request({
        url: sUrl,
        success: function(response) {
            if(response.responseText == 'disabled'){
                // continue the action
                if(this.request_type == 'redirect'){
                    window.location.href = this.request;
                }else{
                    window.document.forms[this.request].submit();
                }
            }
            info.innerHTML = response.responseText;
        },
        failure: function(response) {
            alert('Error. Couldn\'t create signature form.');
        },
        params: {
            head: head
        }
    });
}

/*
* Create the html required to initialise the signature panel
*/
var createSignature = function() {

    if(document.getElementById('signature-panel')){
        p = document.getElementById('signature-panel');
    }else {
        p = document.getElementById('pageBody').appendChild(document.createElement('div'));
        p.id = 'signature-panel';
    }

    inner = '<div id="signature" class="x-hidden"><div class="x-window-header">Electronic Signature</div><div class="x-window-body">';
    inner = inner + '<div id="sign_here>Loading...</div></div></div>';
    p.innerHTML = inner;
}

/*
* Close the popup
*/
var panel_close = function() {
    this.win.destroy();
}

/*
* Submit the authentication form
*/
var submitForm = function() {

    var sUrl = rootURL + '/plugins/ktstandard/KTElectronicSignatures.php';
    var info = document.getElementById('sign_here');
    var user = document.getElementById('sign_username').value;
    var pwd = document.getElementById('sign_password').value;
    var comment = document.getElementById('sign_comment').value;

    Ext.Ajax.request({
        url: sUrl,
        success: function(response) {
            if(response.responseText == 'success'){
                // continue the action
                if(this.request_type == 'redirect'){
                    window.location.href = this.request;
                }else{
                    window.document.forms[this.request].submit();
                }
            }

            info.innerHTML = response.responseText;
        },
        failure: function(response) {
            alert('Error. Couldn\'t create signature form.');
        },
        params: {
            head: this.head,
            action: 'submit',
            sign_username: user,
            sign_password: pwd,
            sign_comment: comment,
            sign_action: this.request_details[0],
            sign_type: this.request_details[1],
            sign_details: this.request_details[2]
        }
    });
}