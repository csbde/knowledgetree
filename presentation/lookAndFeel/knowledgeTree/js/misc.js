/**
 * Displays an alert message and then redirects to the specified url
 */
function redirectLink(alertMessage, url) {
	alert(alertMessage);
	self.location.href=url;
}

/**
 * Disables an element by blurring as soon as it focuses
 */
function disable(elem) {
	elem.onfocus=elem.blur;
}

function setActionAndSubmit(newAction) {	
    document.MainForm.action = newAction;	
    document.MainForm.submit();
}

function setActionAndSubmitSearch(newAction) {	
	document.MainForm.fForStandardSearch.value = 'yes';
	document.MainForm.method = 'GET';
	document.MainForm.action = newAction;
	document.MainForm.submit();	
}

function isEmailAddr(email)
{
  var result = false;
  var theStr = new String(email);
  var index = theStr.indexOf("@");
  if (index > 0)
  {
    var pindex = theStr.indexOf(".",index);
    if ((pindex > index+1) && (theStr.length > pindex+1))
	result = true;
  }
  return result;
}

function isBlank(formField) {
	var result = false;
    if (formField){
	// trim formField.value
	formField.value = formField.value.replace(/\s+/g," ").replace(/^\s*|\s*$/g,"");
        switch(formField.type){
            case "select-one":
                if (formField.selectedIndex == 0 || formField.options[formField.selectedIndex].text == "" || formField.options[formField.selectedIndex].text == "None"){
                    result = true;
                }
                break;
            case "select-multiple":
                if (formField.selectedIndex == -1){
                    result = true;
                }
                break;
            case "hidden":                
            case "text":
            case "textarea":
                if (formField.value == "" || formField.value == null){
                    result = true;
                }
                break;
            default:
                if (formField.value == "" || formField.value == null){
                    result = true;
                }
        }
    } else {
		result = true;
    }
    return result;
}

function validRequired(formField,fieldLabel)
{
	var result = true;
    if (formField){
        switch(formField.type){
            case "select-one":
                if (formField.options[formField.selectedIndex].text == "" || formField.options[formField.selectedIndex].text == "None"){
                    result = false;
                }
                break;
            case "select-multiple":
                if (formField.selectedIndex == -1){
                    result = false;
                }
                break;
            case "text":
            case "textarea":
                if (formField.value == "" || formField.value == null){
                    result = false;
                }
                break;
            default:
                if (formField.value == "" || formField.value == null){
                    result = false;
                }
            }
    }
    
    if (!result) {
		if (fieldLabel == "selected") {
			alert('Please enter a value for the ' + fieldLabel +' field.');
		} else {
			alert('Please enter a value for the "' + fieldLabel + '" field.');
		}        
		formField.focus();
	}

	return result;
}

function allDigits(str)
{
	return inValidCharSet(str,"0123456789");
}

function inValidCharSet(str,charset)
{
	var result = true;

	// Note: doesn't use regular expressions to avoid early Mac browser bugs	
	for (var i=0;i<str.length;i++)
		if (charset.indexOf(str.substr(i,1))<0)
		{
			result = false;
			break;
		}
	
	return result;
}

function validEmail(formField,fieldLabel,required)
{
	var result = true;
	
	if (required && !validRequired(formField,fieldLabel))
		result = false;

	if (result && ((formField.value.length < 3) || !isEmailAddr(formField.value)) )
	{
		alert("Please enter a complete email address in the form: yourname@yourdomain.com");
		formField.focus();
		result = false;
	}
   
  return result;

}

function validNum(formField,fieldLabel,required)
{
	var result = true;

	if (required && !validRequired(formField,fieldLabel))
		result = false;
  
 	if (result)
 	{
 		if (!allDigits(formField.value))
 		{
 			alert('Please enter a number for the "' + fieldLabel +'" field.');
			formField.focus();		
			result = false;
		}
	} 
	
	return result;
}


function validInt(formField,fieldLabel,required)
{
	var result = true;

	if (required && !validRequired(formField,fieldLabel))
		result = false;
  
 	if (result)
 	{
 		var num = parseInt(formField.value,10);
 		if (isNaN(num))
 		{
 			alert('Please enter a number for the "' + fieldLabel +'" field.');
			formField.focus();		
			result = false;
		}
	} 
	
	return result;
}


function validDate(formField,fieldLabel,required)
{
	var result = true;

	if (required && !validRequired(formField,fieldLabel))
		result = false;
  
 	if (result)
 	{
 		var elems = formField.value.split("-");
 		
 		result = (elems.length == 3); // should be three components
 		
 		if (result)
 		{
 			var year = parseInt(elems[0],10);
  			var month = parseInt(elems[1],10);
 			var day = parseInt(elems[2],10);
			result = allDigits(elems[0]) && (month > 0) && (month < 13) &&
					 allDigits(elems[1]) && (day > 0) && (day < 32) &&
					 allDigits(elems[2]) && ((elems[2].length == 2) || (elems[2].length == 4));
 		}
 		
  		if (!result)
 		{
 			alert('Please enter a date in the format YYYY-MM-DD for the "' + fieldLabel +'" field.');
			formField.focus();		
		}
	} 
	
	return result;
}

/*function validateForm(theForm)
{
	// Customize these calls for your form

	// Start ------->
	if (!validRequired(theForm.fullname,"Name"))
		return false;

	if (!validEmail(theForm.email,"Email Address",true))
		return false;

	if (!validDate(theForm.available,"Date Available",true))
		return false;

	if (!validNum(theForm.yearsexperience,"Years Experience",true))
		return false;
	// <--------- End
	
	return true;
}*/
