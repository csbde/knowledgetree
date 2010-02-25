/*
*    Create the electronic signature dialog
*/
var showForm = function(){
    createForm();
    // create the window
    this.win = new Ext.Window({
        applyTo     : 'firstlogin',
        layout      : 'fit',
        width       : 800,
        height      : 500,
        closeAction :'destroy',
        y           : 75,
        shadow: false,
        modal: true
    });
    
    this.win.show();
}

var createForm = function() {
	var holder = "<div id='firstlogin'></div>"; 
	$("#wrapper").append(holder);// Append to current dashboard
	var address = "setup/firstlogin/index.php";
	getUrl(address, "firstlogin");
}

// Send request and update a div
var getUrl = function (address, div)  {
	$.ajax({
		url: address,
		dataType: "html",
		type: "POST",
		cache: false,
		success: function(data) {
			$("#"+div).empty();
			$("#"+div).append(data);
		}
	});
}

$(function() { // Document is ready
  
  showForm();
});
