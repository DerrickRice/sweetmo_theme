<?php

class Schedule {
	public static function section($text, $anchor) {
		echo '<a name="' . esc_attr($anchor) . '"></a>';
		echo '<h2>' . esc_html($text) . '</h2>';
	}

	public static function header($event) {
		echo '<div class="schedule_header">' . esc_html($event) . '</div>';
	}

	public static function time($start, $end) {
		$text = $start . ' to ' . $end;
		echo '<div class="grid1col schedule_time">' . esc_html($text) . '</div>';
	}

	public static function event($name) {
		echo '<div class="grid1col schedule_event">' . esc_html($event) . '</div>';
	}
}

?>

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
		Schedule::time("8:30", "9:30");
		Schedule::time("9:30", "12:00");

 	?>
	<div class="schedule_header">
		Blues Union
	</div>
</div>

<?php Schedule::section("Friday Evening", "fridayeve"); ?>

Schedule::section("Saturday", "saturday"); ?>

Schedule::section("Saturday Evening", "saturdayeve");

Schedule::section("Sunday", "sunday");

Schedule::section("Sunday Evening", "sundayeve");

?>
