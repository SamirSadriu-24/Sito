<?php
/**
 * Header del sito.
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="relative min-h-screen overflow-x-hidden bg-background text-foreground">

	<div class="halftone halftone-14 pointer-events-none fixed inset-0 z-0 text-secondary opacity-[0.07]" aria-hidden="true"></div>

	<?php get_template_part( 'template-parts/header', 'site' ); ?>
