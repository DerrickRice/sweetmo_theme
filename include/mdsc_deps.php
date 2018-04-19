<?php
/* Carefully importing from My Data Shortcodes aka mdsc */

$mdsc_ipaths = [
	'my_data_shortcodes/includes/html.php',
	'my_data_shortcodes/includes/data.php',
];

foreach ($mdsc_ipaths as $ipath) {
	if (!include_once(WP_PLUGIN_DIR . '/' . $ipath)) {
		throw new Exception(
			"Unable to include $ipath " .
			"Is the My Data Shortcodes plugin installed?"
		);
	}
}

$mdsc_classes = [
    'MDSC',
    'HtmlGen'
];

foreach ($mdsc_classes as $clzz) {
    if ( ! class_exists($clzz) ) {
    	throw new Exception(
    		"$clzz class unavailable." .
    		'Is the My Data Shortcodes plugin functional?'
    	);
    }
}

1;

?>
