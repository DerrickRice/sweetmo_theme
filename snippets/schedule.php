<?php

try {
	if (!@include_once(__DIR__ . '/../include/schedule.php')) {
		throw new Exception(
			"Unable to include mdsc_deps.php"
		);
	}
} catch (Exception $err) {
	echo sweetmo_internal_error_html($err->getMessage());
	return 1;
}

Schedule::js_include_once('js/schedule.js');
Schedule::begin_grid($schedule_width);
Schedule::handle_sections(
	Schedule::split_sections($schedule_markup)
);
Schedule::end_grid();

1;
