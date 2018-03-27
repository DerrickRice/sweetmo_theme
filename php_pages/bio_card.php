<?php

$target = get_query_var('__bio_card_target');

echo '<div id="_bct">';
echo esc_html($target);
echo '</div>';
