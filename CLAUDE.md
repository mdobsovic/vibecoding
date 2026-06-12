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
3. [HOTOVO] Funkcionalita "Moje projekty" - projekty sa citaju z databazy, kazdy ma vlastnu
   detailnu podstranku (`/projekt/<slug>`) a admin rozhranie na `/admin` (prihlasenie + CRUD).
4. [HOTOVO] Galeria obrazkov k projektom - nahravanie a sprava obrazkov v adminovi
   (na editacii projektu), zobrazenie na detaile projektu s lightboxom. Subory sa ukladaju
   do priecinka `/galeria` (mimo gitu), v DB (`projekt_obrazky`) je len nazov suboru.
   Limit 2 MB/obrazok, povolene formaty JPG/PNG/WEBP/GIF.

## Struktura projektu

Stranka je dynamicka (PHP). Vstupny bod je `index.php`, ktory sklada sekcie a vyuziva spolocne
casti a pomocne moduly:

- `index.php` - hlavna stranka (sklada partials, generuje projekty, obsahuje kontaktny formular)
- `projekt.php` - detailna podstranka projektu (`/projekt/<slug>` cez `.htaccess` rewrite)
- `.htaccess` (root) - pekne URL pre detail projektu
- `inc/bootstrap.php` - spolocny zaklad (nacita config, session, helpers); volat na zaciatku kazdeho PHP vstupu
- `inc/config.sample.php` - VZOR konfiguracie (commituje sa)
- `inc/config.php` - SKUTOCNA konfiguracia s heslami (SMTP/DB/admin); je v `.gitignore`, NIKDY sa necommituje
- `inc/helpers.php` - `e()`, `csrf_token()`, `csrf_check()`, `json_response()`, `is_ajax()`, `asset()`, `asset_v()`, `slugify()`, `gallery_dir()`, `gallery_url()`; konstanta `ASSET_VERSION` (verzia CSS/JS)
- `inc/mailer.php` - `send_contact_mail()` (odoslanie cez SMTP pomocou PHPMailer)
- `inc/db.php` - `db()` lazy PDO pripojenie (projekty z DB)
- `inc/Projects.php` - repozitar projektov (`all()`, `findBySlug()`, admin CRUD)
- `inc/ProjectImages.php` - repozitar obrazkov galerie (`forProject()`, `add()`, `updateMeta()`, `delete()`)
- `inc/admin_auth.php` - prihlasenie do admina (`is_admin()`, `admin_login()`, `require_admin()`, `admin_logout()`)
- `inc/partials/header.php`, `inc/partials/footer.php` - spolocne casti verejnej stranky
- `inc/partials/admin-header.php`, `inc/partials/admin-footer.php` - spolocne casti admin rozhrania
- `admin/` - admin rozhranie: `login.php`, `logout.php`, `index.php` (zoznam),
  `projekt-form.php` (formular vratane spravy galerie), `projekt-save.php`, `projekt-delete.php`,
  `obrazok-upload.php` (nahratie obrazkov), `obrazok-save.php` (alt + poradie), `obrazok-delete.php`
- `api/kontakt.php` - spracovanie kontaktneho formulara (validacia, anti-spam, odoslanie)
- `lib/PHPMailer/` - prilozena kniznica na odosielanie e-mailov (bez Composera; serverova, nepristupna z webu)
- `vendor/trix/` - WYSIWYG editor Trix (CSS + JS), nacitavany prehliadacom v adminovi
- `tools/make-hash.php` - pomocnik na vygenerovanie hashu admin hesla (po pouziti ZMAZAT, nenahravat)
- `sql/schema.sql` - schema databazy (`projekty`, `projekt_obrazky`)
- `galeria/` - nahrate obrazky galerie (obsah je mimo gitu); obsahuje ochranny `.htaccess`
  (zakaz spustania skriptov) a `.gitkeep` (zachovanie priecinka)
- `css/style.css`, `css/admin.css`, `js/`, `img/` - styly, skripty, obrazky

## Verziovanie assetov (cache-busting)

Vlastne CSS/JS subory sa do stranky pripajaju cez `asset_v()`, ktore za cestu prida `?v=ASSET_VERSION`
(napr. `css/style.css?v=1.0.0`). Vdaka tomu prehliadace po zmene suboru nacitaju novu verziu a netreba
rucne cistit cache.

**DOLEZITE:** Pri KAZDEJ zmene obsahu `css/style.css`, `css/admin.css` alebo `js/main.js` ZVYS
konstantu `ASSET_VERSION` v `inc/helpers.php` (napr. z `'1.0.0'` na `'1.0.1'`). Bez zvysenia verzie
moze navstevnik dostat stary subor z cache.

- Verziuju sa len vlastne subory cez `asset_v()`. Kniznice tretich stran (napr. `vendor/trix/*`)
  sa pripajaju cez obycajne `asset()` bez verzie - su staticke a nemenia sa.
- Konvencia verzie: `major.minor.patch` (staci postupne zvysovat, napr. 1.0.0 -> 1.0.1 -> 1.0.2).

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
- Admin rozhranie (`/admin`): v configu vypln `admin.user` a `admin.pass_hash`. Hash hesla
  vygeneruj cez `tools/make-hash.php` (docasne nahraj, vygeneruj, vloz do configu, subor ZMAZ).
  Nastav aj `base_url` (zvycajne `'/'`) kvoli peknym URL a asset cestam mimo korena.

## Nahravanie na hosting (SFTP)

Na webserveri NIE je git ani pristup cez konzolu - stranka sa nahrava rucne cez SFTP.
Zachovaj presne rovnaku strukturu priecinkov ako v projekte. Nahravaju sa IBA subory
potrebne pre chod stranky (nie vyvojove/git subory).

NAHRAT (potrebne pre chod):
- `index.php`
- `projekt.php`
- `.htaccess` (root - pekne URL pre detail projektu)
- `css/style.css`
- `css/admin.css`
- `js/main.js`
- `img/` (obrazky, napr. `img/michal-dobsovic.jpg`)
- `inc/bootstrap.php`
- `inc/helpers.php`
- `inc/mailer.php`
- `inc/Projects.php`
- `inc/db.php`
- `inc/admin_auth.php`
- `inc/partials/header.php`
- `inc/partials/footer.php`
- `inc/partials/admin-header.php`
- `inc/partials/admin-footer.php`
- `inc/.htaccess`
- `inc/config.php` - VYTVORIT priamo na serveri z `config.sample.php` a vyplnit hesla
  (nie je v gite; ak sa upravuje lokalne, nahrat tiez)
- `api/kontakt.php`
- `inc/ProjectImages.php`
- `admin/login.php`, `admin/logout.php`, `admin/index.php`,
  `admin/projekt-form.php`, `admin/projekt-save.php`, `admin/projekt-delete.php`,
  `admin/obrazok-upload.php`, `admin/obrazok-save.php`, `admin/obrazok-delete.php`
- `galeria/.htaccess` - ochrana priecinka s obrazkami (zakaz spustania skriptov)
- priecinok `galeria/` musi byt na serveri ZAPISOVATELNY (web server don nahrava obrazky).
  Samotne obrazky v nom su mimo gitu; na server sa nahravaju cez admin, netreba ich kopirovat rucne.
- `vendor/trix/trix.css`, `vendor/trix/trix.umd.min.js`
- `sql/schema.sql` (na import do DB)
- `lib/.htaccess`
- `lib/PHPMailer/PHPMailer.php`
- `lib/PHPMailer/SMTP.php`
- `lib/PHPMailer/Exception.php`

VOLITELNE:
- `inc/config.sample.php` - hodi sa ako vzor na serveri
- `lib/PHPMailer/LICENSE` - kvoli licencnej cistote (odporucane ponechat)

NENAHRAVAT (zbytocne / nepatria na web / bezpecnost):
- `tools/make-hash.php` - len docasne na vygenerovanie admin hashu, potom ZMAZAT
- `.git/`, `.gitignore`, `.gitattributes`
- `CLAUDE.md`, `README.txt`
- `.claude/`
- pripadne lokalne vyvojove subory

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
