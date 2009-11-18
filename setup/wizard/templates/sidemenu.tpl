<?php
	$passCurrent = false;
	foreach ($sideMenuElements as $ele) {
		?>
		<span id="<?php echo $ele['step']; ?>" class="<?php echo $ele['class']; ?>">
			<?php if ($ele['class'] == "current") { ?>
				<?php echo $ele['name']; ?>
				<?php $passCurrent = true; ?>
				<?php //echo 'cur'; ?>
			<?php } else { ?>
				<?php if ($ajax) { ?>
					<?php if(!$passCurrent) { ?>
					<a tabindex="-1" href='#' onclick='javascript:{w.getUrl("index.php?step_name=<?php echo $ele['step']; ?>", "content_container");}'>
					<?php } ?>
						<?php echo $ele['name']; ?>
						<?php echo ''; ?>
					</a>
				<?php } else { ?>
					<?php if($ele['class'] == 'inactive') { ?>
						<?php echo $ele['name']; ?>
						<?php echo ''; ?>
					<?php } else { ?>
					<a tabindex="-1" href="index.php?step_name=<?php echo $ele['step'];?>">
						<?php echo $ele['name']; ?>
						<?php echo ''; ?>
					</a>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</span>
		<br />
		<?php
	}
?>