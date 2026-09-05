<?php
/**
 * Barra superiore: logo, navigazione e CTA "Enquire".
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<header class="relative z-10 border-b-2 border-foreground">
	<div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-5">

		<div class="flex items-center gap-3">
			<div class="grid size-9 -rotate-6 place-items-center bg-primary font-display text-xl text-primary-foreground" aria-hidden="true">R</div>
			<div class="leading-none">
				<div class="font-display text-2xl tracking-tight">
					RICK FERRUZZI
				</div>
				<div class="font-mono text-[10px] uppercase tracking-[0.25em] text-muted-foreground">neo-pop · street</div>
			</div>
		</div>

		<nav class="hidden items-center gap-6 font-mono text-xs uppercase tracking-[0.2em] sm:flex"
			aria-label="<?php esc_attr_e( 'Navigazione principale', 'onthewall' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'depth'          => 1,
					'walker'         => new OnTheWall_Nav_Walker(),
					'fallback_cb'    => 'onthewall_primary_menu_fallback',
				)
			);
			?>
		</nav>

		<a href="<?php echo esc_url( 'mailto:' . onthewall_email() ); ?>"
			class="-rotate-2 bg-foreground px-4 py-2 font-mono text-xs uppercase tracking-[0.2em] text-background transition-colors hover:bg-primary">
			<?php esc_html_e( 'Enquire', 'onthewall' ); ?>
		</a>

	</div>
</header>
