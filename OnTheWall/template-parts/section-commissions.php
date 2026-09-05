<?php
/**
 * Sezione "commissions" — footer.
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$onthewall_email = onthewall_email();
?>
<footer id="commissions" class="relative z-10 border-t-2 border-foreground bg-foreground text-background">

	<div class="mx-auto grid max-w-6xl grid-cols-12 gap-8 px-5 py-16">

		<div class="col-span-12 lg:col-span-7">
			<div class="font-mono text-xs uppercase tracking-[0.25em] text-accent">
				<?php esc_html_e( '(b) / commissions', 'onthewall' ); ?>
			</div>
			<h2 class="mt-3 font-display text-5xl leading-[0.9] tracking-tight text-balance sm:text-6xl">
				<?php esc_html_e( 'Got a wall?', 'onthewall' ); ?><br />
				<span class="text-primary"><?php esc_html_e( 'Got an idea?', 'onthewall' ); ?></span>
			</h2>
			<p class="mt-5 max-w-[46ch] text-pretty text-background/70">
				<?php esc_html_e( 'Original works, murals and series — tell me the size and the tone. No brief too weird, no idea too loud. For more details and commissions, drop me a line.', 'onthewall' ); ?>
			</p>
		</div>

		<div class="col-span-12 flex flex-col justify-center lg:col-span-5">
			<a href="<?php echo esc_url( 'mailto:' . $onthewall_email ); ?>"
				class="inline-block -rotate-1 bg-primary px-6 py-4 font-display text-2xl break-all text-primary-foreground ring-1 ring-black/20 transition-all hover:rotate-0 hover:bg-accent hover:text-accent-foreground sm:text-3xl">
				<?php echo esc_html( $onthewall_email ); ?>
			</a>
			<div class="mt-6 flex flex-wrap gap-3 font-mono text-[11px] uppercase tracking-[0.2em] text-background/50">
				<span><?php esc_html_e( 'originals & commissions', 'onthewall' ); ?></span>
				<span>·</span>
				<span><?php esc_html_e( 'no middlemen', 'onthewall' ); ?></span>
				<span>·</span>
				<span><?php esc_html_e( 'just paint', 'onthewall' ); ?></span>
			</div>
		</div>

	</div>

	<div class="border-t border-background/10">
		<div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-5 py-5 font-mono text-[11px] uppercase tracking-[0.2em] text-background/40">
			<span>
				<?php
				printf(
					/* translators: %s: anno corrente. */
					esc_html__( '© %s Rick Ferruzzi — all walls reserved', 'onthewall' ),
					esc_html( gmdate( 'Y' ) )
				);
				?>
			</span>
			<span><?php esc_html_e( 'neo-pop · spray · stencil · wheatpaste', 'onthewall' ); ?></span>
		</div>
	</div>

</footer>
