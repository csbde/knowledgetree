/**
 * Events can be registered with this class, and the run event called using jQuery's ready event,
 * in order to defer javascript events which might otherwise delay loading of additional js files
 * and other page elements.
 *
 * NOTE that use of this class DOES require jQuery.
 */
kt.eventhandler = new function() {

    var self = this;

    self.events = [];

    this.register = function(callback, parameters, type)
    {
        type = (typeof type == 'undefined') ? 'unknown' : type;
        var event = [callback, parameters, type];
        self.events[self.events.length] = event;
    }

    // NOTE the special case for fragment requests demands that requests be grouped...
    //      this was designed specifically for that purpose and undoubtedly will require
    //      refactoring if we want to use this for something else...i.e make it more generic.
    //      in the meantime, "for you, special price" ;)
    this.run = function()
    {
        if (self.events.length < 1) { return; }

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
        if (typeof fragmentCallback == 'function') { fragmentCallback(fragments); }
        if (typeof execCallback == 'function') { execCallback(execs); }
    }

}

// NOTE Would prefer this to use (window).load, but this is delayed on the document details page
//      when there is a thumbnail/instant view - these delay the onload event enough that a user
//      can kick off additional requests for content which is still coming.
jQuery(document).ready(function() {
    kt.eventhandler.run();
});
