<?php
/**
 * Template Name: Schedule
 *
 */

remove_action( 'omega_after_main', 'omega_primary_sidebar' );

function tpl_schedule_content_replacement($ign) {
	return get_template_part('php_pages/schedule');
}
add_filter( 'the_content', 'tpl_schedule_content_replacement', 99);

get_header(); ?>

	<main  class="<?php echo omega_apply_atomic( 'main_class', 'content' );?>" <?php omega_attr( 'content' ); ?>>

		<?php
		do_action( 'omega_before_content' );

		do_action( 'omega_content' );

		do_action( 'omega_after_content' );
		?>

	</main><!-- .content -->

<?php get_footer(); ?>
