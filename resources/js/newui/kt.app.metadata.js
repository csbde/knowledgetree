kt.app.metadata = new function() {

    var self = this;

    this.saveTags = function()
    {
        var tags = encodeURIComponent(jQuery('#tagcloud').val());
        var params = {'tags': tags, 'documentId': 250};
        var synchronous = false;
        var func = 'metadataService.saveTags';
        ktjapi.callMethod(func, params, self.updateSuccessful, synchronous, self.updateFailed, 30000);
    }

    this.updateSuccessful = function()
    {
        alert('success');
        return;
    }

    this.updateFailed = function()
    {
        alert('the sweet sound of failure');
        return;
    }

}
