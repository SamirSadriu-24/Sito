<?php
/**
 * Template tags e helper di contenuto.
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//Commento prova
/**
 * Indirizzo email usato dalle CTA "Enquire" e "commissions".
 *
 * @return string
 */
function onthewall_email() {
	/**
	 * Filtra l'indirizzo email di contatto del tema.
	 *
	 * @param string $email Indirizzo email.
	 */
	return (string) apply_filters( 'onthewall_email', 'rick.ferruzzi@gmail.com' );
}

/**
 * Rotazione ciclica delle card della griglia opere.
 *
 * Riproduce la sequenza della preview: -1deg, 1deg, -2deg, 2deg, ripetuta.
 *
 * @param int $index Indice della card (0-based).
 * @return string Classe di utilità.
 */
function onthewall_card_rotation( $index ) {
	$rotations = array( '-rotate-1', 'rotate-1', '-rotate-2', 'rotate-2' );

	return $rotations[ $index % count( $rotations ) ];
}

/**
 * Opere di default, identiche alla preview approvata.
 *
 * Usate finché non viene pubblicata nessuna opera nel CPT "artwork".
 *
 * @return array[]
 */
function onthewall_default_artworks() {
	return array(
		array(
			'title'     => 'SISTER STATIC',
			'size'      => '80×80',
			'technique' => 'spray · stencil · canvas',
			'image'     => 'ig_02.jpg',
		),
		array(
			'title'     => 'GAZE NO.1',
			'size'      => '80×80',
			'technique' => 'spray · stencil · canvas',
			'image'     => 'ig_03.jpg',
		),
		array(
			'title'     => 'SCREAM QUEEN',
			'size'      => '80×80',
			'technique' => 'spray · stencil · canvas',
			'image'     => 'ig_04.jpg',
		),
		array(
			'title'     => 'FAST FOOD DAVID',
			'size'      => '80×80',
			'technique' => 'spray · stencil · canvas',
			'image'     => 'ig_05.jpg',
		),
		array(
			'title'     => 'RED SIGNAL',
			'size'      => '80×80',
			'technique' => 'spray · stencil · canvas',
			'image'     => 'ig_06.jpg',
		),
		array(
			'title'     => 'REBEL WITHOUT A PAUSE',
			'size'      => '80×80',
			'technique' => 'spray · stencil · canvas',
			'image'     => 'ig_07.jpg',
		),
		array(
			'title'     => 'DANDY RIOT',
			'size'      => '80×80',
			'technique' => 'spray · stencil · canvas',
			'image'     => 'ig_08.jpg',
		),
		array(
			'title'     => 'OGNUNO VIBRA',
			'size'      => '60×90',
			'technique' => 'wheatpaste · newspaper collage · spray',
			'image'     => 'ig_09.jpg',
		),
		array(
			'title'     => 'TWO HEADS',
			'size'      => '80×80',
			'technique' => 'spray · stencil · canvas',
			'image'     => 'ig_10.jpg',
		),
		array(
			'title'     => 'VERMILION SCENE',
			'size'      => '80×80',
			'technique' => 'spray · stencil · canvas',
			'image'     => 'ig_11.jpg',
		),
	);
}

/**
 * Restituisce le opere da mostrare nella sezione "The Wall".
 *
 * Se esistono opere pubblicate nel CPT "artwork" vengono usate quelle,
 * altrimenti si ricade sul contenuto di default della preview.
 *
 * @return array[] Lista normalizzata: title, size, technique, image_html.
 */
function onthewall_get_artworks() {
	$items = array();

	$query = new WP_Query(
		array(
			'post_type'              => 'artwork',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'menu_order date',
			'order'                  => 'ASC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);

	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			$image_html = get_the_post_thumbnail(
				$post,
				'onthewall-square',
				array(
					'class'   => 'aspect-square w-full object-cover',
					'loading' => 'lazy',
				)
			);

			$items[] = array(
				'title'      => get_the_title( $post ),
				'size'       => (string) get_post_meta( $post->ID, '_onthewall_size', true ),
				'technique'  => (string) get_post_meta( $post->ID, '_onthewall_technique', true ),
				'image_html' => $image_html,
			);
		}

		return $items;
	}

	foreach ( onthewall_default_artworks() as $artwork ) {
		$src = get_template_directory_uri() . '/assets/img/' . $artwork['image'];

		$artwork['image_html'] = sprintf(
			'<img src="%1$s" alt="%2$s" width="640" height="640" loading="lazy" class="aspect-square w-full object-cover" />',
			esc_url( $src ),
			esc_attr( $artwork['title'] . ' — ' . $artwork['technique'] )
		);

		$items[] = $artwork;
	}

	return $items;
}
