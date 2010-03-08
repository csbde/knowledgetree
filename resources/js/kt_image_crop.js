jQuery(function () {
    //TODO: Draw attributes and config options from widget attributes
    //jQuery('#kt_image_to_crop').imgAreaSelect({ maxWidth: 200, maxHeight: 150, handles: true });
    //jQuery('#kt_image_to_crop').imgAreaSelect({ aspectRatio: '4:3', handles: true });
    jQuery('#kt_image_to_crop').imgAreaSelect({ 
        x1: 0, 
        y1: 0, 
        x2: 313, 
        y2: 50,
        handles: true
    });
});

jQuery(document).ready(function() {
    jQuery('#kt_image_to_crop').imgAreaSelect({
        onSelectEnd: function (img, selection) {
            jQuery('input[name="data[crop_x1]"]').val(selection.x1);
            jQuery('input[name="data[crop_y1]"]').val(selection.y1);
            jQuery('input[name="data[crop_x2]"]').val(selection.x2);
            jQuery('input[name="data[crop_y2]"]').val(selection.y2);
        }
    })
});
