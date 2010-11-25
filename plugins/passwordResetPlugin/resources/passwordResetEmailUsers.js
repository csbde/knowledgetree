/* Hide the password request box and display the login form */
var hideBox = function(){
    var box = document.getElementById('forgot_box');
    var formbox = document.getElementById('login_form');

    formbox.style.display = 'block';
    formbox.style.visibility = 'visible';
    box.style.display = 'none';
    box.style.visibility = 'hidden';

    document.getElementById('username').focus();
}

/* Hide the login form and display the password request box */
var showBox = function(){
    var box = document.getElementById('forgot_box');
    var formbox = document.getElementById('login_form');

    box.style.display = 'block';
    box.style.visibility = 'visible';
    formbox.style.display = 'none';
    formbox.style.visibility = 'hidden';

    document.getElementById('reset_username').focus();
}

/* Hide the login form and display the reset password box */
var showResetBox = function(){
    var box = document.getElementById('reset_box');
    var formbox = document.getElementById('login_form');

    box.style.display = 'block';
    box.style.visibility = 'visible';
    formbox.style.display = 'none';
    formbox.style.visibility = 'hidden';

    document.getElementById('new_password').focus();
}

/* Hide the reset password box and display the login form */
var hideResetBox = function(){
    var box = document.getElementById('reset_box');
    var formbox = document.getElementById('login_form');

    formbox.style.display = 'block';
    formbox.style.visibility = 'visible';
    box.style.display = 'none';
    box.style.visibility = 'hidden';

    document.getElementById('username').focus();
}

/* Display the error / success messages in the correct format */
var showMessages = function() {
    var box = document.getElementById('messages');

    box.style.display = 'block';
    box.style.visibility = 'visible';
}

/* Check the entered details and use ajax to send the email confirming the users request
on success display the response from the server */
var sendEmailRequest = function(sUrl) {
    // Check the username and password has been supplied
    var user = document.getElementById('reset_username');
    var email = document.getElementById('reset_email');

    if(!user.value){
        alert('Please enter a username.');
        user.focus();
        return false;
    }
    if(!email.value){
        alert('Please enter a valid email address.');
        email.focus();
        return false;
    }

    Ext.Ajax.request({
        url: sUrl,
        success: function(response) {
            //hideBox();
            showMessages();
            document.getElementById('messages').innerHTML = response.responseText;
        },
        failure: function(response) {
            showMessages();
            document.getElementById('messages').innerHTML = 'A server error occurred, please refresh and try again.';
        },
        params: {
            username: user.value,
            email: email.value
        }
    });
}

/* Check the entered details and use ajax to reset the users password
on success display the response from the server */
var resetPassword = function(sUrl, redirect) {
	
    // Check the fields have been filled in
    var email = document.getElementById('new_email');
    var password = document.getElementById('new_password');

    if(!email.value){
        alert('Please enter a valid email address.');
        email.focus();
        return false;
    }
    if(!password.value){
        alert('Please enter new password.');
        password.focus();
        return false;
    }
    
	Ext.Ajax.request({
        url: sUrl,
        success: function(response) {
        	window.location.href = redirect;
        },
        failure: function(response) {
            showMessages();
            document.getElementById('messages').innerHTML = 'A server error occurred, please refresh and try again.';
        },
        params: {
            email: email.value,
            password: password.value,
        }
    });
}