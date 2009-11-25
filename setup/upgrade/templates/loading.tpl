<div id="loadingBar" style="display:none; text-align: center; padding: 4px;">
		<img src="../wizard/resources/graphics/loading.gif">
	<br/>
	<?php echo $loadingText; ?>
</div>
<script>
    function doSubmit(obj)
	{
        var e = document.createElement('input');
            e.setAttribute('type','hidden');
            e.setAttribute('name',obj.name);
            e.setAttribute('value',obj.value);
        document.getElementById('loadingBar').appendChild(e);

        $('#buttonBar').hide();            
        $('#loadingBar').show();
        $('#dbForm').submit();
    }
</script>