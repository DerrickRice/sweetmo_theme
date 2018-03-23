var toggle_visible_group_info = new Object();

function toggle_visible_update_group_info(id) {
    var current = toggle_visible_group_info[id.grp];

    if (! current) {
        toggle_visible_group_info[id.grp] = id.id;
        return null;
    }

    if (current == id.id) {
        toggle_visible_group_info[id.grp] = null;
        return null;
    }

    toggle_visible_group_info[id.grp] = id.id;

    return {
        id: current,
        grp: id.grp
    };
}

function toggle_visible_by_id(id) {
    jQuery(".tv_toggle_vis").filter(function(){
        return jQuery(this).attr("data-workshop") == id.id;
    }).toggle();

/*
    jQuery(".tv_stylize").filter(function(){
        return jQuery(this).attr("data-tv-id") == id;
    }).toggleClass('tv_shown');
*/
}

function extract_workshop_id_from_element(jq_element) {
    return {
        id: jq_element.attr('data-workshop'),
        grp: jq_element.attr('data-workshop-group')
    };
}

// Returns {id: _, grp: _} for the given element, traversing up the tree as
// necessary
function get_workshop_id_for_element(element) {
    element = jQuery(element);
    var i;

    if (element.attr('data-workshop')) {
        return extract_workshop_id_from_element(element);
    }

    parents = element.parents();
    for (i=0; i<parents.length; i++) {
        element = jQuery(parents[i]);
        if (element.attr('data-workshop')) {
            return extract_workshop_id_from_element(element);
        }
    }

    return null;
}

function tv_button_handler() {
    console.log('Button handler activated');
    var id = get_workshop_id_for_element(this);
    if (! id) {
        console.log('No ID found');
        return;
    }
    console.log('Click handler for %O', id);

    if (id.grp) {
        var current_vis = toggle_visible_update_group_info(id);
        if (current_vis) {
            toggle_visible_by_id(current_vis);
        }
    }

    toggle_visible_by_id(id);
}

jQuery(document).ready(function() {
    jQuery(".tv_button").click(tv_button_handler);
    console.log('Button handler installed');
});

/*

(function($) {
// gets bound to a scrolling table's scroll event with the table as `this`
function unboundScrollEventHandler () {
    var scrollElement = $(this);
    var jqScrollElement = $(scrollElement);

    var left = jqScrollElement.scrollLeft();

    jqScrollElement.find("tr > :first-child").css({
        "position": "relative",
        "left": left,
    });
}

function triggerScroll () {
    $(".scrolling").each(
        function () {
            $(this).scroll();
        }
    );
}

function setBackground(elements) {
    elements.each(
        function(k, element) {
            var element = $(element);
            var parent = $(element).parent();

            var elementBackground = element.css("background-color");
            elementBackground = (elementBackground == "transparent" || elementBackground == "rgba(0, 0, 0, 0)") ? null : elementBackground;

            var parentBackground = parent.css("background-color");
            parentBackground = (parentBackground == "transparent" || parentBackground == "rgba(0, 0, 0, 0)") ? null : parentBackground;

            var background = parentBackground ? parentBackground : "white";
            background = elementBackground ? elementBackground : background;

            element.css("background-color", background);
        }
    );
}

function setUp () {
    $(".scrolling").each(
        function () {
            var jqScrollElement = $(this);

            // set an opaque background color on the cells
            setBackground(jqScrollElement.find("tr > :first-child"));

            // bind the event handler
            var boundScrollEventHandler = unboundScrollEventHandler.bind(this);
            jqScrollElement.scroll(boundScrollEventHandler);
            return true;
        }
    );

    $(window).resize(triggerScroll);

    triggerScroll();
}

$(document).ready(setUp);
})(jQuery);

*/
