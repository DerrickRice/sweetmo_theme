<?php

require_once('include/shortcodes.php');

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

	sweetmo_setup_shortcodes();
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
		$out .= '<img class="header-image" ';
		$out .= 'src="' . esc_url($img_src) . '" ';
		$out .= 'alt="' . esc_attr($title) . '" ';
		$out .= '></img>';
		$out .= '<div class="header-title">' . esc_html($title) . '</div>';
	} else {
		$out .= esc_html($title);
	}
	$out .= '</a></div>';

	// wants to be echoed
	echo $out;
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
