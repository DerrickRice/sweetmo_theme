jQuery(function ($) {

	$('.nav-primary ul.menu-primary').superfish({
		delay:       100,								// 0.1 second delay on mouseout
		animation:   {opacity:'show',height:'show'},	// fade-in and slide-down animation
		dropShadows: false								// disable drop shadows
	});

	$(".menu-icon").click(function(){
		$(".nav-primary ul.menu-primary").slideToggle();
		$(".menu-icon").toggleClass( "active" );
	});

	$(window).resize(function(){
		if(window.innerWidth > 768) {
			$(".nav-primary ul.menu-primary, nav .sub-menu").removeAttr("style");
		}
	});

});

/* Validation logic for purchases */
(function( sweetmo_shop, $, undefined ) {
	// == Private Variables == //
    // var a_private_variable = default;

	// == Public Variables == //

	// array of [classname, is_installed]
	sweetmo_shop._triggers = [];

	// array of [source_field, has_values, show_field, clear_on_hide]
	sweetmo_shop._visrules = [];

	sweetmo_shop._submit_hooked = false;

	// == Private Methods == //
	function get_wrappers_for_fields_by_class(clname) {
		return $("table.wccpf_fields_table").filter(function() {
			var jqelem = $(this);
			return jqelem.hasClass(clname) || jqelem.hasClass(clname + "-wrapper");
		}).toArray();
	}

	function get_inputs_from_wrapper(wrapper) {
		return $(wrapper).find("select, input, textarea").toArray();
	}

	// == Public Methods == //
	sweetmo_shop.activate = function() {
		if (sweetmo_shop._install_triggers()) {
			sweetmo_shop.sync();
		}

		if (!sweetmo_shop._submit_hooked) {
			$('#content div.product form.cart').submit(function(ev){
				sweetmo_shop.submit_hook(ev, this);
			});
			sweetmo_shop._submit_hooked = true;
		}
	};

	sweetmo_shop._install_triggers = function() {
		var found = false;

		var i;
		for (i=0; i<sweetmo_shop._triggers.length; i++) {
			var trigger = sweetmo_shop._triggers[i];
			if (trigger[1]) {
				continue;	// already installed
			}

			var fields = get_inputs_from_wrapper(
				get_wrappers_for_fields_by_class(trigger[0])
			);

			if (fields.length == 0) {
				continue;	// not found. Come back later?
			}

			$(fields).change(sweetmo_shop.sync);
			trigger[1] = true;
			found = true;
		}

		return found;
	};

	sweetmo_shop.add_trigger = function(clname) {
		if (!clname) {
			throw new Error("need a real class name");
		}
		var i;
		for (i=0; i<sweetmo_shop._triggers.length; i++) {
			if (sweetmo_shop._triggers[i][0] === clname) {
				return false;
			}
		}
		sweetmo_shop._triggers.push([clname, false]);
		return true;
	};

	sweetmo_shop.add_visrule = function(
		source_field, has_values, show_field, clear_on_hide
	) {
		if (!source_field || !show_field) {
			throw new Error("need a real class name");
		}
		var visrule = [source_field, has_values, show_field, clear_on_hide];
		var i;
		for (i=0; i<sweetmo_shop._visrules.length; i++) {
			ex_source_field = sweetmo_shop._visrules[i][0];
			ex_show_field = sweetmo_shop._visrules[i][2];
			if (ex_source_field === source_field && ex_show_field == show_field) {
				return false;
			}
		}

		sweetmo_shop._visrules.push(visrule);
		return true;
	};

	sweetmo_shop.sync = function() {
		var i;
		for (i=0; i<sweetmo_shop._visrules.length; i++) {
			var visrule = sweetmo_shop._visrules[i];
			sweetmo_shop._sync_visrule(
				visrule[0],
				visrule[1],
				visrule[2],
				visrule[3]
			);
		}
	};

	sweetmo_shop._sync_visrule = function(source_field, has_values, show_field, clear) {
		var fields = get_inputs_from_wrapper(
			get_wrappers_for_fields_by_class(source_field)
		);

		var has_match = false;
		var i;
		for (i=0; i<fields.length && !has_match; i++) {
			// get the value of the input, whatever that means.
			var value = sweetmo_shop._get_field_value(fields[i]);
			if (value === null || value === undefined) {
				continue;
			}

			var j;
			for (j=0; j<has_values.length && !has_match; j++) {
				if (value === has_values[j]) {
					has_match = true;
				}
			}
		}

		if (has_match) {
			sweetmo_shop.show_fields_by_class(show_field);
		} else {
			sweetmo_shop.hide_fields_by_class(show_field, clear);
		}
	};

	sweetmo_shop.show_fields_by_class = function(clname) {
		if (!clname) {
			throw new Error("need a real class name");
		}
		$(get_wrappers_for_fields_by_class(clname)).show();
	};

    sweetmo_shop.hide_fields_by_class = function(clname, clear=false) {
		if (!clname) {
			throw new Error("need a real class name");
		}
		var wrappers = get_wrappers_for_fields_by_class(clname);
		if (clear) {
			var fields = get_inputs_from_wrapper(wrappers);
			var i;
			for (i=0; i<fields.length; i++) {
				sweetmo_shop._clear_field(fields[i]);
			}
		}
		$(wrappers).hide();
    };

	sweetmo_shop.clear_hidden_fields = function() {
		var fields = get_inputs_from_wrapper(
			$("table.wccpf_fields_table").filter(":hidden")
		);

		var i;
		for (i=0; i<fields.length; i++) {
			sweetmo_shop._clear_field(fields[i]);
		}
	};

	sweetmo_shop._get_field_value = function(input_element) {
		var jqelem = $(input_element);
		if (jqelem.length != 1) {
			throw new Error("Expected just 1 input element for _get_field_value. Have " + jqelem.length);
		}

		if (jqelem.is("textarea") || jqelem.is("input[type=\"text\"]")) {
			return jqelem.val();
		} else if (jqelem.is("input[type=\"number\"]") {
			return Number(jqelem.val());
		} else if (jqelem.is("input[type=\"checkbox\"]" || jqelem.is("input[type=\"radio\"]"))) {
			if (jqelem.prop("checked")) {
				return jqelem.val();
			} else {
				return null;
			}
		} else if (jqelem.is("select") && !jqelem.prop('multiple')) {
			return jqelem.val();
		} else {
			throw new Error("Unsure how to get the value of input type " + jqelem.prop('tagName'));
		}
	}

	sweetmo_shop._clear_field = function(input_element) {
		var jqelem = $(input_element);
		if (jqelem.length != 1) {
			throw new Error("Expected just 1 input element for _clear_field. Have " + jqelem.length);
		}

		if (jqelem.is("textarea") || jqelem.is("input[type=\"text\"]")) {
			jqelem.val("");
		} else if (jqelem.is("input[type=\"number\"]") {
			jqelem.val("0");
		} else if (jqelem.is("input[type=\"checkbox\"]" || jqelem.is("input[type=\"radio\"]"))) {
			jqelem.prop("checked", false);
		} else if (jqelem.is("select") && !jqelem.prop('multiple')) {
			jqelem.prop('selectedIndex', 0);
		} else {
			throw new Error("Unsure how to change value of input type " + jqelem.prop('tagName'));
		}
	};

	sweetmo_shop.submit_hook = function(ev, form_element) {
		var errors = sweetmo_shop.form_validate();
		if (errors.length > 0) {
			sweetmo_shop._inform_errors(errors);
			ev.preventDefault();
			return false;
		}

		sweetmo_shop.clear_hidden_fields();
		return true;
	};

	sweetmo_shop._inform_errors = function(errors) {
		$("ul.woocommerce-error").remove();
		var warnings = $("<ul></ul>")
				.addClass('woocommerce-error')
				.attr('role', 'alert');
		var i;
		for (i=0; i<errors.length; i++) {
			warnings.append(
				$("<li></li>").text(errors[i])
			);
		}

		$("#content").prepend(warnings);
		var top = warnings.offset().top;
		var container = $('html, body');
		if (container.scrollTop() > top) {
			container.animate({scrollTop: top}, 500);
		}
	};

	sweetmo_shop.form_validate = function() {
		var i, j, inputs, has_value, classes, val, rval, wrappers;
		var errors = [];

		// ValCheck fields validation
		wrappers = $(get_wrappers_for_fields_by_class("valcheck")).filter(":visible");
		for (i=0; i<wrappers.length; i++) {
			rval = null;

			classes = $(wrappers[i]).attr("class").split(' ');
			for (j=0; j<classes.length; j++) {
				if (classes[j].indexOf('val-') == 0) {
					rval = classes[j].substring(4);
					break;
				}
			}

			if (!rval) {
				throw new Error("Could not find a 'val-' for a valcheck input");
			}

			has_value = false;
			inputs = get_inputs_from_wrapper(wrappers[i]);

			for (j=0; j<inputs.length && !has_value; j++) {
				val = sweetmo_shop._get_field_value(inputs[j]);
				has_value = has_value || (val === rval);
			}

			if (has_value) {
				continue;
			}

			val = $(wrappers[i]).find("label").first().text();
			val = "Incorrect value provided for \"" + val + "\"";
			errors.push(val);
		}

		// Required fields validation
		wrappers = $(get_wrappers_for_fields_by_class("required")).filter(":visible");
		for (i=0; i<wrappers.length; i++) {
			inputs = get_inputs_from_wrapper(wrappers[i]);
			has_value = false
			for (j=0; j<inputs.length && !has_value; j++) {
				val = sweetmo_shop._get_field_value(inputs[j]);
				has_value = has_value || !!val;
			}

			if (has_value) {
				continue;
			}

			val = $(wrappers[i]).find("label").first().text();
			val = "A value is required for \"" + val + "\"";
			errors.push(val);
		}

		return errors;
	};
}( window.sweetmo_shop = window.sweetmo_shop || {}, jQuery ));

jQuery(document).ready(function(){
	window.sweetmo_shop.add_trigger('housing');
	window.sweetmo_shop.add_visrule('housing', ['guest'], 'housing_guest');
	window.sweetmo_shop.add_visrule('housing', ['host'], 'housing_host');
	window.sweetmo_shop.add_visrule('housing', ['guest', 'host'], 'housing_either');
	window.sweetmo_shop.activate();
});
