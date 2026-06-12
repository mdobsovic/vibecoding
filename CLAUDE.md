# CLAUDE.md

Tento subor obsahuje pokyny pre pracu na projekte. Komentare v kode pis vzdy bez diakritiky.

## O projekte

Osobna webstranka - rozcestnik projektov pre Michala Dobsovica.

Michal Dobsovic je lektor v IT LEARNING SLOVAKIA (www.itlearning.sk) a zameriava sa na skolenia v oblastiach:

- Windows Server
- Linuxove servery
- Pocitacove siete - obzvlast MikroTik
- Vyvoj webovych aplikacii (HTML, JS, CSS, PHP)
- Databazy - Microsoft SQL Server a MySQL (resp. MariaDB)

## Obsah stranky

- text o autorovi
- moje projekty (budu sa pridavat postupne podla potreby)
- kontaktny formular

## Zakladne pravidla

- Stranka je v slovencine, texty su pisane s diakritikou.
- Komentare v kode pis VZDY bez diakritiky.
- Povolene technologie: HTML, CSS, JavaScript, PHP, MariaDB.
- Projekt je verziovany v gite (GitHub).
  - Mozes robit commity, ale NIKDY nerob `push` - push robi vzdy pouzivatel.
  - Commit rob iba vtedy, ked sa na tom spolocne dohodneme.

## Postup prace

1. [HOTOVO] Zakladna struktura stranky + design.
2. [HOTOVO] Funkcionalita kontaktneho formulara + prechod zo statickej stranky na PHP.
3. [PRIPRAVENE] Funkcionalita "Moje projekty" - DB vrstva je pripravena, zostava zapnut citanie
   projektov z databazy a doplnit ich spravu.

## Struktura projektu

Stranka je dynamicka (PHP). Vstupny bod je `index.php`, ktory sklada sekcie a vyuziva spolocne
casti a pomocne moduly:

- `index.php` - hlavna stranka (sklada partials, generuje projekty, obsahuje kontaktny formular)
- `inc/bootstrap.php` - spolocny zaklad (nacita config, session, helpers); volat na zaciatku kazdeho PHP vstupu
- `inc/config.sample.php` - VZOR konfiguracie (commituje sa)
- `inc/config.php` - SKUTOCNA konfiguracia s heslami (SMTP/DB); je v `.gitignore`, NIKDY sa necommituje
- `inc/helpers.php` - `e()` (escape), `csrf_token()`, `csrf_check()`, `json_response()`, `is_ajax()`
- `inc/mailer.php` - `send_contact_mail()` (odoslanie cez SMTP pomocou PHPMailer)
- `inc/db.php` - `db()` lazy PDO pripojenie (pouzije sa az pri projektoch z DB)
- `inc/Projects.php` - repozitar projektov (`Projects::all()`)
- `inc/partials/header.php`, `inc/partials/footer.php` - spolocne casti stranky
- `api/kontakt.php` - spracovanie kontaktneho formulara (validacia, anti-spam, odoslanie)
- `lib/PHPMailer/` - prilozena kniznica na odosielanie e-mailov (bez Composera)
- `sql/schema.sql` - schema databazy (tabulka `projekty`)
- `css/`, `js/`, `img/` - styly, skripty, obrazky

## Konfiguracia a nasadenie

- **PHP verzia na hostingu: 7.4.** Vsetok PHP kod musi ostat kompatibilny s PHP 7.4
  (NEPOUZIVAT konstrukcie z PHP 8+: `match`, `str_contains`/`str_starts_with`/`str_ends_with`,
  union typy, pomenovane argumenty, konstruktor property promotion, nullsafe `?->`, `enum`,
  `readonly`, `mixed` a pod.). Pouzite kniznice musia tiez podporovat 7.4
  (PHPMailer 6.9.3 vyzaduje len PHP >= 5.5 - OK).
- Potrebne PHP rozsirenia: `mbstring`, `ctype`, `filter`, `openssl` (kvoli SMTP cez TLS/SSL);
  pre buducu DB aj `pdo_mysql`. Na beznom hostingu su zvycajne zapnute.
- Na hostingu skopiruj `inc/config.sample.php` na `inc/config.php` a vypln skutocne udaje
  (SMTP host/port/login/heslo, odosielatel, cielova adresa; DB az pri projektoch).
- `inc/config.php` sa NIKDY necommituje (obsahuje hesla).
- Kontaktny formular: e-maily sa posielaju cez SMTP (PHPMailer). Pre spolahlive dorucenie
  ma byt `from_email` rovnaka schranka ako SMTP login (kvoli SPF/DMARC); odpoved ide na
  e-mail navstevnika cez Reply-To.
- Projekty z databazy: importuj `sql/schema.sql`, vypln `db` v configu a nastav
  `use_db_projects = true`.

## Lokalny vyvoj a testovanie

- Na PC zatial nie je lokalne PHP, preto sa `index.php` neda zobrazit cez `file://`
  (prehliadac by ukazal zdrojovy kod). PHP funkcionalita sa testuje na hostingu
  (nahratie z GitHubu) alebo po pripadnej instalacii lokalneho PHP (`php -S localhost:8000`).
- Ked bude treba pripravit nieco na strane servera (PHP verzia, SMTP/DB udaje), vopred na to upozorni.

## Prostredie

- Pracujeme na OS Windows 11, webstranka bezi lokalne v prehliadaci.
- Ked bude potrebne pouzit PHP, vopred na to upozorni pouzivatela, aby si stihol pripravit vsetko potrebne.
- Vo finale stranka pobezi na externom webovom serveri (mimo pocitaca pouzivatela), kam sa bude stahovat z GitHubu.

## Bezpecnost

- Dbaj na bezpecnost, NIKDY neukladaj do gitu citlive udaje (hesla, API kluce a pod.).
- Pri akejkolvek pochybnosti, ci je planovany krok v sulade s bezpecnostnymi pravidlami, sa vzdy opytaj.

## Design

- Sans-serif pismo (napr. Manrope).
- Pre ukazky kodu (pri projektoch) pouzi pismo JetBrains Mono.
- Farebna schema: svieza, moderna; preferovane farby su modra a zelena.
- Fotografiu autora doda pouzivatel neskor, ked to bude potrebne.
