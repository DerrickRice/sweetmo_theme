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
$data = MDSC::instance()->data->get_data('person')[$bio_target];

if ($data['img'] && ! $data['img_tba']) {
    echo HtmlGen::celem('img', array('src' => $data['img']));
}

echo '<div>';

echo HtmlGen::div_wrap(esc_html($data['name']), '_nom');

if ($data['role']) {
    echo HtmlGen::div_wrap(esc_html($data['role']), '_role');
}

if ($data['bio'] && ! $data['bio_tba']) {
    // bio is richtext (already HTML)
    echo HtmlGen::div_wrap($data['bio'], '_bio');
}

echo '</div></div>';
