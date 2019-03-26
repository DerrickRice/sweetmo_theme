<?php
# vim: et ts=4 sw=4

require_once('snippets.php');

function sweetmo_setup_shortcodes() {
    $codes = array('bio', 'schedule', 'venue', 'tag', 'include');

    foreach ($codes as $codename) {
        $shortcode = "smb_$codename";
        $function = "sweetmo_sc_smb_$codename";
        add_shortcode($shortcode, $function);
    }
}

function sweetmo_sc_smb_include($attrs = null, $content = null, $tag = null){
    if (! $attrs) { $attrs = []; }
    if (! $content) { return ''; }

    require_once('mdsc_deps.php');

    if ( ! class_exists('MDSC') ) {
        return sweetmo_internal_error_html(
            'MDSC class unavailable.'
        );
    }

    $struct = MDSC::instance()->data->get_data('text')[$content];
    if ($struct && $struct['value']) {
        return $struct['value'];
    } else {
        return '';
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

function sweetmo_sc_smb_tag($attrs = null, $content = null, $tag = null){
    if (! $attrs) { $attrs = []; }
    if (! $content) { return ''; }

    $args = array(
        'tag_target' => $content,
        'class' => isset($attrs['class']) ? $attrs['class'] : ''
    );
    return sweetmo_snippet('tag_card', $args);
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
    if (! $attrs) { $attrs = []; }
    if (! $content) { return ''; }

    $args = array(
        'schedule_markup' => $content,
        'schedule_width' => isset($attrs['width']) ? $attrs['width'] : '1',
    );
    return sweetmo_snippet('schedule', $args);
}
