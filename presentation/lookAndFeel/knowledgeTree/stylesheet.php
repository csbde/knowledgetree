<?php
require("../../../config/dmsDefaults.php");
header("Content-type: text/css");
?>
P {
font-size : 9pt;
font-family : Verdana, sans-serif;
font-weight : lighter;
font-style : normal;
color : #000000;
text-decoration: none;
}

P.footer {
font-size : 9pt;
font-family : Verdana, sans-serif;
font-weight : lighter;
font-style : normal;
color : #999999;
text-decoration: none;
}

P.title {
font-size : 9pt;
font-family : Verdana, sans-serif;
font-weight : inherit;
font-style : normal;
color : #ffffff;
text-decoration: none;
}


A {
font-size : 8pt;
font-family : Verdana, sans-serif;
text-decoration: none;
color : #000000;
}


A:Visited  {
font-size : 8pt;
font-family : Verdana, sans-serif;
font-style : normal;
color : #000000;
}

A:Active  {
color : #000000;
font-size : 8pt;
font-family : Verdana, sans-serif;
font-style : normal;
}

A:hover {
color : #000000;
font-size : 8pt;
font-family : Verdana,sans-serif;
font-style : normal;
text-decoration: underline;
}

TABLE {
	font-size : 10pt;
	font-family : Verdana, sans-serif;
}

TD {
	font-size : 8pt ! important;
	font-family : Verdana, sans-serif;
}

CAPTION {
	font-size : 10pt;
}

TH {
	font-size : 9pt;
	font-family : Verdana, sans-serif;
}

TH.sectionHeading {
font-size : 10 pt;
font-family : Verdana, sans-serif;
font-style : normal;
color : #ffffff;
text-decoration: none;
}
  
TH.sectionColumns {
font-size : 8pt;
font-family : Verdana, sans-serif;
font-weight : lighter;
font-style : normal;
color : #000000;
text-decoration: none;
}

.browseTypeSelect {
    color : #000000;
    font-size : 8pt;
    font-family : Verdana,sans-serif;
}

.errorText {
	color : #FF0000;
	font-size : 8pt;
	text-align : center;
	font-family : Verdana,sans-serif;
}

INPUT {
	font-size : 8pt;
	font-family : Verdana,sans-serif;
}

SELECT {
	font-size : 8pt;
	font-family : Verdana,sans-serif;
}

#divUp   {
    position:absolute; 
    left: <?php echo $default->upArrowLeft ?>px; 
    top: <?php echo $default->upArrowTop ?>px;}

#divDown {
    position:absolute; 
    left: <?php echo $default->downArrowLeft ?>px; 
    top: <?php echo $default->downArrowTop ?>px;}

#divScrollTextCont {
    position:absolute; 
    left: <?php echo $default->textBoxLeft ?>px; 
    top: <?php echo $default->textBoxTop ?>px; 
    width: <?php echo $default->textBoxWidth ?>px; 
    height: <?php echo $default->textBoxHeight ?>px; 
    clip:rect(0px <?php echo $default->textBoxWidth ?>px <?php echo $default->textBoxHeight ?>px 0px); 
    overflow:hidden; 
    visibility:hidden;
}

#divText {
    position:absolute; 
    left:0px; 
    top:0px;
}