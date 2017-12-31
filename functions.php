<?php

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 */
function sweetmo_theme_setup() {

	remove_action( 'omega_before_header', 'omega_get_primary_menu' );	
	add_action( 'omega_header', 'omega_get_primary_menu' );

	add_theme_support(
		'custom-header',
		array( 'header-text' => false,
			'flex-width'    => true,
			'uploads'       => true,
			'default-image' => get_stylesheet_directory_uri() . '/images/header.jpg'
			));

	add_action( 'omega_after_header', 'sweetmo_intro' );

	add_filter( 'omega_site_description', 'sweetmo_site_description' );

	add_action( 'wp_enqueue_scripts', 'sweetmo_scripts_styles' );

	load_child_theme_textdomain( 'sweetmo', get_stylesheet_directory() . '/languages' );
}

add_action( 'after_setup_theme', 'sweetmo_theme_setup', 11 );

/**
 * Loads the intro.php template.
 */
function sweetmo_intro() {
	if (get_header_image()) {
		echo '<div class="site-intro"><img class="header-image" src="' . esc_url( get_header_image() ) . '" alt="' . esc_html(get_bloginfo( 'description' )) . '" /></div>';
	}
}

/**
 * disable site description
 */
function sweetmo_site_description($desc) {
	$desc = "";
	return $desc;
}

/**
 * Enqueue Scripts and Google fonts
 */
function sweetmo_scripts_styles() {
 	wp_enqueue_style('sweetmo-fonts', sweetmo_fonts_url(), array(), null );
 	wp_enqueue_script('jquery-superfish', get_stylesheet_directory_uri() . '/js/menu.js', array('jquery'), '1.0.0', true );
 	wp_enqueue_script('sweetmo-init', get_stylesheet_directory_uri() . '/js/init.js', array('jquery'));
}

/**
 * Register custom fonts.
 */
function sweetmo_fonts_url() {
	$fonts_url = '';
	 
	/* Translators: If there are characters in your language that are not
	* supported by Lora, translate this to 'off'. Do not translate
	* into your own language.
	*/
	$satisfy = _x( 'on', 'Satisfy font: on or off', 'sweetmo' );
	 
	/* Translators: If there are characters in your language that are not
	* supported by Open Sans, translate this to 'off'. Do not translate
	* into your own language.
	*/
	$open_sans = _x( 'on', 'Open Sans font: on or off', 'sweetmo' );
	 
	if ( 'off' !== $satisfy || 'off' !== $open_sans ) {
		$font_families = array();
		 
		if ( 'off' !== $satisfy ) {
			$font_families[] = 'Satisfy:400';
		}
		 
		if ( 'off' !== $open_sans ) {
			$font_families[] = 'Open Sans:400,600,700';
		}
		 
		$query_args = array(
			'family' => urlencode( implode( '|', $font_families ) ),
			'subset' => urlencode( 'latin,latin-ext' ),
		);
		 
		$fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
	}
	 
	return esc_url_raw( $fonts_url );
}
