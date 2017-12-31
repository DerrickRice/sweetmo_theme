<?php
/**
 * Template Name: Full-width
 *
 */

remove_action( 'omega_after_main', 'omega_primary_sidebar' );

get_header(); ?>

	<main  class="<?php echo omega_apply_atomic( 'main_class', 'content' );?>" <?php omega_attr( 'content' ); ?>>

		<?php 
		do_action( 'omega_before_content' ); 

		do_action( 'omega_content' ); 

		do_action( 'omega_after_content' ); 
		?>

	</main><!-- .content -->

<?php get_footer(); ?>
