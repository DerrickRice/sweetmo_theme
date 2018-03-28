<?php

class Schedule {
	public static function ready() {
		return self::_ready_mdsc();
	}

	private static function _ready_mdsc() {
		if ( ! class_exists('MDSC') ) {
			self::_internal_error('MDSC class unavailable.');
			return false;
		}

		return true;
	}

	private static function _internal_error($text) {
		echo self::_div_html(
			array('server_error'),
			esc_html('Internal server error: ' . $text)
		);
	}

	public static function section($text, $anchor) {
		echo '<a name="' . esc_attr($anchor) . '"></a>';
		echo '<h2>' . esc_html($text) . '</h2>';
	}

	public static function header($event) {
		echo self::_div_html(array('schedule_header'), esc_html($event));
	}

	public static function time(
		$start,
		$end,
		$name=null,
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

		echo self::_div_html($classes, $html, true);
	}

	public static function event($name, $span='1') {
		self::_event_impl(esc_html($name), $span);
	}

	private static function _event_impl($html, $span='1') {
		echo self::_div_html(
			array("schedule_event", self::_span_class($span)),
			$html
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

	private static function _div_html(
		$classes,
		$html,
		$extra_div=false,
		$attrs=null
	) {
		if (! $attrs) {
			$attrs = array();
		}
		if ($classes) {
			$attrs['class'] = implode(' ', $classes);
		}
		$rv = '<div ';
		foreach ($attrs as $aname => $avalue) {
			$rv .= $aname . '="' . esc_attr($avalue) . '" ';
			# code...
		}
		$rv .= '>';

		if ($extra_div) {
			$rv .= '<div>';	// needed for vertical alignment
		}

		$rv .= $html;

		if ($extra_div) {
			$rv .= '</div>';
		}
		$rv .= '</div>';
		return $rv;
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
			$html .= self::_div_html(
				array('workshop_title', 'tbd'),
				esc_html('Class TBA')
			);
		} else {
			$html .= self::_div_html(
				array('workshop_title'),
				esc_html($data['title'])
			);
		}

		if ($data['teacher_text_tba'] || empty($data['teacher_text'])) {
			$html .= self::_div_html(
				array('workshop_teacher', 'tbd'),
				esc_html('Instructor TBA')
			);
		} else {
			$html .= self::_div_html(
				array('workshop_teacher'),
				esc_html($data['teacher_text'])
			);
		}

		if ($data['level_tba']) {
			$html .= self::_div_html(
				array('workshop_level', 'tbd'),
				esc_html('Level TBA')
			);
		} elseif (! empty($data['level'])) {
			$html .= self::_div_html(
				array('workshop_level'),
				esc_html($data['level'])
			);
		}

		$html .= self::_div_html(array('tv_style_shader'), '');

		echo self::_div_html(
			array(
				"schedule_event",
				self::_span_class($span),
				"tv_button",
				"tv_stylize"
			),
			$html,
			false,
			self::_workshop_attrs($id)
		);
	}

	public static function workshop_details($id) {
		$data = self::_get_data('class', $id);
		$html = '';

		// Workshop title
		if ($data['title_tba'] || empty($data['title'])) {
			$html .= self::_div_html(
				array('workshop_title', 'tbd'),
				esc_html('Class TBA')
			);
		} else {
			$html .= self::_div_html(
				array('workshop_title'),
				esc_html($data['title'])
			);
		}

		$html .= esc_html(' with ');

		if ($data['teacher_text_tba'] || empty($data['teacher_text'])) {
			$html .= self::_div_html(
				array('workshop_teacher', 'tbd'),
				esc_html('Instructor TBA')
			);
		} else {
			$html .= self::_div_html(
				array('workshop_teacher'),
				esc_html($data['teacher_text'])
			);
		}

		$html = self::_div_html(
			array('workshop_details_top'),
			$html
		);

		if ($data['description_tba'] || empty($data['description'])) {
			$html .= self::_div_html(
				array('workshop_description', 'tbd'),
				esc_html('Description TBA')
			);
		} else {
			$html .= self::_div_html(
				array('workshop_description'),
				esc_html($data['description'])
			);
		}

		if ($data['level_tba']) {
			$html .= self::_div_html(
				array('workshop_level', 'tbd'),
				esc_html('Level: TBA')
			);
		} elseif (! empty($data['level'])) {
			$html .= self::_div_html(
				array('workshop_level'),
				esc_html('Level: ' . $data['level'])
			);
		}

		$html .= self::_div_html(
			array('close_button', 'tv_button'),
			esc_html('[close]')
		);

		echo self::_div_html(
			array("workshop_details", "gridspan", "tv_toggle_vis"),
			$html,
			false,
			self::_workshop_attrs($id)
		);
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
		echo '<script type="text/javascript" src="';
		echo esc_attr(self::_local_path_url($local_path)) . '" ></script>';
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
		Schedule::header("Blues Union");
			Schedule::time("7:30", "8:30");
				Schedule::event("Beginner Lesson");
			Schedule::time("8:30", "9:30");
				Schedule::event("Intermediate+ Lesson");
			Schedule::time("9:30", "12:00");
				Schedule::event("Social dance");
	 	?>
	</div>

<?php Schedule::section("Friday Evening", "fridayeve"); ?>

<div class="schedule_grid schedule_grid1">
	<?php
	Schedule::header("Social Dance at WCYC");
		Schedule::time("8:45", "11:45");
			Schedule::event("Live Music with TBA");
		Schedule::time(null, null, null, false, true);
			Schedule::event("Band breaks DJ'd by TBD");
		Schedule::time("9:30", null, "First band break", true, true);
			Schedule::event("Performance by TBD");
		Schedule::time("10:30", null, "Second band break", true, true);
			Schedule::event("Performance by TBD");
	Schedule::header("Late Night at TBA");
		Schedule::time("12:00", "3:30");
			Schedule::event("DJ'd music by TBA");
		Schedule::time("1:30", null, null, true, false);
			Schedule::event("Performance by TBD");
	?>
</div>

<?php Schedule::section("Saturday", "saturday"); ?>

<div class="schedule_grid schedule_grid4">
	<?php
	Schedule::header("Workshops at MIT");
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
	Schedule::header("Social Dance at WCYC");
		Schedule::time("8:45", "11:45");
			Schedule::event("Live Music with TBA");
		Schedule::time(null, null, null, false, true);
			Schedule::event("Band breaks DJ'd by TBD");
		Schedule::time("9:30", null, "First band break", true, true);
			Schedule::event("Performance by TBD");
		Schedule::time("10:30", null, "Second band break", true, true);
			Schedule::event("Performance by TBD");
	Schedule::header("Late Night at TBA");
		Schedule::time("12:00", "4:30");
			Schedule::event("DJ'd music by TBA");
		Schedule::time("1:30", null, null, true, false);
			Schedule::event("Performance by TBD");
	?>
</div>

<?php Schedule::section("Sunday", "sunday"); ?>

<div class="schedule_grid schedule_grid4">
	<?php
	Schedule::header("Workshops at MIT");
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
	Schedule::header("BBQ Dinner at WCYC");
		Schedule::time("7:00", "8:30");
	Schedule::header("Social Dance at WCYC");
		Schedule::time("8:30", "11:45");
			Schedule::event("Live Music with TBA");
		Schedule::time(null, null, null, false, true);
			Schedule::event("Band breaks DJ'd by TBD");
		Schedule::time("9:30", null, "First band break", true, true);
			Schedule::event("Performance by TBD");
		Schedule::time("10:30", null, "Second band break", true, true);
			Schedule::event("Performance by TBD");
	Schedule::header("Late Night at TBA");
		Schedule::time("12:00", "4:00");
			Schedule::event("DJ'd music by TBA");
		Schedule::time("1:30", null, null, true, false);
			Schedule::event("Performance by TBD");
	?>
</div>
