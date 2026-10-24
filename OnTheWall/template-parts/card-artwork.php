<?php
/**
 * Card di una singola opera nella griglia "The Wall", con la sua finestra.
 *
 * La finestra è un popover HTML nativo e le foto scorrono con lo scroll-snap
 * CSS: si apre, si chiude e si sfoglia senza JavaScript.
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

$onthewall_rotation    = onthewall_card_rotation( $onthewall_index );
$onthewall_price_label = onthewall_artwork_price_label( $onthewall_artwork );
$onthewall_photos      = ! empty( $onthewall_artwork['photos'] ) ? array_values( $onthewall_artwork['photos'] ) : array( $onthewall_artwork['image_html'] );
$onthewall_photo_count = count( $onthewall_photos );
$onthewall_box_id      = 'opera-' . ( $onthewall_index + 1 );
?>
<article class="tilt-hover col-span-12 bg-card p-3 ring-1 ring-foreground/15 sm:col-span-6 lg:col-span-4 <?php echo esc_attr( $onthewall_rotation ); ?>">

	<button type="button" class="lightbox-trigger" popovertarget="<?php echo esc_attr( $onthewall_box_id ); ?>">
		<?php echo wp_kses_post( $onthewall_artwork['image_html'] ); ?>
		<?php // aria-hidden: "SOLD" è già annunciato dall'etichetta di prezzo qui sotto. ?>
		<?php if ( ! empty( $onthewall_artwork['sold'] ) ) : ?>
			<span class="sold-tape" aria-hidden="true">
				<span class="sold-tape-roll font-display"><?php esc_html_e( 'SOLD', 'onthewall' ); ?></span>
			</span>
		<?php endif; ?>
	</button>

	<div class="mt-3 flex items-start justify-between gap-2">
		<h3 class="font-display text-xl tracking-tight"><?php echo esc_html( $onthewall_artwork['title'] ); ?></h3>
		<?php if ( ! empty( $onthewall_artwork['size'] ) ) : ?>
			<span class="font-mono text-[11px] uppercase tracking-[0.15em] whitespace-nowrap text-muted-foreground">
				<?php echo esc_html( $onthewall_artwork['size'] ); ?>
			</span>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $onthewall_artwork['technique'] ) || '' !== $onthewall_price_label ) : ?>
		<div class="card-meta-row">
			<?php if ( ! empty( $onthewall_artwork['technique'] ) ) : ?>
				<p class="font-mono text-[11px] uppercase tracking-[0.15em] text-muted-foreground">
					<?php echo esc_html( $onthewall_artwork['technique'] ); ?>
				</p>
			<?php endif; ?>
			<?php if ( '' !== $onthewall_price_label ) : ?>
				<span class="ml-auto font-mono text-[11px] font-bold tracking-[0.15em] whitespace-nowrap text-primary">
					<?php echo esc_html( $onthewall_price_label ); ?>
				</span>
			<?php endif; ?>
		</div>
	<?php endif; ?>

</article>

<div id="<?php echo esc_attr( $onthewall_box_id ); ?>" popover class="lightbox" role="dialog" aria-label="<?php echo esc_attr( $onthewall_artwork['title'] ); ?>">

	<button type="button" class="lightbox-close font-mono text-xs uppercase tracking-[0.2em]" popovertarget="<?php echo esc_attr( $onthewall_box_id ); ?>" popovertargetaction="hide">
		<?php esc_html_e( 'Chiudi', 'onthewall' ); ?> &times;
	</button>

	<div class="lightbox-track" tabindex="0" aria-label="<?php esc_attr_e( 'Foto dell\'opera', 'onthewall' ); ?>">
		<?php foreach ( $onthewall_photos as $onthewall_n => $onthewall_photo ) : ?>
			<figure id="<?php echo esc_attr( $onthewall_box_id . '-' . ( $onthewall_n + 1 ) ); ?>" class="lightbox-slide">
				<?php echo wp_kses_post( $onthewall_photo ); ?>
				<?php if ( $onthewall_photo_count > 1 ) : ?>
					<figcaption class="lightbox-nav font-mono text-xs tracking-[0.2em]">
						<?php // Tre posti fissi: la freccia che manca lascia il suo spazio vuoto, così il gruppo non si sposta. ?>
						<?php if ( $onthewall_n > 0 ) : ?>
							<a href="#<?php echo esc_attr( $onthewall_box_id . '-' . $onthewall_n ); ?>" aria-label="<?php esc_attr_e( 'Foto precedente', 'onthewall' ); ?>">&larr;</a>
						<?php else : ?>
							<span aria-hidden="true"></span>
						<?php endif; ?>
						<span><?php echo esc_html( ( $onthewall_n + 1 ) . ' / ' . $onthewall_photo_count ); ?></span>
						<?php if ( $onthewall_n < $onthewall_photo_count - 1 ) : ?>
							<a href="#<?php echo esc_attr( $onthewall_box_id . '-' . ( $onthewall_n + 2 ) ); ?>" aria-label="<?php esc_attr_e( 'Foto successiva', 'onthewall' ); ?>">&rarr;</a>
						<?php else : ?>
							<span aria-hidden="true"></span>
						<?php endif; ?>
					</figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>

	<div class="lightbox-details">
		<h3 class="font-display text-5xl leading-[0.9] tracking-tight text-balance"><?php echo esc_html( $onthewall_artwork['title'] ); ?></h3>
		<?php if ( ! empty( $onthewall_artwork['size'] ) ) : ?>
			<p class="mt-4 font-mono text-xs uppercase tracking-[0.2em] text-muted-foreground"><?php echo esc_html( $onthewall_artwork['size'] ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $onthewall_artwork['technique'] ) ) : ?>
			<p class="mt-2 font-mono text-xs uppercase tracking-[0.2em] text-muted-foreground"><?php echo esc_html( $onthewall_artwork['technique'] ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $onthewall_price_label ) : ?>
			<p class="mt-6 font-display text-2xl tracking-tight text-primary"><?php echo esc_html( $onthewall_price_label ); ?></p>
		<?php endif; ?>
	</div>

</div>
