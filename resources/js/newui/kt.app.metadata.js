kt.app.metadata = new function() {

    var self = this;

    this.saveTags = function(documentId)
    {
    	jQuery('.editable_control', jQuery('.tags')).removeClass('none').addClass('spin').css('visibility', 'visible');
        var tags = encodeURIComponent(jQuery('#tagcloud').val());
        var params = {'tags': tags, 'documentId': documentId};
        var synchronous = false;
        var func = 'metadataService.saveTags';
        ktjapi.callMethod(func, params, self.updateSuccessful, synchronous, self.updateFailed, 30000);
    }

    this.updateSuccessful = function()
    {
        jQuery('.editable_control', jQuery('.tags')).removeClass('spin').addClass('none').css('visibility', 'hidden');
        return;
    }

    this.updateFailed = function()
    {
        alert('the sweet sound of failure');
        return;
    }

}
