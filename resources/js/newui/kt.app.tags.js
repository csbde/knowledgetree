kt.app.tags = new function() {

    var self = this;

    this.submitTags = function()
    {
        var tags = encodeURIComponent(jQuery('#tagcloud').val());
        var params = {tags: tags};
        var synchronous = false;
        var func = 'metadata.save';
        ktjapi.callMethod(func, params, self.updateSuccessful, synchronous, self.updateFailed, 200);
    }

    this.updateSuccessful = function()
    {
        return;
    }

    this.updateFailed = function()
    {
        return;
    }

}
