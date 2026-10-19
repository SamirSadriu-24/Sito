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

	<?php $onthewall_has_price = isset( $onthewall_artwork['price'] ) && '' !== (string) $onthewall_artwork['price']; ?>
	<?php if ( ! empty( $onthewall_artwork['technique'] ) || $onthewall_has_price ) : ?>
		<div class="card-meta-row">
			<?php if ( ! empty( $onthewall_artwork['technique'] ) ) : ?>
				<p class="font-mono text-[11px] uppercase tracking-[0.15em] text-muted-foreground">
					<?php echo esc_html( $onthewall_artwork['technique'] ); ?>
				</p>
			<?php endif; ?>
			<?php if ( $onthewall_has_price ) : ?>
				<span class="ml-auto font-mono text-[11px] font-bold tracking-[0.15em] whitespace-nowrap text-primary">
					<?php echo esc_html( "€\u{00A0}" . number_format_i18n( (int) $onthewall_artwork['price'] ) ); ?>
				</span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

</article>
