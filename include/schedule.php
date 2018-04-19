<?php

if (!@include_once 'mdsc_deps.php') {
	throw new Exception("Unable to include mdsc_deps.php");
}

class Schedule {
	private static $_js_included = array();
	private static $_supported_grid_sizes = array('1', '4');
	private static $_group = 0;

	public static function new_grp_id() {
		self::$_group++;
		return 'g' . self::$_group;
	}

	public static function maybe_esc_html($content) {
		if (empty($content) || $content[0] == '<') {
			return $content;
		}
		return esc_html($content);
	}

	public static function begin_grid($size) {
		assert(in_array($size, self::$_supported_grid_sizes));
		echo HtmlGen::elem(
			'div',
			array('class' => "schedule_grid schedule_grid$size")
		);
	}

	public static function end_grid() {
		echo '</div>';
	}

	/*
	 * The header of a schedule grid. Usually indicates the beginning of the
	 * event, such as the main dance or workshops.
	 */
 	public static function header($event, $venueid=null, $note=null) {
		$title = $event;

		$venueinfo = self::_venue_link_html($venueid);
		if ($venueinfo) {
			$title .= " at $venueinfo";
		}

		$html = HtmlGen::div_wrap($title, 'event');

		if ($note) {
			$html .= HtmlGen::div_wrap($note, 'note');
		}

 		echo HtmlGen::div_wrap($html, 'schedule_header');
 	}

	private static function _venue_link_html($venueid) {
		$venueid = trim($venueid);
		if (empty($venueid)) {
			return '';
		}

		$venue = self::_get_data('venue', $venueid);
		if (empty($venue)) {
			return '';
		}

		if ($venue['name_tba']) {
			return esc_html('TBA');
		}

		$name = $venue['name'];
		if (! empty($venue['shortname'])) {
			$name = $venue['shortname'];
		}

		// TODO: build more interesting information
		return esc_html($name);
	}

	public static function time(
		$start,
		$end,
		$sub=false
	) {
		$classes = array('grid1col', 'schedule_time');
		if ($sub) {
			$classes[] = 'schedule_subtime';
		}

		if ($end) {
			$time_text = $start . esc_html(' to ') . $end;
		} else if ($start) {
			$time_text = $start;
		} else {
			$time_text = '';
		}

		// need an extra inner div to vertically align things
		$html = HtmlGen::div_wrap($time_text);
		echo HtmlGen::div_wrap($html, $classes);
	}

	public static function live_music($id) {
		// band has a name, shrotname, bio, photo, and band-break-DJ-id
		// "Live music (TBA) by $id and breaks DJ'd by __TODO__";
		self::event("Live music (TBA)");
	}

	public static function event($name, $span='1') {
		echo HtmlGen::div_wrap(
			$name,
			array("schedule_event", self::_span_class($span))
		);
	}

	public static function performance($id, $span='1') {
		echo HtmlGen::div_wrap(
			esc_html("Special Performance (TBA)"),
			array("schedule_event", self::_span_class($span))
		);
	}

	private static function _get_data($type, $id) {
		// TODO: Handle the case where the type is invalid / not found.
		// It currently throws an exception.
		$all_data = MDSC::instance()->data->get_data($type);
		// TODO: Handle the case where where $id is invalid / not found.
		// It currently returns null, I think.
		return $all_data[$id];
	}

	private static function _span_class($span) {
		assert(in_array($span, self::$_supported_grid_sizes));
		return "grid{$span}col";
	}

	private static function _workshop_attrs($group, $id) {
		return array(
			'data-workshop-group' => $group,
			'data-workshop' => $id,
		);
	}

	public static function workshop($group, $id, $span='1') {
		$data = self::_get_data('class', $id);
		$html = '';

		// Workshop title
		if ($data['title_tba'] || empty($data['title'])) {
			$html .= HtmlGen::div_wrap(
				esc_html('Class TBA'),
				array('workshop_title', 'tba')
			);
		} else {
			$html .= HtmlGen::div_wrap(
				esc_html($data['title']),
				'workshop_title'
			);
		}

		if ($data['teacher_text_tba'] || empty($data['teacher_text'])) {
			$html .= HtmlGen::div_wrap(
				esc_html('Instructor TBA'),
				array('workshop_teacher', 'tba')
			);
		} else {
			$html .= HtmlGen::div_wrap(
				esc_html($data['teacher_text']),
				'workshop_teacher'
			);
		}

		if ($data['level_tba']) {
			$html .= HtmlGen::div_wrap(
				esc_html('Level TBA'),
				array('workshop_level', 'tba')
			);
		} elseif (! empty($data['level'])) {
			$html .= HtmlGen::div_wrap(
				esc_html($data['level']),
				'workshop_level'
			);
		}

		$html .= HtmlGen::div_wrap('', 'tv_style_shader');

		$classes = array(
			"schedule_event",
			self::_span_class($span),
			"tv_button",
			"tv_stylize"
		);

		echo HtmlGen::elem(
			'div',
			array_merge(
				array('class' => HtmlGen::classes($classes)),
				self::_workshop_attrs($group, $id)
			)
		);
		echo $html;
		echo '</div>';
	}

	public static function workshop_details($group, $id) {
		$data = self::_get_data('class', $id);
		$html = '';

		// Workshop title
		if ($data['title_tba'] || empty($data['title'])) {
			$html .= HtmlGen::div_wrap(
				esc_html('Class TBA'),
				array('workshop_title', 'tba')
			);
		} else {
			$html .= HtmlGen::div_wrap(
				esc_html($data['title']),
				'workshop_title'
			);
		}

		$html .= esc_html(' with ');

		if ($data['teacher_text_tba'] || empty($data['teacher_text'])) {
			$html .= HtmlGen::div_wrap(
				esc_html('Instructor TBA'),
				array('workshop_teacher', 'tbd')
			);
		} else {
			$html .= HtmlGen::div_wrap(
				esc_html($data['teacher_text']),
				'workshop_teacher'
			);
		}

		$html = HtmlGen::div_wrap($html, 'workshop_details_top');

		if ($data['description_tba'] || empty($data['description'])) {
			$html .= HtmlGen::div_wrap(
				esc_html('Description TBA'),
				array('workshop_description', 'tbd')
			);
		} else {
			$html .= HtmlGen::div_wrap(
				esc_html($data['description']),
				'workshop_description'
			);
		}

		if ($data['level_tba']) {
			$html .= HtmlGen::div_wrap(
				esc_html('Level: TBA'),
				array('workshop_level', 'tbd')
			);
		} elseif (! empty($data['level'])) {
			$html .= HtmlGen::div_wrap(
				esc_html('Level: ' . $data['level']),
				'workshop_level'
			);
		}

		$html .= HtmlGen::div_wrap(
			esc_html('[close]'),
			array('close_button', 'tv_button')
		);

		$classes = array(
			"workshop_details",
			"gridspan",
			"tv_toggle_vis",
			"sync_scroll",
		);
		echo HtmlGen::elem(
			'div',
			array_merge(
				array('class' => HtmlGen::classes($classes)),
				self::_workshop_attrs($group, $id)
			)
		);
		echo $html;
		echo '</div>';
	}

	private static function _local_path_url($local_path, $version=true) {
		$url = get_stylesheet_directory_uri() . '/' . $local_path;
		if ($version) {
			return add_query_arg('v', wp_get_theme()->version, $url);
		}
		else {
			return $url;
		}
	}

	public static function js_include_once($local_path) {
		if (in_array($local_path, self::$_js_included)) {
			return false;
		}

		self::$_js_included[] = $local_path;

		echo HtmlGen::eelem(
			'script',
			array(
				'type' => 'text/javascript',
				'src' => self::_local_path_url($local_path)
			)
		);

		return true;
	}


	public static function split_sections($content) {
		$lines = preg_split('/(\r\n?|\n)+/', $content);

		$scount = 0;
		$sections = array();
		foreach ($lines as $line) {
			$fc = $line[0];
			if ($fc == '#') {
				$sections[] = $line;
				$scount++;
			} elseif ($scount >= 1) {
				$sections[$scount-1] = $sections[$scount-1] . "\n" . $line;
			} elseif (trim($line) != '') {
				echo sweetmo_internal_error_html('Improper schedule markup');
				return array();
			}
		}

		return $sections;
	}

	public static function handle_sections($sections) {
		$group = self::new_grp_id();
		foreach ($sections as $section) {

			$sc = $section[1];
			if ($sc == '@') {
				$group = self::new_grp_id();
				self::handle_time_section($section);
			} elseif ($sc == '#') {
				$group = self::new_grp_id();
				self::handle_header_section($section);
			} elseif ($sc == ';') {
				// pass. It's a comment.
			} elseif ($sc == 'c' && strncmp($section, '#classes', 8) == 0) {
				self::handle_classes_section($section, $group);
			} elseif ($sc == 'b' && strncmp($section, '#band', 5) == 0) {
				self::handle_band_section($section);
			} elseif ($sc == 'p' && strncmp($section, '#performance', 12) == 0) {
				self::handle_performance_section($section);
			} elseif ($sc == 'e' && strncmp($section, '#event', 6) == 0) {
				self::handle_event_section($section);
			} else {
				echo sweetmo_internal_error_html('Unknown section');
				return false;
			}

		}
		return true;
	}

	public static function handle_header_section($section) {
		// remove '##' from leading part and trim
		$section = trim(substr($section, 2));

		$title = $loc = $note = null;
		$header = explode('@', $section, 2);
		$title = self::maybe_esc_html(trim($header[0]));
		if (! empty($header[1])) {
			$header = explode(',', $header[1], 2);
			$loc = trim($header[0]);
			$note = self::maybe_esc_html(trim($header[1]));
		}

		self::header($title, $loc, $note);
	}

	public static function handle_time_section($section) {
		// remove '#@'
		$section = substr($section, 2);

		$times = explode('-', $section, 2);
		$start = trim($times[0]);
		$end = trim($times[1]);

		$subtime = false;
		if ($start[0] == '>') {
			$start = substr($start, 1);
			$subtime = true;
		}

		$start = self::maybe_esc_html($start);
		$end = self::maybe_esc_html($end);
		self::time($start, $end, $subtime);
	}

	public static function handle_classes_section($section, $group) {
		// remove '#classes'
		$section = trim(substr($section, 8));
		$span = '1';

		if ($section[0] == '[') {
			$parts = explode(']', $section);
			$span = substr($parts[0], 1);
			$section = trim($parts[1]);
		}

		$classes = explode(',', $section);

		foreach ($classes as $idx => $class) {
			$classes[$idx] = $class = trim($class);
			self::workshop($group, $class, $span);
		}

		foreach ($classes as $class) {
			self::workshop_details($group, $class);
		}
	}

	public static function handle_event_section($section) {
		// remove '#event'
		$section = trim(substr($section, 6));
		$span = '1';

		if ($section[0] == '[') {
			$parts = explode(']', $section);
			$span = substr($parts[0], 1);
			$section = trim($parts[1]);
		}

		self::event($section, $span);
	}

	public static function handle_band_section($section) {
		// remove '#band'
		$section = trim(substr($section, 5));
		$span = '1';

		if ($section[0] == '[') {
			$parts = explode(']', $section);
			$span = substr($parts[0], 1);
			$section = trim($parts[1]);
		}

		// $span currently unused.
		self::live_music($section);
	}

	public static function handle_performance_section($section) {
		// remove '#performance'
		$section = trim(substr($section, 12));
		$span = '1';

		if ($section[0] == '[') {
			$parts = explode(']', $section);
			$span = substr($parts[0], 1);
			$section = trim($parts[1]);
		}

		self::performance($section, $span);
	}
}

1;
