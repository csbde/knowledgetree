kt.tags = new function() {

    var self = this;

    this.submitTags = function()
    {
        var tags = encodeURIComponent(jQuery('#tagcloud').val());
        jQuery.ajax({
                url: '/saveTags.php?tags=' + tags,
                timeout: 30000,
                success: function() { alert('saved!'); },
                error: function() { alert('NOT saved!'); }
        });
    }

}
