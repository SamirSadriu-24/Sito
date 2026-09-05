<?php
/**
 * Sezione "works" — The Wall.
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$onthewall_artworks = onthewall_get_artworks();
?>
<section id="works" class="relative z-10 border-t-2 border-foreground">
	<div class="mx-auto max-w-6xl px-5 py-14">

		<div class="mb-10 flex flex-wrap items-end justify-between gap-4">
			<div>
				<div class="font-mono text-xs uppercase tracking-[0.25em] text-primary">
					<?php esc_html_e( '(a) / selected works', 'onthewall' ); ?>
				</div>
				<h2 class="font-display text-5xl tracking-tight text-balance sm:text-6xl">
					<?php esc_html_e( 'The Wall', 'onthewall' ); ?>
				</h2>
			</div>
			<div class="font-mono text-xs uppercase tracking-[0.2em] text-muted-foreground">
				<?php esc_html_e( 'originals · one of one', 'onthewall' ); ?>
			</div>
		</div>

		<div class="grid grid-cols-12 gap-6">
			<?php
			foreach ( $onthewall_artworks as $onthewall_index => $onthewall_artwork ) {
				set_query_var( 'onthewall_artwork', $onthewall_artwork );
				set_query_var( 'onthewall_index', $onthewall_index );
				get_template_part( 'template-parts/card', 'artwork' );
			}
			?>
		</div>

	</div>
</section>
