<?php
/**
 * Plugin Name: Berounsko Rezervace
 * Plugin URI:  https://github.com/trnkapavel/berounsko-wp
 * Description: Rezervační modál pro komentované vycházky Berounsko.net. Vložte shortcode <code>[berounsko_rezervace]</code> kamkoli na stránku – návštěvníci si vyberou trasu, zadají e-mail a počet osob. Plugin automaticky odešle potvrzovací e-mail s QR kódem pro platbu a kalendářní pozvánkou (.ics). Nastavení v <a href="options-general.php?page=berounsko-rezervace">Nastavení → Berounsko Rezervace</a>.
 * Version:     1.1.0
 * Author:      Pavel Trnka
 * Author URI:  https://github.com/trnkapavel
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: berounsko-rezervace
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BREZ_VERSION', '1.1.0' );
define( 'BREZ_PATH', plugin_dir_path( __FILE__ ) );
define( 'BREZ_URL', plugin_dir_url( __FILE__ ) );

// --- SHORTCODE ---
add_shortcode( 'berounsko_rezervace', 'brez_shortcode' );

function brez_shortcode( $atts ) {
    $atts = shortcode_atts( [
        'button_text' => 'Rezervovat vycházku',
    ], $atts, 'berounsko_rezervace' );

    // Načtení assetů jen na stránkách kde je shortcode
    brez_enqueue_assets();

    ob_start();
    include BREZ_PATH . 'templates/modal.php';
    $output = ob_get_clean();

    $button = '<button class="brez-open-btn" onclick="brezOpenModal()">'
              . esc_html( $atts['button_text'] )
              . '</button>';

    return $button . $output;
}

// --- ASSETS ---
function brez_enqueue_assets() {
    wp_enqueue_style(
        'berounsko-rezervace',
        BREZ_URL . 'assets/css/modal.css',
        [],
        BREZ_VERSION
    );

    wp_enqueue_script(
        'berounsko-rezervace',
        BREZ_URL . 'assets/js/modal.js',
        [],
        BREZ_VERSION,
        true  // footer
    );

    // Předání dat do JS (AJAX URL, nonce, cesta k obrázkům)
    wp_localize_script( 'berounsko-rezervace', 'brezData', [
        'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
        'nonce'     => wp_create_nonce( 'brez_rezervace' ),
        'pluginUrl' => BREZ_URL,
    ] );
}

// --- SETTINGS ---
require_once BREZ_PATH . 'includes/settings.php';

// --- AJAX HANDLER ---
require_once BREZ_PATH . 'includes/ajax-handler.php';

add_action( 'wp_ajax_berounsko_rezervace',        'brez_handle_ajax' );
add_action( 'wp_ajax_nopriv_berounsko_rezervace', 'brez_handle_ajax' );
