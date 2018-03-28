<?php

/*
 * Expects the following variables to be defined:
 *  - $bio_target: A short string (MyDataShortCode id) of a biography
 *  - $class: A string of additional class names to put on the wrapping div
 */

echo '<div class="smb_bio_card '.esc_attr($class).'">';
echo esc_html($bio_target);
echo '</div>';
