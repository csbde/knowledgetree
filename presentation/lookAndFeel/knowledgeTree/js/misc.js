function validateString(field, msg, min, max) {
    if (!min) { min = 1 }
    if (!max) { max = 65535 }

    if (!field.value || field.value.length < min || field.value.max > max) {
        alert(msg);
        field.focus();
        field.select();
        return false;
    }
    return true;
}

function validateNumber(field, msg, min, max) {
    if (!min) { min = 0 }
    if (!max) { max = 255 }
    
    if ( (parseInt(field.value) != field.value) || field.value.length < min || field.value.length > max) {
        alert(msg);
        field.focus();
        field.select();
        return false;
    }
    return true;
}
    
function setActionAndSubmit(newAction) {
    document.MainForm.action = newAction;
    document.MainForm.submit();
}
