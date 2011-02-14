/**
 * Events can be registered with this class, and the run event called on body load, in order to defer
 * javascript events which might otherwise delay loading of additional js files and other page elements.
 */
kt.eventhandler = new function() {

    var self = this;

    self.events = [];

    this.register = function(callback, parameters)
    {
        var event = [callback, parameters];
        self.events[self.events.length] = event;
    }

    this.run = function()
    {
        for (idx = 0; idx < self.events.length; ++idx) {
            console.log('running registered event ' + self.events[idx][0] + ' ' + self.events[idx][1])
            self.events[idx][0](self.events[idx][1]);
        }
    }

}