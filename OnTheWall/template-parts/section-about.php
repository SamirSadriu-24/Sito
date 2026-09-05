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
<section id="about" class="relative z-10 mx-auto max-w-6xl px-5 pt-12 pb-20">
	<div class="grid grid-cols-12 items-stretch gap-5">

		<div class="col-span-12 lg:col-span-7 lg:pr-6">

			<div class="animate-spray inline-block -rotate-2 bg-accent px-3 py-1 font-mono text-xs uppercase tracking-[0.2em]">
				<?php esc_html_e( 'anarchist · neo-pop artist', 'onthewall' ); ?>
			</div>

			<h1 class="mt-6 font-display leading-[0.95] tracking-tight text-balance">
				<span class="animate-spray block text-[clamp(3rem,10vw,7rem)]">HELLO.</span>
				<span class="animate-spray spray-delay-120 drip mt-2 inline-block bg-foreground px-3 text-[clamp(3rem,10vw,7rem)] text-background">I'M RICK.</span>
				<span class="animate-spray spray-delay-240 mt-2 block text-[clamp(3rem,10vw,7rem)] text-primary">MY ART IS A RIOT.</span>
			</h1>

			<p class="animate-spray spray-delay-360 mt-10 max-w-[52ch] text-base leading-relaxed text-pretty">
				<?php esc_html_e( 'My art is a high-voltage collision between the grit of the canvas and the glossy saturation of consumer culture. I recycle everyday icons, mass-media imagery, and childhood nostalgia and recontextualize them through a hyper-saturated, ironic lens.', 'onthewall' ); ?>
			</p>

			<p class="animate-spray spray-delay-440 mt-4 max-w-[52ch] text-base leading-relaxed text-pretty">
				<?php esc_html_e( 'By blending classic Pop Art sensibilities with raw street art techniques — spray paint, stencils, wheatpastes, and screen printing — I strip luxury and commercialism of their power, handing them back to the public. My work does not ask for permission; it demands a conversation. It challenges the viewer to question what we consume, and what we worship.', 'onthewall' ); ?>
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

		<div class="relative col-span-12 min-h-[380px] lg:col-span-5">

			<div class="halftone halftone-11 absolute inset-0 bg-secondary text-background/20 opacity-40" aria-hidden="true"></div>

			<div class="torn absolute top-6 left-0 w-[78%] rotate-3 bg-accent p-2 ring-1 ring-black/10">
				<img src="<?php echo esc_url( $onthewall_img . 'ig_05.jpg' ); ?>"
					alt="<?php esc_attr_e( 'RED SIGNAL — red stencil portrait on black canvas by Rick Ferruzzi', 'onthewall' ); ?>"
					width="482" height="640" class="aspect-[4/3] w-full object-cover" />
			</div>

			<div class="torn absolute right-0 bottom-0 w-[68%] -rotate-4 bg-chart-4 p-2 ring-1 ring-black/10">
				<img src="<?php echo esc_url( $onthewall_img . 'ig_09.jpg' ); ?>"
					alt="<?php esc_attr_e( 'OGNUNO VIBRA — chimpanzee wheatpaste over newspaper collage by Rick Ferruzzi', 'onthewall' ); ?>"
					width="482" height="640" loading="lazy" class="aspect-[5/4] w-full object-cover" />
			</div>

			<div class="absolute top-2 right-2 -rotate-6 bg-foreground px-3 py-1.5 font-mono text-[10px] uppercase tracking-[0.2em] text-background">
				<?php esc_html_e( 'no permission asked', 'onthewall' ); ?>
			</div>

		</div>

	</div>
</section>
