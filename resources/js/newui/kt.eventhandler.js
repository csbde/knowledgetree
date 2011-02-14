/**
 * Events can be registered with this class, and the run event called on body load, in order to defer
 * javascript events which might otherwise delay loading of additional js files and other page elements.
 */
kt.eventhandler = new function() {

    var self = this;

    self.events = [];

    this.register = function(callback, parameters, type)
    {
        if (typeof type == 'undefined') { type == 'unknown' };

        var event = [callback, parameters, type];
        self.events[self.events.length] = event;
    }

    // NOTE the special case for fragment requests demands that requests be grouped...
    //      this was designed specifically for that purpose and undoubtedly will require
    //      refactoring if we want to use this for something else...i.e make it more generic.
    //      in the meantime, "for you, special price" ;)
    this.run = function()
    {
        for (idx = 0; idx < self.events.length; ++idx) {
            if (self.events[idx][2] == 'fragment') {
                if (typeof fragments == 'undefined') {
                    var fragments = [];
                    var fragmentCallback = self.events[idx][0];
                }

                for (idx2 = 0; idx2 < self.events[idx][1].length; ++idx2) {
                    fragments[fragments.length] = self.events[idx][1][idx2];
                }
            }
            else if (self.events[idx][2] == 'exec') {
                if (typeof execs == 'undefined') {
                    var execs = [];
                    var execCallback = self.events[idx][0];
                }

                for (idx2 = 0; idx2 < self.events[idx][1].length; ++idx2) {
                    execs[execs.length] = self.events[idx][1][idx2];
                }
            }
            else {
                self.events[idx][0](self.events[idx][1]);
            }
        }

        // deal with fragment loading
        fragmentCallback(fragments);
        execCallback(execs);
    }

}