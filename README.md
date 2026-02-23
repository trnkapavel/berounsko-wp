# Berounsko Rezervace – WordPress Plugin

Rezervační modál pro komentované vycházky Berounsko.net.
Vložte shortcode kamkoli na stránku nebo do příspěvku.

## Instalace

1. Stáhněte nebo naklonujte repozitář do `wp-content/plugins/berounsko-wp/`
2. Aktivujte plugin v **WordPress admin → Pluginy**

## Použití

Vložte shortcode na libovolnou stránku:

```
[berounsko_rezervace]
```

Nebo s vlastním textem tlačítka:

```
[berounsko_rezervace button_text="Rezervovat místo"]
```

## Struktura

```
berounsko-wp/
├── berounsko-rezervace.php   # Hlavní soubor pluginu (shortcode, enqueue, AJAX)
├── includes/
│   └── ajax-handler.php      # Zpracování formuláře (e-mail, ICS, Google Sheets)
├── assets/
│   ├── css/modal.css         # Styly modálu
│   ├── js/modal.js           # Logika modálu
│   └── img/                  # Fotografie tras
│       ├── srbsko-chlum.jpg
│       ├── svatojansky-okruh.jpg
│       ├── brdatka.jpg
│       └── alkazar.jpg
└── templates/
    └── modal.php             # HTML šablona modálu
```

## Nastavení (WordPress Options)

Nastavení se ukládají přes WP Options API (lze přidat Settings stránku v budoucnu).
Zatím upravte přímo v `includes/ajax-handler.php` nebo přidejte hodnoty přes `add_option()`:

| Option klíč              | Popis                        | Výchozí hodnota              |
|--------------------------|------------------------------|------------------------------|
| `brez_admin_email`       | E-mail správce               | `admin_email` z WP nastavení |
| `brez_iban`              | IBAN pro QR platby           | `CZ15 3030 0000 0011 4692 8017` |
| `brez_google_sheet_url`  | URL Google Apps Scriptu      | _(prázdné)_                  |

## Požadavky

- WordPress 5.0+
- PHP 7.4+
- Funkční WP Mail (pro odesílání e-mailů)
