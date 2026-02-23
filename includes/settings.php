<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// --- REGISTRACE MENU ---
add_action( 'admin_menu', 'brez_add_settings_page' );

function brez_add_settings_page() {
    add_options_page(
        'Berounsko Rezervace – Nastavení',
        'Berounsko Rezervace',
        'manage_options',
        'berounsko-rezervace',
        'brez_render_settings_page'
    );
}

// --- REGISTRACE POLÍ (Settings API) ---
add_action( 'admin_init', 'brez_register_settings' );

function brez_register_settings() {
    register_setting( 'brez_settings_group', 'brez_admin_email',      [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_email',
        'default'           => get_option( 'admin_email' ),
    ] );
    register_setting( 'brez_settings_group', 'brez_iban',             [
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => 'CZ15 3030 0000 0011 4692 8017',
    ] );
    register_setting( 'brez_settings_group', 'brez_google_sheet_url', [
        'type'              => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default'           => '',
    ] );

    // Sekce
    add_settings_section(
        'brez_section_main',
        'Základní nastavení',
        '__return_false',
        'berounsko-rezervace'
    );

    // Pole
    add_settings_field(
        'brez_admin_email',
        'E-mail správce',
        'brez_field_admin_email',
        'berounsko-rezervace',
        'brez_section_main'
    );
    add_settings_field(
        'brez_iban',
        'IBAN (pro QR platby)',
        'brez_field_iban',
        'berounsko-rezervace',
        'brez_section_main'
    );
    add_settings_field(
        'brez_google_sheet_url',
        'Google Apps Script URL',
        'brez_field_google_sheet_url',
        'berounsko-rezervace',
        'brez_section_main'
    );
}

// --- VÝSTUP POLÍ ---
function brez_field_admin_email() {
    $val = get_option( 'brez_admin_email', get_option( 'admin_email' ) );
    echo '<input type="email" name="brez_admin_email" value="' . esc_attr( $val ) . '" class="regular-text">';
    echo '<p class="description">Na tento e-mail dorazí notifikace o každé nové rezervaci.</p>';
}

function brez_field_iban() {
    $val = get_option( 'brez_iban', 'CZ15 3030 0000 0011 4692 8017' );
    echo '<input type="text" name="brez_iban" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="CZ00 0000 0000 0000 0000 0000">';
    echo '<p class="description">IBAN účtu pro generování QR kódu. Formát s mezerami i bez funguje.</p>';
}

function brez_field_google_sheet_url() {
    $val = get_option( 'brez_google_sheet_url', '' );
    echo '<input type="url" name="brez_google_sheet_url" value="' . esc_attr( $val ) . '" class="large-text" placeholder="https://script.google.com/macros/s/...">';
    echo '<p class="description">URL publikovaného Google Apps Scriptu. Ponechte prázdné, pokud Google Sheets nepoužíváte.</p>';
}

// --- RENDER STRÁNKY ---
function brez_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <?php settings_errors( 'brez_settings_group' ); ?>

        <form method="post" action="options.php">
            <?php
            settings_fields( 'brez_settings_group' );
            do_settings_sections( 'berounsko-rezervace' );
            submit_button( 'Uložit nastavení' );
            ?>
        </form>

        <hr>

        <h2>Použití shortcodu</h2>
        <p>Vložte na libovolnou stránku nebo příspěvek:</p>
        <code>[berounsko_rezervace]</code>
        <p>S vlastním textem tlačítka:</p>
        <code>[berounsko_rezervace button_text="Rezervovat místo"]</code>

        <hr>

        <h2>Test odeslání e-mailu</h2>
        <p>Otestujte, zda WordPress správně odesílá e-maily na váš server:</p>
        <?php
        if ( isset( $_GET['brez_test_mail'] ) && check_admin_referer( 'brez_test_mail' ) ) {
            $to      = get_option( 'brez_admin_email', get_option( 'admin_email' ) );
            $sent    = wp_mail( $to, 'Test – Berounsko Rezervace', 'Testovací e-mail z pluginu Berounsko Rezervace funguje správně.' );
            if ( $sent ) {
                echo '<div class="notice notice-success inline"><p>✅ Testovací e-mail byl odeslán na <strong>' . esc_html( $to ) . '</strong>.</p></div>';
            } else {
                echo '<div class="notice notice-error inline"><p>❌ E-mail se nepodařilo odeslat. Zkontrolujte nastavení SMTP na vašem hostingu.</p></div>';
            }
        }
        $test_url = wp_nonce_url(
            admin_url( 'options-general.php?page=berounsko-rezervace&brez_test_mail=1' ),
            'brez_test_mail'
        );
        echo '<a href="' . esc_url( $test_url ) . '" class="button">Odeslat testovací e-mail</a>';
        ?>
    </div>
    <?php
}
