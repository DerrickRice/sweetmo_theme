<?php

class Schedule {
	public static function header2($text, $anchor) {
		echo '<a name="'.$anchor.'"></a><h2>'.$text.'</h2>';
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

<?php
Schedule::header2("Thursday Evening", "thursdayeve");

Schedule::header2("Friday Evening", "fridayeve");

Schedule::header2("Saturday", "saturday");

Schedule::header2("Saturday Evening", "saturdayeve");

Schedule::header2("Sunday", "sunday");

Schedule::header2("Sunday Evening", "sundayeve");

?>
