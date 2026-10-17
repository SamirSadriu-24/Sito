<?php
/**
 * Sezione "about" — hero.
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$onthewall_img = get_template_directory_uri() . '/assets/img/';
?>
<section id="about" class="relative mx-auto max-w-6xl px-5 pt-12 pb-20">
	<div class="grid grid-cols-12 items-stretch gap-5">

		<div class="col-span-12 lg:col-span-7 lg:pr-6">

			<h1 class="mt-6 font-display leading-[0.95] tracking-tight text-balance">
				<span class="animate-spray block text-[clamp(3rem,10vw,7rem)]">HELLO.</span>
				<span class="animate-spray spray-delay-120 drip mt-2 inline-block bg-foreground px-3 text-[clamp(3rem,10vw,7rem)] text-background">I'M RICK.</span>
			</h1>

			<?php // Testo del cliente (issue #6), copiato alla lettera in un unico paragrafo: vedi docs/DECISIONI.md. ?>
			<p class="animate-spray spray-delay-360 mt-10 max-w-[52ch] text-base leading-relaxed text-pretty">
				<?php esc_html_e( 'I\'m an anarchist and neo-pop artist, my art is a high-voltage collision between the grit of the canvas and the glossy saturation of consumer culture. Recycling everyday icons, mass-media imagery, and childhood nostalgia and recontextualize them through a hyper-saturated, ironic lens. By blending classic Pop Art sensibilities with raw street art techniques—spray paint, stencils, wheatpastes, and screen printing—I strip luxury and commercialism of their power, handing them back to the public. My work does not ask for permission; it demands a conversation. It challenges the viewer to question what we consume, and what we worship. By pulling these cultural artifacts out of their original contexts, I examine how the digital age has flattened the boundary between the sacred and the superficial.', 'onthewall' ); ?>
			</p>

			<div class="animate-spray spray-delay-520 mt-8 flex flex-wrap items-center gap-4">
				<a href="#works" class="rotate-1 bg-primary px-6 py-3 font-display text-lg text-primary-foreground transition-all hover:rotate-0 hover:bg-foreground">
					<?php esc_html_e( 'See the wall ↓', 'onthewall' ); ?>
				</a>
				<span class="font-mono text-xs uppercase tracking-[0.2em] text-muted-foreground">
					<?php esc_html_e( 'spray · stencil · wheatpaste · screen print', 'onthewall' ); ?>
				</span>
			</div>

		</div>

		<?php // Logo al posto della colonna immagini (issue #5). Il multiply raggiunge lo sfondo solo perché la <section> non ha z-index: vedi docs/DECISIONI.md. ?>
		<div class="col-span-12 grid place-items-center lg:col-span-5">
			<img src="<?php echo esc_url( $onthewall_img . 'logo-misfit-toy.jpg' ); ?>"
				alt="<?php esc_attr_e( 'Misfit Toy logo', 'onthewall' ); ?>"
				width="1024" height="1024" class="mix-blend-multiply w-full max-w-md" />
		</div>

	</div>
</section>
