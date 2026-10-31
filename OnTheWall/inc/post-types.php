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
 * Registra i meta dell'opera (misura, tecnica e descrizione).
 */
function onthewall_register_artwork_meta() {
	$meta = array(
		'_onthewall_size'      => esc_html__( 'Misura', 'onthewall' ),
		'_onthewall_technique' => esc_html__( 'Tecnica', 'onthewall' ),
	);

	// La descrizione è su più righe, quindi ha una sanitizzazione sua.
	register_post_meta(
		'artwork',
		'_onthewall_description',
		array(
			'type'              => 'string',
			'description'       => esc_html__( 'Descrizione', 'onthewall' ),
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_textarea_field',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
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

	// Prezzo in euro. Senza valore salvato vale il default (200); salvato vuoto, la card non lo mostra.
	register_post_meta(
		'artwork',
		'_onthewall_price',
		array(
			'type'              => 'integer',
			'description'       => esc_html__( 'Prezzo (€)', 'onthewall' ),
			'single'            => true,
			'default'           => 200,
			'sanitize_callback' => 'onthewall_sanitize_price',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	// Foto aggiuntive (ID allegati separati da virgola), dopo l'immagine in evidenza.
	register_post_meta(
		'artwork',
		'_onthewall_gallery',
		array(
			'type'              => 'string',
			'description'       => esc_html__( 'Altre foto', 'onthewall' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'onthewall_sanitize_gallery',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	// Venduta: la card mostra SOLD al posto del prezzo, che resta salvato.
	register_post_meta(
		'artwork',
		'_onthewall_sold',
		array(
			'type'              => 'boolean',
			'description'       => esc_html__( 'Venduta', 'onthewall' ),
			'single'            => true,
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'onthewall_register_artwork_meta' );

/**
 * Prezzo: stringa vuota (nessun prezzo) oppure intero in euro.
 *
 * @param mixed $value Valore inserito.
 * @return int|string
 */
function onthewall_sanitize_price( $value ) {
	$value = trim( (string) $value );

	return '' === $value ? '' : absint( $value );
}

/**
 * Galleria: tiene solo ID di immagini esistenti, senza doppioni, nell'ordine dato.
 *
 * @param mixed $value Stringa "12,15,18" o array di ID.
 * @return string
 */
function onthewall_sanitize_gallery( $value ) {
	$ids = is_array( $value ) ? $value : explode( ',', (string) $value );
	$ids = array_unique( array_filter( array_map( 'absint', $ids ), 'wp_attachment_is_image' ) );

	return implode( ',', $ids );
}

/**
 * ID delle foto aggiuntive di un'opera.
 *
 * @param int $post_id ID dell'opera.
 * @return int[]
 */
function onthewall_get_gallery_ids( $post_id ) {
	return array_values( array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, '_onthewall_gallery', true ) ) ) ) );
}

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

	$size        = (string) get_post_meta( $post->ID, '_onthewall_size', true );
	$technique   = (string) get_post_meta( $post->ID, '_onthewall_technique', true );
	$description = (string) get_post_meta( $post->ID, '_onthewall_description', true );
	$price       = (string) get_post_meta( $post->ID, '_onthewall_price', true );
	$sold        = (bool) get_post_meta( $post->ID, '_onthewall_sold', true );
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
	<p>
		<label for="onthewall-description"><strong><?php esc_html_e( 'Descrizione', 'onthewall' ); ?></strong></label><br />
		<textarea id="onthewall-description" name="onthewall_description" class="widefat" rows="4"><?php echo esc_textarea( $description ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Compare nella finestra dell\'opera. Lascia vuoto per non mostrarla.', 'onthewall' ); ?></span>
	</p>
	<p>
		<label for="onthewall-price"><strong><?php esc_html_e( 'Prezzo (€)', 'onthewall' ); ?></strong></label><br />
		<input type="number" id="onthewall-price" name="onthewall_price" class="widefat" min="0" step="1"
			value="<?php echo esc_attr( $price ); ?>" <?php echo $sold ? 'readonly' : ''; ?> />
		<span class="description"><?php esc_html_e( 'Lascia vuoto per non mostrare il prezzo.', 'onthewall' ); ?></span>
	</p>
	<p>
		<label>
			<input type="checkbox" name="onthewall_sold" value="1" class="onthewall-sold-toggle" data-price="onthewall-price" <?php checked( $sold ); ?> />
			<strong><?php esc_html_e( 'Venduta', 'onthewall' ); ?></strong>
		</label><br />
		<span class="description"><?php esc_html_e( 'Sulla pagina compare SOLD al posto del prezzo.', 'onthewall' ); ?></span>
	</p>
	<p><strong><?php esc_html_e( 'Altre foto', 'onthewall' ); ?></strong></p>
	<?php onthewall_render_gallery_control( 'onthewall_meta', 'onthewall_gallery', onthewall_get_gallery_ids( $post->ID ) ); ?>
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

	$description = isset( $_POST['onthewall_description'] )
		? sanitize_textarea_field( wp_unslash( $_POST['onthewall_description'] ) )
		: '';
	update_post_meta( $post_id, '_onthewall_description', $description );

	$price = isset( $_POST['onthewall_price'] ) ? onthewall_sanitize_price( wp_unslash( $_POST['onthewall_price'] ) ) : '';
	update_post_meta( $post_id, '_onthewall_price', $price );

	update_post_meta( $post_id, '_onthewall_sold', ! empty( $_POST['onthewall_sold'] ) );

	if ( isset( $_POST['onthewall_gallery'] ) ) {
		update_post_meta( $post_id, '_onthewall_gallery', onthewall_sanitize_gallery( wp_unslash( $_POST['onthewall_gallery'] ) ) );
	}
}
add_action( 'save_post_artwork', 'onthewall_save_artwork_meta' );
