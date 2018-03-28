<?php

function sweetmo_snippet(
    $__snippet_name,
    $__snippet_args=null,
    $__snippet_capture=true)
{
//	set_query_var('__bio_card_target', $content);

    $__snippet_results = null;

    if ($__snippet_capture) {
        ob_start();
    }

    try {
        if ($__snippet_args) {
            extract($__snippet_args);
        }
        require(sweetmo_snippet_name_to_path($__snippet_name));
        if ($__snippet_capture) {
            $__snippet_results = ob_get_contents();
        }
    } finally {
        if ($__snippet_capture) {
            ob_end_clean();
        }
    }

    return $__snippet_results;
}

function sweetmo_snippet_name_to_path($shortname) {
    $name = "/snippets/$shortname.php";
    $path = locate_template(array($name));
    // TODO: if ! $path
    return $path;
}
