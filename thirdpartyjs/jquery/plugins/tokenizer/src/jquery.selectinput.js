/*
 * jQuery Plugin: Dynamic listing of items populated from a drop down selector.
 *
 * Modified a plugin by:

 * Copyright (c) 2009 James Smith (http://loopj.com)
 * Licensed jointly under the GPL and MIT licenses,
 * choose which one suits your project best!
 */

(function($) {

$.fn.selectInput = function (src, options) {
    var settings = $.extend({
        tokenLimit: null,
        onAdd: null,
        onDelete: null
    }, options);

    settings.classes = $.extend({
        tokenList: "token-input-list",
        token: "token-input-token",
        tokenDelete: "token-input-delete-token",
        selectedToken: "token-input-selected-token",
        highlightedToken: "token-input-highlighted-token",
        dropdown: "token-input-dropdown",
        dropdownItem: "token-input-dropdown-item",
        dropdownItem2: "token-input-dropdown-item2",
        selectedDropdownItem: "token-input-selected-dropdown-item",
        inputToken: "token-input-input-token"
    }, options.classes);

    return this.each(function () {
        var list = new $.SelectTokenList(this, src, settings);
    });
};

$.SelectTokenList = function (input, src, settings) {
    //
    // Variables
    //

    // TODO prevent adding of items already in list
    // Override the src input onchange event to add items to our input list
    var selector = src;
    selector.onchange = function() {
        if (selector.options[selector.selectedIndex].value != '') {
            add_token(selector.options[selector.selectedIndex].value, selector.options[selector.selectedIndex].text);
            selector.options[selector.selectedIndex].disabled = true;
            selector.selectedIndex = '';
        }
    };

    // Save the tokens
    var saved_tokens = [];

    // Keep track of the number of tokens in the list
    var token_count = 0;

    // Basic cache to save on db hits
    var cache = new $.TokenList.Cache();

    // Create a new text input
    var input_box = $("<input type=\"text\"  autocomplete=\"off\">")
        .css({
            outline: "none"
        });

    // Keep a reference to the original input box
    var hidden_input = $(input)
                           .hide()
                           .focus(function () {
                               input_box.focus();
                           })
                           .blur(function () {
                               input_box.blur();
                           });

    // Keep a reference to the selected token
    var selected_token = null;

    // The list to store the token items in
    var token_list = $("<ul />")
        .addClass(settings.classes.tokenList)
        .insertBefore(hidden_input)
        .click(function (event) {
            var li = get_element_from_event(event, "li");
            if(li && li.get(0) != input_token.get(0)) {
                toggle_select_token(li);
                return false;
            } else {
                input_box.focus();
                if(selected_token) {
                    deselect_token($(selected_token), POSITION.END);
                }
            }
        })
        .mouseover(function (event) {
            var li = get_element_from_event(event, "li");
            if(li && selected_token !== this) {
                li.addClass(settings.classes.highlightedToken);
            }
        })
        .mouseout(function (event) {
            var li = get_element_from_event(event, "li");
            if(li && selected_token !== this) {
                li.removeClass(settings.classes.highlightedToken);
            }
        })
        .mousedown(function (event) {
            // Stop user selecting text on tokens
            var li = get_element_from_event(event, "li");
            if(li){
                return false;
            }
        });

    // The token holding the input box
    // TODO this is a hack to keep the code working, it needs something there, but we don't want to see it :)
    //      Would prefer nothing here at all, but then the box is a flat line until populated.
    var input_token = $("<input style='border:none' />")
        .addClass(settings.classes.inputToken)
        .appendTo(token_list);
//        .append(input_box);

    init_list();

    //
    // Functions
    //

    // Pre-populate list if items exist
    function init_list () {
//        hidden_input.val("");
        li_data = settings.prePopulate;
        if(li_data && li_data.length) {
            for(var i = 0; i < li_data.length; ++i) {
                var this_token = $("<li><p>"+li_data[i].name+"</p> </li>")
                    .addClass(settings.classes.token)
                    .insertBefore(input_token);

                $("<span>&times;</span>")
                    .addClass(settings.classes.tokenDelete)
                    .appendTo(this_token)
                    .click(function () {
                        delete_token($(this).parent());
                        return false;
                    });

                $.data(this_token.get(0), "tokeninput", {"id": li_data[i].id, "name": li_data[i].name});

                // Save this token id
                var id_string = li_data[i].id + ","
                hidden_input.val(hidden_input.val() + id_string);

                token_count++;
                saved_tokens[saved_tokens.length] = li_data[i].id;
            }
        }
    }

    // Get an element of a particular type from an event (click/mouseover etc)
    function get_element_from_event (event, element_type) {
        return $(event.target).closest(element_type);
    }

    // Inner function to a token to the list
    function insert_token(id, value) {
        var this_token = $("<li><p>"+ value +"</p> </li>")
                            .addClass(settings.classes.token)
                            .insertBefore(input_token);

        // The 'delete token' button
        $("<span>x</span>")
            .addClass(settings.classes.tokenDelete)
            .appendTo(this_token)
            .click(function () {
                delete_token($(this).parent());
                return false;
            });

        $.data(this_token.get(0), "tokeninput", {"id": id, "name": value});

        return this_token;
    }

    // Add a token to the token list based on user input
    function add_token (id, value) {
        // Prevent duplicates
        if ($.inArray(id, saved_tokens) != -1) { return false; }

        var li_data = {id: id, name: value};
        var this_token = insert_token(li_data.id, li_data.name);
        var callback = settings.onAdd;

        // Save this token id
        var id_string = li_data.id + ","
        hidden_input.val(hidden_input.val() + id_string);

        token_count++;
        saved_tokens[saved_tokens.length] = id;

        // TODO this will have to disable the select input.
        //      Since we won't likely be limiting selections, probably not required.
        if(settings.tokenLimit != null && token_count >= settings.tokenLimit) {
            input_box.hide();
        }

        // Execute the onAdd callback if defined
        if($.isFunction(callback)) {
            callback(li_data.id);
        }
    }

    // Select a token in the token list
    function select_token (token) {
        token.addClass(settings.classes.selectedToken);
        selected_token = token.get(0);
    }

    // Deselect a token in the token list
    function deselect_token (token, position) {
        token.removeClass(settings.classes.selectedToken);
        selected_token = null;

        if(position == POSITION.BEFORE) {
            input_token.insertBefore(token);
        } else if(position == POSITION.AFTER) {
            input_token.insertAfter(token);
        } else {
            input_token.appendTo(token_list);
        }

        // Show the input box and give it focus again
        input_box.focus();
    }

    // Toggle selection of a token in the token list
    function toggle_select_token (token) {
        if(selected_token == token.get(0)) {
            deselect_token(token, POSITION.END);
        } else {
            if(selected_token) {
                deselect_token($(selected_token), POSITION.END);
            }
            select_token(token);
        }
    }

    // Delete a token from the token list
    function delete_token (token) {
        // Remove the id from the saved list
        var token_data = $.data(token.get(0), "tokeninput");
        var callback = settings.onDelete;

        // Delete the token
        token.remove();
        selected_token = null;

        // Show the input box and give it focus again
        input_box.focus();

        // Delete this token's id from hidden input
        var str = hidden_input.val()
        var start = str.indexOf(token_data.id+",");
        var end = str.indexOf(",", start) + 1;

        if(end >= str.length) {
            hidden_input.val(str.slice(0, start));
        } else {
            hidden_input.val(str.slice(0, start) + str.slice(end, str.length));
        }

        token_count--;
        index = $.inArray(token_data.id, saved_tokens);
        if (index != -1) {
            saved_tokens.splice(index, 1);
            $("#" + selector.id + " option[value='" + token_data.id + "']").removeAttr('disabled');
        }

        // Execute the onDelete callback if defined
        if($.isFunction(callback)) {
            callback(token_data.id);
        }
    }
};

})(jQuery);