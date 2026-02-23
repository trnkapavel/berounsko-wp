<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function brez_handle_ajax() {

    // --- Ověření nonce ---
    if ( ! check_ajax_referer( 'brez_rezervace', 'nonce', false ) ) {
        wp_send_json_error( 'Neplatný bezpečnostní token.' );
    }

    // --- Nastavení ---
    $adminEmail      = get_option( 'brez_admin_email', get_option( 'admin_email' ) );
    $rawIban         = get_option( 'brez_iban',        'CZ15 3030 0000 0011 4692 8017' );
    $iban            = str_replace( ' ', '', $rawIban );
    $googleScriptUrl = get_option( 'brez_google_sheet_url', '' );

    // --- Data tras ---
    $walksData = [
        'kras' => [
            'name'     => 'Okruh Srbsko, Chlum',
            'date_txt' => '18. 4. 2026',
            'start'    => '20260418T100000',
            'end'      => '20260418T140000',
            'location' => 'Srbsko, Česká republika',
            'guide'    => 'Martin Majer a Jan Holeček',
            'img'      => plugin_dir_url( dirname( __FILE__ ) ) . 'assets/img/srbsko-chlum.jpg',
        ],
        'svatojan' => [
            'name'     => 'Svatojanský okruh',
            'date_txt' => '16. 5. 2026',
            'start'    => '20260516T100000',
            'end'      => '20260516T140000',
            'location' => 'Svatý Jan pod Skálou',
            'guide'    => 'František Zima',
            'img'      => plugin_dir_url( dirname( __FILE__ ) ) . 'assets/img/svatojansky-okruh.jpg',
        ],
        'krivoklat' => [
            'name'     => 'Brdatka',
            'date_txt' => 'datum bude upřesněno',
            'start'    => '20260601T100000',
            'end'      => '20260601T150000',
            'location' => 'Roztoky u Křivoklátu',
            'guide'    => 'Markéta Hrnčálová',
            'img'      => plugin_dir_url( dirname( __FILE__ ) ) . 'assets/img/brdatka.jpg',
        ],
        'alkazar' => [
            'name'     => 'Alkazar',
            'date_txt' => 'datum bude upřesněno',
            'start'    => '20260615T100000',
            'end'      => '20260615T140000',
            'location' => 'Hostim u Berouna',
            'guide'    => 'Martin Majer a Jan Holeček',
            'img'      => plugin_dir_url( dirname( __FILE__ ) ) . 'assets/img/alkazar.jpg',
        ],
    ];

    // --- Vstupní data ---
    $walkId = sanitize_text_field( $_POST['walk_id'] ?? 'kras' );
    $email  = sanitize_email( $_POST['email'] ?? '' );
    $count  = absint( $_POST['count'] ?? 0 );

    if ( ! is_email( $email ) || $count < 1 ) {
        wp_send_json_error( 'Chybí e-mail nebo počet osob.' );
    }

    $info        = $walksData[ $walkId ] ?? $walksData['kras'];
    $walkName    = $info['name'];
    $walkDateTxt = $info['date_txt'];

    // --- Cena ---
    $pricePerPerson = ( $walkId === 'kras' ) ? 0 : 100;
    $totalPrice     = $count * $pricePerPerson;

    // --- 1. QR kód ---
    $qrUrl    = '';
    $qrImgTag = '';
    if ( $totalPrice > 0 ) {
        $msg      = 'Vychazka ' . substr( $walkName, 0, 30 );
        $spayd    = "SPD*1.0*ACC:{$iban}*AM:{$totalPrice}.00*CC:CZK*MSG:{$msg}";
        $qrUrl    = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode( $spayd );
        $qrImgTag = "<div style='text-align:center;margin:20px 0;'><img src='{$qrUrl}' alt='QR Platba' style='border:5px solid #fff;box-shadow:0 0 10px rgba(0,0,0,.1);width:200px;'></div>";
    }

    // --- 2. Google Sheets ---
    if ( ! empty( $googleScriptUrl ) ) {
        $sheetData = [
            'date'    => current_time( 'Y-m-d H:i:s' ),
            'walk'    => $walkName,
            'email'   => $email,
            'count'   => $count,
            'price'   => $totalPrice,
            'qr_link' => $qrUrl,
        ];
        wp_remote_post( $googleScriptUrl, [
            'body'    => wp_json_encode( $sheetData ),
            'headers' => [ 'Content-Type' => 'application/json' ],
            'timeout' => 5,
        ] );
    }

    // --- 3. ICS soubor ---
    $uid        = md5( uniqid( mt_rand(), true ) ) . '@berounsko.net';
    $icsContent = "BEGIN:VCALENDAR\r\n"
        . "VERSION:2.0\r\n"
        . "PRODID:-//Berounsko.net//Rezervace//CZ\r\n"
        . "METHOD:PUBLISH\r\n"
        . "BEGIN:VEVENT\r\n"
        . "UID:{$uid}\r\n"
        . "DTSTAMP:" . gmdate( 'Ymd\THis\Z' ) . "\r\n"
        . "DTSTART:{$info['start']}\r\n"
        . "DTEND:{$info['end']}\r\n"
        . "SUMMARY:Vycházka: {$walkName}\r\n"
        . "LOCATION:{$info['location']}\r\n"
        . "DESCRIPTION:Průvodce: {$info['guide']}\\nPočet osob: {$count}\\nRezervace přes Berounsko.net\r\n"
        . "STATUS:CONFIRMED\r\n"
        . "END:VEVENT\r\n"
        . "END:VCALENDAR";

    // --- 4. E-mail klientovi (HTML + ICS příloha) ---
    $boundary = md5( time() );

    $htmlBody = "<!DOCTYPE html><html><head><meta charset='UTF-8'>
<style>
body{font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:0}
.wrap{max-width:600px;margin:0 auto;background:#fff}
.hdr{background:#30B0FF;padding:30px;text-align:center;color:#fff}
.hdr h1{margin:0;font-size:24px;text-transform:uppercase;letter-spacing:1px}
.hero{width:100%;height:auto;display:block}
.body{padding:40px;color:#000;line-height:1.6}
.box{background:#f9f9f9;padding:20px;border-left:5px solid #80C024;margin:20px 0}
.price{font-size:24px;color:#80C024;font-weight:bold;display:block;margin-top:10px}
.ftr{background:#333;color:#888;text-align:center;padding:20px;font-size:12px}
</style></head><body>
<div class='wrap'>
  <div class='hdr'><h1>Rezervace potvrzena</h1></div>
  <img src='{$info['img']}' alt='{$walkName}' class='hero'>
  <div class='body'>
    <h2 style='color:#30B0FF;margin-top:0'>Dobrý den,</h2>
    <p>děkujeme za Váš zájem o komentované vycházky <strong>Berounsko.net</strong>. Tímto potvrzujeme Vaši rezervaci.</p>
    <div class='box'>
      <p><strong>Trasa:</strong> {$walkName}</p>
      <p><strong>Datum:</strong> {$walkDateTxt}</p>
      <p><strong>Průvodce:</strong> {$info['guide']}</p>
      <p><strong>Počet osob:</strong> {$count}</p>
      <hr style='border:0;border-top:1px solid #ddd'>
      <span class='price'>Cena celkem: {$totalPrice} Kč</span>
    </div>
    " . ( $totalPrice > 0
        ? "<h3 style='color:#80C024;text-align:center'>Platba QR kódem</h3>
           <p style='text-align:center'>Pro dokončení rezervace prosím uhraďte částku pomocí QR kódu:</p>
           {$qrImgTag}
           <p style='text-align:center;font-size:.9em'>Číslo účtu: <strong>{$rawIban}</strong></p>"
        : "<h3 style='color:#80C024;text-align:center'>Vstup je zdarma</h3>" )
    . "
    <p style='font-size:.9em;color:#666;margin-top:30px;text-align:center'>
      📅 <strong>Tip:</strong> V příloze najdete soubor <strong>pozvanka.ics</strong> – uložte si událost do kalendáře.
    </p>
    <p>Těšíme se na viděnou!</p>
  </div>
  <div class='ftr'>&copy; " . date( 'Y' ) . " Berounsko.net | Komentované vycházky</div>
</div>
</body></html>";

    $msgClient  = "--{$boundary}\r\n";
    $msgClient .= "Content-Type: text/html; charset=UTF-8\r\n";
    $msgClient .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $msgClient .= $htmlBody . "\r\n";
    $msgClient .= "--{$boundary}\r\n";
    $msgClient .= "Content-Type: text/calendar; name=\"pozvanka.ics\"\r\n";
    $msgClient .= "Content-Transfer-Encoding: base64\r\n";
    $msgClient .= "Content-Disposition: attachment; filename=\"pozvanka.ics\"\r\n\r\n";
    $msgClient .= chunk_split( base64_encode( $icsContent ) ) . "\r\n";
    $msgClient .= "--{$boundary}--";

    $headersClient = [
        'MIME-Version: 1.0',
        'From: rezervace@berounsko.net',
        "Content-Type: multipart/mixed; boundary=\"{$boundary}\"",
    ];

    $sent = wp_mail( $email, "Potvrzení rezervace: {$walkName}", $msgClient, $headersClient );

    // --- 5. E-mail správci ---
    $adminBody = "<html><body style='font-family:monospace;font-size:14px;color:#333'>
<h3>Nový účastník</h3><hr>
<strong>Vycházka:</strong> {$walkName}<br>
<strong>Datum:</strong> {$walkDateTxt}<br><br>
<strong>E-mail:</strong> <a href='mailto:{$email}'>{$email}</a><br>
<strong>Počet osob:</strong> {$count}<br>
<strong>Cena celkem:</strong> {$totalPrice} Kč<br>
<hr><p style='color:#666;font-size:12px'>Data odeslána do Google Sheets.</p>
</body></html>";

    $headersAdmin = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: rezervace@berounsko.net',
        "Reply-To: {$email}",
    ];

    wp_mail( $adminEmail, "[Nová objednávka] {$walkName} ({$count} os.)", $adminBody, $headersAdmin );

    if ( $sent ) {
        wp_send_json_success();
    } else {
        wp_send_json_error( 'Chyba odeslání e-mailu. Zkuste to prosím znovu.' );
    }
}
