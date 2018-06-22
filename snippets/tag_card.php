<?php

/*
 * Expects the following variables to be defined:
 *  - $tag_target: A short string (MyDataShortCode id) of a biography
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

$data = MDSC::instance()->data->get_data('tags')[$tag_target];
$name = $data['name'];
if ($data['shortname']) {
    $name .= ' ("' . $data['shortname'] . '")';
}

echo '<div class="smb_bio_card '.esc_attr($class).'">';
echo '<div>';

echo HtmlGen::div_wrap(esc_html($name), '_nom');

if (! empty($data['description'])) {
    // bio is richtext (already HTML)
    echo HtmlGen::div_wrap($data['description'], '_bio');
}

echo '</div></div>';
