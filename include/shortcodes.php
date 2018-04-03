<?php

require_once('snippets.php');

function sweetmo_setup_shortcodes() {
    $codes = array('bio', 'schedule', 'venue');

    foreach ($codes as $codename) {
        $shortcode = "smb_$codename";
        $function = "sweetmo_sc_smb_$codename";
        add_shortcode($shortcode, $function);
    }
}

function sweetmo_sc_smb_bio($attrs = null, $content = null, $tag = null){
	if (! $attrs) { $attrs = []; }
	if (! $content) { return ''; }

    $args = array(
        'bio_target' => $content,
        'class' => isset($attrs['class']) ? $attrs['class'] : ''
    );
    return sweetmo_snippet('bio_card', $args);
}

function sweetmo_sc_smb_venue($attrs = null, $content = null, $tag = null){
	if (! $attrs) { $attrs = []; }
	if (! $content) { return ''; }

    $args = array(
        'venue_target' => $content,
        'class' => isset($attrs['class']) ? $attrs['class'] : ''
    );
    return sweetmo_snippet('venue_card', $args);
}

function sweetmo_sc_smb_schedule($attrs = null, $content = null, $tag = null){
    return sweetmo_snippet('schedule');
}
