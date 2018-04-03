<?php

@include_once($WP_PLUGIN_DIR . '/my_data_shortcodes/includes/html.php');
@include_once($WP_PLUGIN_DIR . '/my_data_shortcodes/includes/data.php');

class Schedule {
	public static function ready() {
		if ( ! class_exists('MDSC') ) {
			echo sweetmo_internal_error_html(
				'MDSC class unavailable.'
			);
			return false;
		}

		if ( ! class_exists('HtmlGen') ) {
			echo sweetmo_internal_error_html(
				'HtmlGen class unavailable.'
			);
			return false;
		}

		return true;
	}

	public static function section($text, $anchor) {
		echo HtmlGen::eelem('a', array('name' => $anchor));
		echo '<h2>' . esc_html($text) . '</h2>';
	}

	/*
	 * The header of a schedule grid. Usually indicates the beginning of the
	 * event, such as the main dance or workshops.
	 */
 	public static function header($event, $venueid=null, $note=null) {
		// venue shortname
		// venue longname
		// $EVENT at $VENUE_SHORTNAME<br/>note
		$title = esc_html($event);

		$venueinfo = self::_venue_link_html($venueid);
		if ($venueinfo) {
			$title .= " at $venueinfo";
		}

		$html = HtmlGen::div_wrap(esc_html($title), 'event');

		if ($note) {
			$html .= HtmlGen::div_wrap(esc_html($note), 'note');
		}

 		echo HtmlGen::div_wrap($html, 'schedule_header');
 	}

	private static function _venue_link_html($venueid) {
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
		$approx=false,
		$sub=false
	) {
		$classes = array('grid1col', 'schedule_time');
		if ($sub) {
			$classes[] = 'schedule_subtime';
		}

		if ($end) {
			$time_text = $start . ' to ' . $end;
		} else if ($start) {
			$time_text = $start;
		} else {
			$time_text = '';
		}

		if ($approx) {
			$time_text = '~ ' . $time_text;
		}

		$html = '';
		if ($name) {
			$html .= esc_html($name) . '<br/>';
		}
		$html .= esc_html($time_text);

		// need an extra inner div to vertically align things
		$html = HtmlGen::div_wrap($html);
		echo HtmlGen::div_wrap($html, $classes);
	}

	public static function live_music($id) {
		// band has a name, shrotname, bio, photo, and band-break-DJ-id
		// "Live music (TBA) by $id and breaks DJ'd by __TODO__";
		self::event("Live music (TBA)");
	}

	public static function event($name, $span='1') {
		echo HtmlGen::div_wrap(
			esc_html($name),
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
		assert(in_array($span, array('1', '4')));
		return "grid{$span}col";
	}

	private static function _workshop_attrs($id) {
		$group = $id;

		$parts = explode('.',$id);
		if (sizeof($parts) > 1 ) {
			$group = $parts[0];
		}

		return array(
			'data-workshop' => $id,
			'data-workshop-group' => $group
		);
	}

	public static function workshop($id, $span='1') {
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
				self::_workshop_attrs($id)
			)
		);
		echo $html;
		echo '</div>';
	}

	public static function workshop_details($id) {
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
				self::_workshop_attrs($id)
			)
		);
		echo $html;
		echo '</div>';
	}

	public static function workshops(...$ids){
		foreach ($ids as $id) {
			self::workshop($id);
		}

		foreach ($ids as $id) {
			self::workshop_details($id);
		}
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
	public static function js_include($local_path) {
		echo HtmlGen::eelem(
			'script',
			array(
				'type' => 'text/javascript',
				'src' => self::_local_path_url($local_path)
			)
		);
	}
}

if ( ! Schedule::ready() ) {
	return;
}

?>

<?php Schedule::js_include('js/schedule.js'); ?>

Jump to:
<ul>
	<li><a href="#thursdayeve">Thursday Evening</a></li>
	<li><a href="#fridayeve">Friday Evening</a></li>
	<li><a href="#saturday">Saturday</a></li>
	<li><a href="#saturdayeve">Saturday Evening</a></li>
	<li><a href="#sunday">Sunday</a></li>
	<li><a href="#sundayeve">Sunday Evening</a></li>
</ul>
<?php Schedule::section("Thursday Evening", "thursdayeve"); ?>
	<div class="schedule_grid schedule_grid1">
		<?php
		Schedule::header("Social Dance", 'bluesunion', 'Sold Separately');
			Schedule::time("7:30", "8:30");
				Schedule::event("Beginner Lesson");
			Schedule::time("8:30", "9:30");
				Schedule::event("Intermediate+ Lesson");
			Schedule::time("9:30", "12:00");
				Schedule::live_music("band0");
	 	?>
	</div>

<?php Schedule::section("Friday Evening", "fridayeve"); ?>

<div class="schedule_grid schedule_grid1">
	<?php
	Schedule::header("Social Dance", 'wcyc');
		Schedule::time("8:45", "11:45");
			Schedule::live_music("band1");
		Schedule::time("9:30", null, true, true);
			Schedule::performance(null);
		Schedule::time("10:30", null, true, true);
			Schedule::performance(null);
	Schedule::header("Late Night", 'tbd');
		Schedule::time("12:00", "3:30");
			Schedule::event("DJ'd music by TBA");
		Schedule::time("1:30", null, true, true);
			Schedule::event("Performance by TBD");
	?>
</div>

<?php Schedule::section("Saturday", "saturday"); ?>

<div class="schedule_grid schedule_grid4">
	<?php
	Schedule::header("Workshops", 'mit');
		Schedule::time("10:30", "11:30");
			Schedule::workshops("sat1.r1", "sat1.r2", "sat1.r3", "sat1.r4");
		Schedule::time("11:45", "12:45");
			Schedule::workshops("sat2.r1", "sat2.r2", "sat2.r3", "sat2.r4");
		Schedule::time("12:45", "1:15");
			Schedule::event("Lunch Break", "4");
		Schedule::time("1:15", "2:15");
			Schedule::workshop("sat3.r0", '4');
			Schedule::workshop_details("sat3.r0");
		Schedule::time("2:25", "3:25");
			Schedule::workshops("sat4.r1", "sat4.r2", "sat4.r3", "sat4.r4");
		Schedule::time("3:40", "4:40");
			Schedule::workshops("sat5.r1", "sat5.r2", "sat5.r3", "sat5.r4");
		Schedule::time("4:50", "5:50");
			Schedule::event("M&M Prelims", "4");
	?>
</div>

<?php Schedule::section("Saturday Evening", "saturdayeve"); ?>

<div class="schedule_grid schedule_grid1">
	<?php
	Schedule::header("Social Dance", 'wcyc');
		Schedule::time("8:45", "11:45");
			Schedule::live_music("band2");
		Schedule::time("9:30", null, true, true);
			Schedule::performance(null);
		Schedule::time("10:30", null, true, true);
			Schedule::event("Choreography Competition Finals");
	Schedule::header("Late Night", 'tbd');
		Schedule::time("12:00", "4:30");
			Schedule::event("DJ'd music by TBA");
		Schedule::time("1:00", null, true, true);
			Schedule::event("M&M Competition Finals");
	?>
</div>

<?php Schedule::section("Sunday", "sunday"); ?>

<div class="schedule_grid schedule_grid4">
	<?php
	Schedule::header("Workshops", 'mit');
		Schedule::time("10:30", "11:30");
			Schedule::workshops("sun1.r1", "sun1.r2", "sun1.r3", "sun1.r4");
		Schedule::time("11:45", "12:45");
			Schedule::workshops("sun2.r1", "sun2.r2", "sun2.r3", "sun2.r4");
		Schedule::time("12:45", "1:15");
			Schedule::event("Lunch Break", "4");
		Schedule::time("1:15", "2:15");
			Schedule::workshop("sun3.r0", '4');
		Schedule::time("2:25", "3:25");
			Schedule::workshops("sun4.r1", "sun4.r2", "sun4.r3", "sun4.r4");
		Schedule::time("3:40", "4:40");
			Schedule::workshops("sun5.r1", "sun5.r2", "sun5.r3", "sun5.r4");
	?>
</div>

<?php Schedule::section("Sunday Evening", "sundayeve"); ?>

<div class="schedule_grid schedule_grid1">
	<?php
	Schedule::header("BBQ Dinner", 'wcyc');
		Schedule::time("7:00", "8:30");
			Schedule::event("Sold Separately");
	Schedule::header("Social Dance", 'wcyc');
		Schedule::time("8:30", "11:45");
			Schedule::live_music("band3");
		Schedule::time("9:30", null, true, true);
			Schedule::performance(null);
		Schedule::time("10:30", null, true, true);
			Schedule::performance(null);
	Schedule::header("Late Night", 'tbd');
		Schedule::time("12:00", "4:00");
			Schedule::event("DJ'd music by TBA");
	?>
</div>
