# 🥾 Berounsko Rezervace – WordPress Plugin

WordPress plugin pro rezervaci komentovaných vycházek v oblasti Berounska. Vložte jediný shortcode kamkoli na stránku nebo příspěvek a návštěvníci mohou rezervovat místo online – přímo bez přechodu na jinou stránku.

## 🎯 Hlavní Funkce

- **Rezervační modál** – animované vyskakovací okno s fotografií trasy, popisem a formulářem
- **4 vycházky** – výběr trasy s rozbalitelným popisem a indikátorem náročnosti
- **Výpočet ceny v reálném čase** – cena se aktualizuje dle počtu účastníků
- **QR kód pro platbu** – automaticky generovaný SPAYD kód pro bankovní aplikace
- **Automatické e-maily** – potvrzení pro účastníka i notifikace správci
- **Kalendářní pozvánka** – příloha `.ics` v e-mailu (Google Calendar, Apple Calendar, Outlook)
- **Google Sheets integrace** – záloha každé rezervace do tabulky
- **Shortcode** – `[berounsko_rezervace]` vložitelný kamkoli, s volitelným textem tlačítka
- **Admin nastavení** – stránka v WP adminu pro e-mail, IBAN a Google Sheets URL
- **Responzivní design** – funguje na mobilu, tabletu i desktopu

## 📋 Dostupné Vycházky

| Trasa | Délka | Cena | Náročnost |
|-------|-------|------|-----------|
| Okruh Srbsko, Chlum | 4 km | Zdarma | 🔴 Velmi těžká |
| Svatojanský okruh | 4 km | 100 Kč | 🔴 Těžká |
| Brdatka (Křivoklátsko) | 9,5 km | 100 Kč | 🟠 Střední |
| Alkazar | 4 km | 100 Kč | 🟢 Velmi lehká |

## 🛠️ Technologie

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla, bez závislostí)
- **Backend**: PHP 7.4+, WordPress Settings API, WP AJAX
- **Integrace**: Google Sheets API, SPAYD formát pro QR platby, iCal (.ics) pro kalendáře
- **WordPress**: Shortcode API, `wp_mail()`, `wp_enqueue_scripts()`, nonce ochrana

## 📁 Struktura Pluginu

```
berounsko-wp/
├── berounsko-rezervace.php     # Hlavní soubor – shortcode, enqueue, AJAX registrace
├── includes/
│   ├── ajax-handler.php        # Zpracování formuláře (e-mail, ICS, Google Sheets)
│   └── settings.php            # Admin stránka s nastavením
├── assets/
│   ├── css/modal.css           # Styly modálu (BEM prefix brez-)
│   ├── js/modal.js             # Logika modálu, data tras, WP AJAX odeslání
│   └── img/                    # Fotografie tras
│       ├── srbsko-chlum.jpg
│       ├── svatojansky-okruh.jpg
│       ├── brdatka.jpg
│       └── alkazar.jpg
└── templates/
    └── modal.php               # HTML šablona modálu
```

## 🚀 Instalace

### Požadavky

- WordPress 5.0+
- PHP 7.4+
- Funkční odesílání e-mailů (`wp_mail()` / SMTP)

### Ruční instalace (doporučená)

1. **Stáhněte nebo naklonujte repozitář**
```bash
git clone https://github.com/trnkapavel/berounsko-wp.git
```

2. **Zkopírujte složku do WordPress**
```bash
cp -r berounsko-wp /cesta/k/wordpress/wp-content/plugins/
```

3. **Aktivujte plugin v administraci**
   - WP Admin → Pluginy → Berounsko Rezervace → **Aktivovat**

4. **Nastavte e-mail a IBAN**
   - WP Admin → Nastavení → **Berounsko Rezervace**

5. **Vložte shortcode na stránku**
```
[berounsko_rezervace]
```

### FTP instalace

1. Nahrajte celou složku `berounsko-wp/` do `wp-content/plugins/` přes FTP
2. Aktivujte plugin v administraci
3. Nastavte hodnoty v **Nastavení → Berounsko Rezervace**

## ⚙️ Konfigurace

### Admin stránka (WP Admin → Nastavení → Berounsko Rezervace)

| Pole | Popis | Výchozí hodnota |
|------|-------|-----------------|
| E-mail správce | Kam chodí notifikace o rezervacích | Admin e-mail z WP nastavení |
| IBAN | Číslo účtu pro generování QR kódu | `CZ15 3030 0000 0011 4692 8017` |
| Google Apps Script URL | URL pro ukládání rezervací do Sheets | _(prázdné)_ |

### Shortcode atributy

```
[berounsko_rezervace]
[berounsko_rezervace button_text="Rezervovat místo"]
[berounsko_rezervace button_text="Chci jít na vycházku"]
```

### Data tras

Trasy, průvodci, data a lokace se upravují přímo v:
- `assets/js/modal.js` – název, obrázek, průvodce, datum, délka, náročnost, popis, cena
- `includes/ajax-handler.php` – datum pro e-mail a `.ics` soubor (`$walksData`)

## 💳 Platební Systém

Plugin generuje QR kódy ve formátu **SPAYD** (iniciativa ČNB).

- Účastník nasnímá QR kód telefonem
- Bankovní aplikace se otevře s vyplněnou částkou a správnou zprávou
- Rezervace je platná po připsání částky

## 📧 E-maily

### Pro účastníka
- HTML e-mail s fotografií trasy, detaily rezervace a cenou
- QR kód pro platbu (u placených tras)
- **Příloha `pozvanka.ics`** – přidá událost do Google Calendar, Apple Calendar nebo Outlook

### Pro správce
- Stručný přehled nové rezervace
- `Reply-To` nastaven na e-mail účastníka pro snadnou komunikaci

## 🔄 Integrace s Google Sheets

Každá rezervace se automaticky zapíše do Google Sheets:

1. Vytvořte Google Apps Script a publikujte jako Web App
2. Vložte URL do **Nastavení → Berounsko Rezervace → Google Apps Script URL**

Struktura odesílaných dat:
```json
{
  "date":    "2026-04-18 10:00:00",
  "walk":    "Okruh Srbsko, Chlum",
  "email":   "user@example.com",
  "count":   2,
  "price":   0,
  "qr_link": ""
}
```

## 🐛 Řešení Problémů

### E-maily se neposílají
- Otestujte odesílání přes **Nastavení → Berounsko Rezervace → Odeslat testovací e-mail**
- Zkontrolujte, zda hosting podporuje `wp_mail()` – na mnoha hostinzích je nutný SMTP plugin (např. WP Mail SMTP)

### QR kód se nezobrazuje v e-mailu
- QR kódy se generují přes API `qrserver.com` – server musí mít přístup k internetu
- Zkontrolujte formát IBANu v nastavení pluginu

### Google Sheets se nenaplňuje
- URL musí být ve tvaru `https://script.google.com/macros/s/.../exec`
- Google Apps Script musí být publikován jako **Web App** s přístupem **Anyone**

### Modal se zobrazuje špatně
- Plugin automaticky přesouvá modal na `<body>` – vyřeší konflikty s WP tématy, která používají CSS `transform`
- Zkontrolujte, zda jiný plugin nepřepisuje `z-index` nebo `position`

## 📱 Responzivita

| Zařízení | Chování modálu |
|----------|----------------|
| 📱 Mobil (< 768px) | Fullscreen, obrázek nahoře (150px), formulář níže |
| 📊 Tablet (768px+) | Dva sloupce, výška 90vh |
| 🖥️ Desktop (1024px+) | Dva sloupce, max. šířka 1000px |

## 👨‍💻 Architektura

Plugin nevyžaduje žádný build process ani závislosti – čistý PHP + Vanilla JS.

### Tok dat při rezervaci

```
Návštěvník klikne na tlačítko
      ↓
JS otevře modal s animací
      ↓
Vyplní e-mail a počet osob → cena v reálném čase
      ↓
Odešle formulář (Fetch API + WP AJAX nonce)
      ↓
PHP ajax-handler.php
      ├→ Ověří nonce (wp_verify_nonce)
      ├→ Sanitizuje vstupy (sanitize_email, absint)
      ├→ Generuje QR kód (SPAYD přes qrserver.com)
      ├→ Sestaví .ics soubor (iCal RFC 5545)
      ├→ Odešle do Google Sheets (wp_remote_post)
      ├→ Odešle e-mail účastníkovi (wp_mail, multipart HTML + .ics)
      ├→ Odešle e-mail správci (wp_mail, HTML)
      └→ Vrátí JSON { success: true }
      ↓
JS zobrazí success animaci s checkmarkem
```

## 📝 Changelog

### v1.1.0 (2026-02-23)
- Přidána admin settings stránka (Nastavení → Berounsko Rezervace)
- Tlačítko pro testovací e-mail
- Oprava overlay při CSS transform v tématu (přesun modalu na `<body>`)

### v1.0.0 (2026-02-23)
- První verze pluginu
- Shortcode `[berounsko_rezervace]`
- Modal s výběrem trasy, formulářem, animacemi
- WP AJAX handler s `wp_mail()`, `.ics` přílohou a Google Sheets integrací

## 📝 Licence

MIT License

## 👋 Kontakt

- **Web**: https://www.berounsko.net
- **E-mail**: info@berounsko.net
- **GitHub**: https://github.com/trnkapavel/berounsko-wp

---

**Verze**: 1.1.0 | **Poslední aktualizace**: 23. února 2026
