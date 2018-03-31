<?php

/**
 * Invoke the named snippet with given args.
 * @param  string  $__snippet_name    The snippet shortname. e.g. 'bio_card'
 * @param  array   $__snippet_args    Variables to have available within the snippet (default null)
 * @param  boolean $__snippet_capture Whether to return the snippet result (default) or directly echo it.
 * @return string                     Snippet results, or null if $__snippet_capture is false.
 */
function sweetmo_snippet(
    $__snippet_name,
    $__snippet_args=null,
    $__snippet_capture=true)
{
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

function sweetmo_internal_error_html($text_content) {
    // Do not use any external dependencies here.
    // We might be reporting on a dependency error.
    $rv = '<div class="server_error">';
    $rv .= esc_html('Internal server error: ' . $text_content);
    $rv .= '</div>';
    return $rv;
}
