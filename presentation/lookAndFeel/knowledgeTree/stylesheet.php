<?php
header('mime_type', 'text/css');
$sAgent = getenv("HTTP_USER_AGENT");
echo $sAgent;
$scroll = array();
$scroll["upArrowLeft"] = "";
$scroll["upArrowTop"] = "";
$scroll["downArrowLeft"] = "";
$scroll["downArrayTop"] = "";
$scroll["textBoxLeft"] = "";
$scroll["textBoxTop"] = "";
$scroll["textBoxWidth"] = "";
$scroll["textBoxHeight"] = "";

if ( ereg("Mozilla", $sAgent) && ereg("4.79", $sAgent) ) {
    // Mozilla/4.79    
    $scroll["upArrowLeft"] = "670";
    $scroll["upArrowTop"] = "190";
    $scroll["downArrowLeft"] = "670";
    $scroll["downArrowTop"] = "585";
    
    $scroll["textBoxLeft"] = "145";
    $scroll["textBoxTop"] = "200";
    $scroll["textBoxWidth"] = "600";
    $scroll["textBoxHeight"] = "395";  
} elseif ( ereg("Mozilla", $sAgent) && ereg("5.0", $sAgent) ) {
    // Mozilla/5.0
    $scroll["upArrowLeft"] = "650";
    $scroll["upArrowTop"] = "150";
    $scroll["downArrowLeft"] = "650";
    $scroll["downArrowTop"] = "580";
    
    $scroll["textBoxLeft"] = "145";
    $scroll["textBoxTop"] = "150";
    $scroll["textBoxWidth"] = "500";
    $scroll["textBoxHeight"] = "440";
} else if ( ereg("MSIE", $sAgent) && ereg("6.0", $sAgent) ) {
   // MSIE 6.0
    $scroll["upArrowLeft"] = "650";
    $scroll["upArrowTop"] = "160";
    $scroll["downArrowLeft"] = "650";
    $scroll["downArrowTop"] = "600";
    
    $scroll["textBoxLeft"] = "145";
    $scroll["textBoxTop"] = "160";
    $scroll["textBoxWidth"] = "500";
    $scroll["textBoxHeight"] = "440";  
}
?>
<pre>
.sectionHeading {
font-size : 10pt;
font-family : Verdana, sans-serif;
font-weight : lighter;
font-style : normal;
color : #ffffff;
text-decoration: none;
}
  
.sectionColumns {
font-size : 8pt;
font-family : Verdana, sans-serif;
font-weight : lighter;
font-style : normal;
color : #000000;
text-decoration: none;
}

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
font-size : 10pt;
font-family : Verdana, sans-serif;
font-style : normal;
color : #000000;
}


A:Visited  {
font-size : 10pt;
font-family : Verdana, sans-serif;
font-style : normal;
color : #0000FF;
}

A:Active  {
color : #000000;
font-size : 10pt;
font-family : Verdana, sans-serif;
font-style : normal;
}

A:hover {
color : #000000;
font-size : 10pt;
font-family : Verdana,sans-serif;
font-style : normal;
background-color : #FFFACD;
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

.browseTypeSelect {
    color : #000000;
    font-size : 10pt;
    font-family : Verdana,sans-serif;
}

.errorText {
	color : #FF0000;
	font-size : 10pt;
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
    left: <?php echo $scroll["upArrowLeft"] ?>px; 
    top: <?php echo $scroll["upArrowTop"] ?>px;}

#divDown {
    position:absolute; 
    left: <?php echo $scroll["downArrowLeft"] ?>px; 
    top: <?php echo $scroll["downArrowTop"] ?>px;}

#divScrollTextCont {
    position:absolute; 
    left: <?php echo $scroll["textBoxLeft"] ?>px; 
    top: <?php echo $scroll["textBoxTop"] ?>px; 
    width: <?php echo $scroll["textBoxWidth"] ?>px; 
    height: <?php echo $scroll["textBoxHeight"] ?>px; 
    clip:rect(0px <?php echo $scroll["textBoxWidth"] ?>px <?php echo $scroll["textBoxHeight"] ?>px 0px); 
    overflow:hidden; 
    visibility:hidden;
}

#divText {
    position:absolute; 
    left:0px; 
    top:0px;
}
