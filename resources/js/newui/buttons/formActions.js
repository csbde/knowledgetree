jQuery(document).ready(function() {
	jQuery("#modal.form_actions input[type='submit']:last-child").css({background:"none"});
	jQuery("#modal.form_actions a:last-child").css({background:"none"});
	jQuery('#modal.form_actions').prepend('<div class="roundleft"></div>').prepend('<div class="roundright2"></div>');
	jQuery('.buttonsList').appendTo(jQuery('#bigbuttons'));
});