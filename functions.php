<?php

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
function sweetmo_theme_setup() {
	add_theme_support(
		'custom-header',
		array(
			'header-text' 	=> false,
			'flex-width'    => true,
			'uploads'       => true,
			'default-image' => get_stylesheet_directory_uri() . '/images/header.jpg'
		)
	);

	// Do not show a title or description.
	remove_action( 'omega_header', 'omega_branding' );

	// Put the primary menu within the header, rather than above it.
	remove_action( 'omega_before_header', 'omega_get_primary_menu' );
	add_action( 'omega_header', 'omega_get_primary_menu' );

	// Use our custom title block, which is just an image (with alt text)
	add_action( 'omega_after_header', 'sweetmo_intro' );

	// Theme CSS and JavaScript
	add_action( 'wp_enqueue_scripts', 'sweetmo_css_and_js' );

	add_shortcode('smb_bio', 'handle_smb_bio_shortcode');
}

add_action( 'after_setup_theme', 'sweetmo_theme_setup', 11 );


/**
 * Loads the intro.php template.
 */
function sweetmo_intro() {
	$title = get_bloginfo('name');
	$img_src = get_header_image();

	$out = '<div class="site-intro">';
	$out .= '<a href="' . esc_url(home_url()) . '">';
	if ($img_src) {
		$out .= '<img class="header-image" src="';
		$out .= esc_url($img_src);
		$out .= '" alt="';
		$out .= esc_attr($title);
		$out .= '" />';
	} else {
		$out .= esc_html($title);
	}
	$out .= '</a></div>';

	// wants to be echoed
	echo $out;
}

function handle_smb_bio_shortcode($attrs = null, $content = null, $tag = null){
	if (! $attrs) { $attrs = []; }
	if (! $content) { return ''; }

	set_query_var('__bio_card_target', $content);
	ob_start();
	get_template_part('php_pages/bio_card');
	$results = ob_get_contents();
	ob_end_clean();
	return $results;
}

function sweetmo_css_and_js() {
	/* Omega doesn't put a version on the URI which is maddening. Fixing it. */
	wp_dequeue_style('omega-style');
 	wp_enqueue_style(
 		'sweetmo-style',
 		get_stylesheet_uri(),
 		array(),
 		wp_get_theme()->version
 	);

 	wp_enqueue_style(
		'sweetmo-open-sans',
		google_fonts_open_sans_css_url(),
		array(),
		wp_get_theme()->version
	);

 	wp_enqueue_script(
		'jquery-superfish',
		get_stylesheet_directory_uri() . '/js/menu.js',
		array('jquery'),	// depends on jQuery, which comes from elsewhere
		'1.0.0',			// superfish version
		true				// not really sure why we want this in the footer
	);

 	wp_enqueue_script(
		'sweetmo-init',
		get_stylesheet_directory_uri() . '/js/init.js',
		array('jquery'),
		wp_get_theme()->version
	);
}

function google_fonts_open_sans_css_url() {
	return esc_url_raw( add_query_arg(
		array(
			'family' => urlencode( 'Open Sans:400,600,700' ),
			'subset' => urlencode( 'latin,latin-ext' ),
		),
		'https://fonts.googleapis.com/css'
	) );
}
