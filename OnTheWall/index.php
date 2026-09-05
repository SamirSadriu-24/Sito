<?php
/**
 * Template principale — one-page portfolio.
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

get_template_part( 'template-parts/section', 'about' );
get_template_part( 'template-parts/section', 'works' );

get_footer();
