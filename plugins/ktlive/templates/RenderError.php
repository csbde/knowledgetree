<style>
	div.liveError{
		border: 1px solid #a10000;
		background-color:#EEEEEE;
		font-family: Arial, Helvetica, sans-serif;
		font-size: 11px;
	}
	
	.liveError .heading{
		display:block;
		text-align: center;
		padding: 10px;
		font-size: 18px;
		font-weight: bold;
		background-color: #DDDDDD;
	}
	
	.liveError .body{
		padding: 15px;
	}
	
	.liveError .title{
		font-size:14px;
		font-weight:bold;
		margin-left: -5px;
		padding: 10px 0px 10px 0px;
		display: block;
	}
	
	.liveError .debug{
		display: block;
		height: 200px;
		max-width: 600px;
		overflow:scroll;
		background-color: #CCCC00;
		margin-top: -5px;
		border: 1px solid #000000;
		text-align:left;
	}
</style>
<table cellspacing="0" cellpadding="0" style="height:100%; width: 100%;">
  <tr>
    <td style="padding: 150px;">
    	<div class="liveError">
			<span class="heading">KTLive Error</span>
			<div class="body">
				<span class="title"><?php echo $error->title; ?></span>
				<div class="description"><?php echo $error->description; ?></div>
				<?php if($error->debug):?>
					<span class="title">Debug Info</span>
					<table style="width: 100%;"><tr><td align="center">
						<div class="debug"><pre><?php echo print_r($error->debug,true); ?></pre></div>
					</td></tr></table>
				<?php endif;?>
			</div>
		</div>
    </td>
  </tr>
</table>
