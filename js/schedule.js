var toggle_visible_group_info = new Object();
var modal_open = null;

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

function sync_full(jq_element) {
    var left = String(jq_element.parent().scrollLeft()) + "px";
    var width = String(jq_element.parent().width()) + "px";
    jq_element.css({'left': left, 'width': width});
}

function sync_scroll(jq_element) {
    var left = String(jq_element.parent().scrollLeft()) + "px";
    jq_element.css({'left': left});
}

function sync_all_full () {
    jQuery(".sync_scroll:visible").each(
        function(){
            sync_full(jQuery(this));
        }
    );
}

function scroll_listener() {
    var jq_scroll_element = jQuery(this);
    var left = String(jq_scroll_element.scrollLeft()) + "px";

    jq_scroll_element.find(".sync_scroll:visible").css({
        'left': left
    })
}

function toggle_visible_by_id(id) {
    jQuery(".tv_toggle_vis").filter(function(){
        return jQuery(this).attr("data-workshop") == id.id;
    }).each(function(){
        var jq_element = jQuery(this);
        if(jq_element.hasClass("sync_scroll")) {
            sync_full(jq_element);
        }
        jq_element.toggle();
    });

    jQuery(".tv_stylize").filter(function(){
        return jQuery(this).attr("data-workshop") == id.id;
    }).toggleClass('tv_shown');
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
    var id = get_workshop_id_for_element(this);
    if (! id) {
        return;
    }

    if (id.grp) {
        var current_vis = toggle_visible_update_group_info(id);
        if (current_vis) {
            toggle_visible_by_id(current_vis);
        }
    }

    toggle_visible_by_id(id);
}

function modal_button_handler(event) {
    var element = jQuery(this);
    var modal_id = element.attr('data-modal-id');

    if (!modal_id) {
        return;
    }

    event.preventDefault();
    //jQuery(document.getElementById(modal_id)).css('display', 'flex');
    jQuery(document.getElementById(modal_id)).show();
    /*global*/ modal_open = modal_id;
}

function modal_close_handler() {
    var element = jQuery(this);
    var i;

    if (element.hasClass('modal')) {
        element.hide();
        /*global*/ modal_open = null;
        return;
    }

    parents = element.parents();
    for (i=0; i<parents.length; i++) {
        element = jQuery(parents[i]);
        if (element.hasClass('modal')) {
            element.hide();
            /*global*/ modal_open = null;
            return;
        }
    }
}

function modal_keydown_handler(event) {
    if (modal_open != null && event.which == 20) {
        event.preventDefault();
        jQuery(document.getElementById(modal_open)).hide();
        /*global*/ modal_open = null;
    }
}

// setup function
function schedule_set_up () {
    jQuery(".tv_button").click(tv_button_handler);
    jQuery(".modal_button").click(modal_button_handler);
    jQuery(".modal .close").click(modal_close_handler);
    jQuery(".modal-wrapper").click(modal_close_handler);

    jQuery(".sync_scroll").parent().each(
        function(){
            jQuery(this).scroll(scroll_listener.bind(this));
            return true;
        }
    );

    jQuery(window).resize(sync_all_full);
    jQuery(window).keydown(modal_keydown_handler);
    sync_all_full();
}

jQuery(document).ready(schedule_set_up);
