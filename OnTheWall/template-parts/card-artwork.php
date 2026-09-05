<?php
/**
 * Card di una singola opera nella griglia "The Wall".
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$onthewall_artwork = get_query_var( 'onthewall_artwork' );
$onthewall_index   = (int) get_query_var( 'onthewall_index' );

if ( empty( $onthewall_artwork ) || empty( $onthewall_artwork['image_html'] ) ) {
	return;
}

$onthewall_rotation = onthewall_card_rotation( $onthewall_index );
?>
<article class="tilt-hover col-span-12 bg-card p-3 ring-1 ring-foreground/15 sm:col-span-6 lg:col-span-4 <?php echo esc_attr( $onthewall_rotation ); ?>">

	<?php echo wp_kses_post( $onthewall_artwork['image_html'] ); ?>

	<div class="mt-3 flex items-start justify-between gap-2">
		<h3 class="font-display text-xl tracking-tight"><?php echo esc_html( $onthewall_artwork['title'] ); ?></h3>
		<?php if ( ! empty( $onthewall_artwork['size'] ) ) : ?>
			<span class="font-mono text-[11px] uppercase tracking-[0.15em] whitespace-nowrap text-muted-foreground">
				<?php echo esc_html( $onthewall_artwork['size'] ); ?>
			</span>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $onthewall_artwork['technique'] ) ) : ?>
		<p class="font-mono text-[11px] uppercase tracking-[0.15em] text-muted-foreground">
			<?php echo esc_html( $onthewall_artwork['technique'] ); ?>
		</p>
	<?php endif; ?>

</article>
