<?php
/**
 * Protezione dell'accesso alla bacheca.
 *
 * Due misure indipendenti:
 *
 *   1. Slug di login segreto — la pagina di login standard (wp-login.php) risponde
 *      404 agli utenti non autenticati. Si accede solo passando prima da un URL
 *      segreto (di default /atelier), che apre un varco temporaneo. È un livello
 *      di offuscamento: riduce il rumore dei bot, NON sostituisce una password
 *      forte.
 *
 *   2. Limitazione dei tentativi di login — dopo troppi tentativi falliti dallo
 *      stesso IP l'accesso viene bloccato per un intervallo. Questa è protezione
 *      reale contro gli attacchi a forza bruta.
 *
 * Personalizzazione (in wp-config.php o in un mu-plugin):
 *
 *   define( 'ONTHEWALL_LOGIN_SLUG', 'un-nome-difficile-da-indovinare' );
 *   define( 'ONTHEWALL_DISABLE_LOGIN_HIDE', true ); // disattiva lo slug segreto
 *
 * Oppure via filtri: onthewall_login_slug, onthewall_login_max_attempts,
 * onthewall_login_lockout_seconds, onthewall_login_client_ip.
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * ---------------------------------------------------------------------------
 *  Slug di login segreto
 * ---------------------------------------------------------------------------
 */

/**
 * Restituisce lo slug segreto che apre l'accesso al login.
 *
 * @return string Slug ripulito (vuoto = funzione disattivata).
 */
function onthewall_login_slug() {
	$slug = defined( 'ONTHEWALL_LOGIN_SLUG' ) ? ONTHEWALL_LOGIN_SLUG : 'atelier';

	return sanitize_title( (string) apply_filters( 'onthewall_login_slug', $slug ) );
}

/**
 * Indica se l'occultamento del login è attivo.
 *
 * @return bool
 */
function onthewall_login_hide_enabled() {
	if ( defined( 'ONTHEWALL_DISABLE_LOGIN_HIDE' ) && ONTHEWALL_DISABLE_LOGIN_HIDE ) {
		return false;
	}

	return '' !== onthewall_login_slug();
}

/**
 * Token del varco, stabile per sito e legato allo slug corrente.
 *
 * @return string
 */
function onthewall_login_gate_token() {
	return substr( wp_hash( 'onthewall_login_gate|' . onthewall_login_slug(), 'auth' ), 0, 20 );
}

/**
 * Imposta il cookie che autorizza a vedere il form di login.
 */
function onthewall_set_login_gate_cookie() {
	$params = array(
		'expires'  => time() + ( 10 * MINUTE_IN_SECONDS ),
		'path'     => COOKIEPATH ? COOKIEPATH : '/',
		'domain'   => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
		'secure'   => is_ssl(),
		'httponly' => true,
		'samesite' => 'Lax',
	);

	setcookie( 'onthewall_login_gate', onthewall_login_gate_token(), $params );
}

/**
 * Verifica se la richiesta corrente ha un varco valido.
 *
 * @return bool
 */
function onthewall_has_login_gate() {
	if ( ! isset( $_COOKIE['onthewall_login_gate'] ) ) {
		return false;
	}

	$cookie = (string) wp_unslash( $_COOKIE['onthewall_login_gate'] );

	return hash_equals( onthewall_login_gate_token(), $cookie );
}

/**
 * Percorso della richiesta corrente, relativo alla home (senza slash iniziali).
 *
 * @return string
 */
function onthewall_current_request_path() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$path = trim( $path, '/' );

	$home = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );

	if ( '' !== $home && 0 === strpos( $path, $home ) ) {
		$path = trim( substr( $path, strlen( $home ) ), '/' );
	}

	return $path;
}

/**
 * Se la richiesta punta allo slug segreto, apre il varco e reindirizza al login.
 */
function onthewall_maybe_open_login_gate() {
	if ( ! onthewall_login_hide_enabled() ) {
		return;
	}

	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : 'GET';

	if ( 'GET' !== $method ) {
		return;
	}

	if ( onthewall_current_request_path() !== onthewall_login_slug() ) {
		return;
	}

	onthewall_set_login_gate_cookie();
	wp_safe_redirect( wp_login_url() );
	exit;
}
add_action( 'init', 'onthewall_maybe_open_login_gate' );

/**
 * Blocca il form di login standard agli sconosciuti senza varco valido.
 *
 * Lascia passare: utenti già loggati, il POST di autenticazione, il recupero
 * password e il logout — così nessun flusso legittimo si rompe.
 */
function onthewall_guard_login_page() {
	if ( ! onthewall_login_hide_enabled() ) {
		return;
	}

	if ( is_user_logged_in() ) {
		return;
	}

	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : 'GET';

	if ( 'POST' === $method ) {
		return;
	}

	$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : 'login';

	$allowed = array( 'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'postpass' );

	if ( in_array( $action, $allowed, true ) ) {
		return;
	}

	if ( onthewall_has_login_gate() ) {
		return;
	}

	status_header( 404 );
	nocache_headers();
	wp_die(
		esc_html__( 'Pagina non trovata.', 'onthewall' ),
		esc_html__( 'Pagina non trovata', 'onthewall' ),
		array( 'response' => 404 )
	);
}
add_action( 'login_init', 'onthewall_guard_login_page' );

/*
 * ---------------------------------------------------------------------------
 *  Limitazione dei tentativi di login
 * ---------------------------------------------------------------------------
 */

/**
 * IP del client. Di default usa REMOTE_ADDR (non falsificabile via header).
 * Dietro un reverse proxy affidabile, adattare con il filtro dedicato.
 *
 * @return string
 */
function onthewall_login_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	return (string) apply_filters( 'onthewall_login_client_ip', $ip );
}

/**
 * Chiave del transient che conta i tentativi per l'IP corrente.
 *
 * @return string
 */
function onthewall_login_attempts_key() {
	return 'onthewall_login_' . md5( onthewall_login_client_ip() );
}

/**
 * Numero massimo di tentativi prima del blocco.
 *
 * @return int
 */
function onthewall_login_max_attempts() {
	return max( 1, (int) apply_filters( 'onthewall_login_max_attempts', 5 ) );
}

/**
 * Durata del blocco in secondi.
 *
 * @return int
 */
function onthewall_login_lockout_seconds() {
	return max( 60, (int) apply_filters( 'onthewall_login_lockout_seconds', 15 * MINUTE_IN_SECONDS ) );
}

/**
 * Blocca l'autenticazione quando l'IP ha superato la soglia di tentativi.
 *
 * Gira in coda al filtro authenticate, così il blocco vale anche se le
 * credenziali sono corrette.
 *
 * @param WP_User|WP_Error|null $user     Risultato dell'autenticazione.
 * @param string                $username Nome utente inviato.
 * @return WP_User|WP_Error|null
 */
function onthewall_enforce_login_lockout( $user, $username = '' ) {
	if ( '' === $username ) {
		return $user;
	}

	$data = get_transient( onthewall_login_attempts_key() );

	if ( ! is_array( $data ) || empty( $data['count'] ) || $data['count'] < onthewall_login_max_attempts() ) {
		return $user;
	}

	$remaining = isset( $data['until'] ) ? (int) $data['until'] - time() : 0;
	$minutes   = max( 1, (int) ceil( $remaining / 60 ) );

	return new WP_Error(
		'onthewall_locked',
		sprintf(
			/* translators: %d: minuti mancanti allo sblocco. */
			esc_html__( 'Troppi tentativi di accesso falliti. Riprova tra circa %d minuti.', 'onthewall' ),
			$minutes
		)
	);
}
add_filter( 'authenticate', 'onthewall_enforce_login_lockout', 30, 2 );

/**
 * Incrementa il contatore a ogni login fallito.
 */
function onthewall_register_failed_login() {
	$key     = onthewall_login_attempts_key();
	$lockout = onthewall_login_lockout_seconds();
	$data    = get_transient( $key );

	if ( ! is_array( $data ) ) {
		$data = array(
			'count' => 0,
			'until' => 0,
		);
	}

	$data['count']++;
	$data['until'] = time() + $lockout;

	set_transient( $key, $data, $lockout );
}
add_action( 'wp_login_failed', 'onthewall_register_failed_login' );

/**
 * Azzera il contatore dopo un accesso riuscito.
 */
function onthewall_clear_login_attempts() {
	delete_transient( onthewall_login_attempts_key() );
}
add_action( 'wp_login', 'onthewall_clear_login_attempts' );
