<?php

/*
 * Expects the following variables to be defined:
 *  - $bio_target: A short string (MyDataShortCode id) of a biography
 *  - $class: A string of additional class names to put on the wrapping div
 */

if ( ! class_exists('MDSC') ) {
    echo sweetmo_internal_error_html(
        'MDSC class unavailable.'
    );
    return;
}

if ( ! class_exists('HtmlGen') ) {
    echo sweetmo_internal_error_html(
        'HtmlGen class unavailable.'
    );
    return;
}

echo '<div class="smb_bio_card '.esc_attr($class).'">';

// TODO: what to do if persons isn't given
$data = MDSC::instance()->data->get_data('venue')[$venue_target];

if ($data['img'] && ! $data['img_tba']) {
    echo HtmlGen::celem('img', array('src' => $data['img']));
}

echo '<div>';

$name = $data['name'];
if ($data['shortname']) {
    $name .= ' ("' . $data['shortname'] . '")';
}

echo HtmlGen::div_wrap(esc_html($name), '_nom');

if ($data['events']) {
    echo HtmlGen::div_wrap(esc_html($data['events']), '_role');
}

if ($data['content'] && ! $data['content_tba']) {
    // bio is richtext (already HTML)
    echo HtmlGen::div_wrap($data['content'], '_bio');
}

echo '</div></div>';
