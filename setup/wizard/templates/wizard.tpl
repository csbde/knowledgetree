<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<title>KnowledgeTree Installer</title>
	<link rel="stylesheet" type="text/css" href="resources/wizard.css" />
</head>

<body>
<div class="container">
    <div class="header">
        <div class="logo"></div>
    </div>
    <div class="content">

        <div class="left">
            <?php echo $left; ?>
        </div>
        <div class="right">
        
           <?php echo $content; ?>
<!--
            <div class="buttons">
                <a href="index.php?step_name=&step=previous" class="next">Previous</a>
                <a href="index.php?step_name=&step=next" class="next">Next</a>
            </div>
            -->
        </div>
    </div>
    <div class="footer">
        <div class="powered-by"></div>
    </div>
</div>
</body>
</html>