<?php
	foreach ($sideMenuElements as $ele) {
		?>
		<span id="<?php echo $ele['step']; ?>" class="<?php echo $ele['class']; ?>">
			<?php if ($ele['class'] == "current") { ?>
				<?php echo $ele['name']; ?>
			<?php } else { ?>
				<?php if ($ajax) { ?>
					<a tabindex="-1" href='#' onclick='javascript:{w.getUrl("index.php?step_name=<?php echo $ele['step']; ?>", "content_container");}'>
						<?php echo $ele['name']; ?>
					</a>
				<?php } else { ?>
					<?php if($ele['class'] == 'inactive') { ?>
						<?php echo $ele['name']; ?>
					<?php } else { ?>
					<a tabindex="-1" href="index.php?step_name=<?php echo $ele['step'];?>">
						<?php echo $ele['name']; ?>
					</a>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</span>
		<br />
		<?php
	}
?>