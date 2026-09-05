<?php
/**
 * Custom post type "artwork" per la galleria opere ("The Wall").
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registra il custom post type "artwork".
 */
function onthewall_register_artwork_post_type() {
	$labels = array(
		'name'               => esc_html__( 'Opere', 'onthewall' ),
		'singular_name'      => esc_html__( 'Opera', 'onthewall' ),
		'menu_name'          => esc_html__( 'Opere', 'onthewall' ),
		'add_new'            => esc_html__( 'Aggiungi nuova', 'onthewall' ),
		'add_new_item'       => esc_html__( 'Aggiungi nuova opera', 'onthewall' ),
		'edit_item'          => esc_html__( 'Modifica opera', 'onthewall' ),
		'new_item'           => esc_html__( 'Nuova opera', 'onthewall' ),
		'view_item'          => esc_html__( 'Vedi opera', 'onthewall' ),
		'search_items'       => esc_html__( 'Cerca opere', 'onthewall' ),
		'not_found'          => esc_html__( 'Nessuna opera trovata', 'onthewall' ),
		'not_found_in_trash' => esc_html__( 'Nessuna opera nel cestino', 'onthewall' ),
		'all_items'          => esc_html__( 'Tutte le opere', 'onthewall' ),
	);

	register_post_type(
		'artwork',
		array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-art',
			'menu_position'       => 20,
			'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
			'capability_type'     => 'post',
		)
	);
}
add_action( 'init', 'onthewall_register_artwork_post_type' );

/**
 * Registra i meta dell'opera (misura e tecnica).
 */
function onthewall_register_artwork_meta() {
	$meta = array(
		'_onthewall_size'      => esc_html__( 'Misura', 'onthewall' ),
		'_onthewall_technique' => esc_html__( 'Tecnica', 'onthewall' ),
	);

	foreach ( $meta as $key => $label ) {
		register_post_meta(
			'artwork',
			$key,
			array(
				'type'              => 'string',
				'description'       => $label,
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'onthewall_register_artwork_meta' );

/**
 * Meta box per misura e tecnica.
 */
function onthewall_add_artwork_meta_box() {
	add_meta_box(
		'onthewall-artwork-details',
		esc_html__( 'Dettagli opera', 'onthewall' ),
		'onthewall_render_artwork_meta_box',
		'artwork',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'onthewall_add_artwork_meta_box' );

/**
 * Output della meta box.
 *
 * @param WP_Post $post Post corrente.
 */
function onthewall_render_artwork_meta_box( $post ) {
	wp_nonce_field( 'onthewall_save_artwork_meta', 'onthewall_artwork_nonce' );

	$size      = (string) get_post_meta( $post->ID, '_onthewall_size', true );
	$technique = (string) get_post_meta( $post->ID, '_onthewall_technique', true );
	?>
	<p>
		<label for="onthewall-size"><strong><?php esc_html_e( 'Misura', 'onthewall' ); ?></strong></label><br />
		<input type="text" id="onthewall-size" name="onthewall_size" class="widefat"
			value="<?php echo esc_attr( $size ); ?>" placeholder="80×80" />
	</p>
	<p>
		<label for="onthewall-technique"><strong><?php esc_html_e( 'Tecnica', 'onthewall' ); ?></strong></label><br />
		<input type="text" id="onthewall-technique" name="onthewall_technique" class="widefat"
			value="<?php echo esc_attr( $technique ); ?>" placeholder="spray · stencil · canvas" />
	</p>
	<?php
}

/**
 * Salvataggio dei meta dell'opera.
 *
 * @param int $post_id ID del post.
 */
function onthewall_save_artwork_meta( $post_id ) {
	if ( ! isset( $_POST['onthewall_artwork_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['onthewall_artwork_nonce'] ) );

	if ( ! wp_verify_nonce( $nonce, 'onthewall_save_artwork_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'_onthewall_size'      => 'onthewall_size',
		'_onthewall_technique' => 'onthewall_technique',
	);

	foreach ( $fields as $meta_key => $field ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		update_post_meta( $post_id, $meta_key, $value );
	}
}
add_action( 'save_post_artwork', 'onthewall_save_artwork_meta' );
