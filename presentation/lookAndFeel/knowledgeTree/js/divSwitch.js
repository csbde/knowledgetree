// function switchDiv()
//  this function takes the id of a div
//  and calls the other functions required
//  to show that div
//
function switchDiv(div_id, object)
{
  var aDocumentDivs = new Array("documentData", "genericMetaData", "typeSpecificMetaData", 
                                "archiveSettings", "documentRouting", "linkedDocuments");
  var aFolderDivs = new Array("folderData", "folderRouting", "documentTypes", "folderPermissions");
  var aSearchDivs = new Array("searchLess", "searchMore");
                                
  var style_sheet = getStyleObject(div_id);
  var aDivs;
  if (style_sheet)
  {
    if (object == "document") {
    	aDivs = aDocumentDivs;
    }
    if (object == "folder") {
    	aDivs = aFolderDivs;
    }
    if (object == "search") {
    	aDivs = aSearchDivs;
    }
    showAll(aDivs);    
    hideAll(aDivs);
    changeObjectVisibility(div_id,"visible");
  }
  else 
  {
    alert("sorry, this only works in browsers that do Dynamic HTML (" + div_id + ")");
  }
}

// function hideAll()
//  hides a bunch of divs

function hideAll(aDivs)
{
   for (var i=0; i<aDivs.length; i++) {
     changeObjectVisibility(aDivs[i], "hidden");
   }
}
// function showAll()
//  shows a bunch of divs
//
function showAll(aDivs)
{
   for (var i=0; i<aDivs.length; i++) {
     changeObjectVisibility(aDivs[i], "visible");
   }
}

// function getStyleObject(string) -> returns style object
//  given a string containing the id of an object
//  the function returns the stylesheet of that object
//  or false if it can't find a stylesheet.  Handles
//  cross-browser compatibility issues.
//
function getStyleObject(objectId) {
  // checkW3C DOM, then MSIE 4, then NN 4.
  //
  if(document.getElementById && document.getElementById(objectId)) {
        return document.getElementById(objectId).style;
   }
   else if (document.all && document.all(objectId)) {  
        return document.all(objectId).style;
   } 
   else if (document.layers && document.layers[objectId]) { 
        return document.layers[objectId];
   } else {
        return false;
   }
}

function changeObjectVisibility(objectId, newVisibility) {
    // first get a reference to the cross-browser style object 
    // and make sure the object exists
    var styleObject = getStyleObject(objectId);
    if(styleObject) {
        styleObject.visibility = newVisibility;
        return true;
    } else {
        // we couldn't find the object, so we can't change its visibility
        return false;
    }
}

// Copyright © 2000 by Apple Computer, Inc., All Rights Reserved.
//
// You may incorporate this Apple sample code into your own code
// without restriction. This Apple sample code has been provided "AS IS"
// and the responsibility for its operation is yours. You may redistribute
// this code, but you are not permitted to redistribute it as
// "Apple sample code" after having made changes.
//
// ************************
// layer utility routines *
// ************************

function getStyleObject(objectId) {
    // cross-browser function to get an object's style object given its id
    if(document.getElementById && document.getElementById(objectId)) {
	// W3C DOM
	return document.getElementById(objectId).style;
    } else if (document.all && document.all(objectId)) {
	// MSIE 4 DOM
	return document.all(objectId).style;
    } else if (document.layers && document.layers[objectId]) {
	// NN 4 DOM.. note: this won't find nested layers
	return document.layers[objectId];
    } else {
	return false;
    }
} // getStyleObject

function changeObjectVisibility(objectId, newVisibility) {
    // get a reference to the cross-browser style object and make sure the object exists
    var styleObject = getStyleObject(objectId);
    if(styleObject) {
	styleObject.visibility = newVisibility;
	return true;
    } else {
	// we couldn't find the object, so we can't change its visibility
	return false;
    }
} // changeObjectVisibility

function moveObject(objectId, newXCoordinate, newYCoordinate) {
    // get a reference to the cross-browser style object and make sure the object exists
    var styleObject = getStyleObject(objectId);
    if(styleObject) {
	styleObject.left = newXCoordinate;
	styleObject.top = newYCoordinate;
	return true;
    } else {
	// we couldn't find the object, so we can't very well move it
	return false;
    }
} // moveObject
