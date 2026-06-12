<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;

/*
 * Odoslanie kontaktnej spravy cez SMTP pomocou prilozenej kniznice PHPMailer
 * (bez Composera - subory nacitavame rucne).
 */

require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Odosle spravu z kontaktneho formulara na ciel z konfiguracie.
 *
 * @param array{meno:string, email:string, predmet:string, sprava:string} $data
 * @throws RuntimeException ak sa e-mail nepodari odoslat
 */
function send_contact_mail(array $data): void
{
    global $config;
    $smtp    = $config['smtp'];
    $contact = $config['contact'];

    $mail = new PHPMailer(true); // true = vyhadzovat vynimky pri chybach

    try {
        // --- Nastavenie SMTP ---
        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->Port       = (int) $smtp['port'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['user'];
        $mail->Password   = $smtp['pass'];
        // 'tls' (port 587) alebo 'ssl' (port 465)
        $mail->SMTPSecure = $smtp['secure'] === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = 'UTF-8';

        // --- Adresy ---
        // From musi byt schranka servera (kvoli SPF/DMARC), nie e-mail navstevnika
        $mail->setFrom($smtp['from_email'], $smtp['from_name']);
        // Envelope sender (Return-Path) zhodny s From - pomaha pri SPF a znizuje sancu na spam
        $mail->Sender = $smtp['from_email'];
        $mail->addAddress($contact['recipient_email'], $contact['recipient_name']);
        // Odpoved pojde priamo navstevnikovi
        $mail->addReplyTo($data['email'], $data['meno']);

        // Potlacenie hlavicky X-Mailer (defaultnu PHPMailer hlavicku niektore filtre penalizuju)
        $mail->XMailer = ' ';

        // --- Obsah ---
        $predmet = $data['predmet'] !== '' ? $data['predmet'] : '(bez predmetu)';
        $mail->Subject = 'Nova sprava z webu: ' . $predmet;

        $mail->isHTML(true);
        $mail->Body    = build_contact_html($data, $predmet, $smtp, $contact);
        $mail->AltBody = build_contact_text($data, $predmet, $smtp, $contact);

        $mail->send();
    } catch (PHPMailerException $e) {
        // Detail zalogujeme, navstevnikovi vratime vseobecnu chybu (riesi volajuci)
        error_log('Kontaktny formular - chyba SMTP: ' . $mail->ErrorInfo);
        throw new RuntimeException('Spravu sa nepodarilo odoslat.');
    }
}

/**
 * Zostavi HTML telo e-mailu (tabulkovy layout + inline styly kvoli kompatibilite
 * e-mailovych klientov). Bohatsi a formatovany obsah znizuje sancu na zaradenie do spamu.
 *
 * @param array{meno:string, email:string, predmet:string, sprava:string} $data
 */
function build_contact_html(array $data, string $predmet, array $smtp, array $contact): string
{
    // Skratka na escapovanie hodnot vkladanych do HTML
    $h = static function (string $v): string {
        return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    };

    $brand     = $smtp['from_name'] !== '' ? $smtp['from_name'] : 'Webová stránka';
    $datum     = date('d.m.Y H:i');
    $meno      = $h($data['meno']);
    $email     = $h($data['email']);
    $predmetEsc = $h($predmet);
    $spravaEsc = nl2br($h($data['sprava']));
    $prijemca  = $h($contact['recipient_name'] !== '' ? $contact['recipient_name'] : 'Príjemca');

    // Farby podla dizajnu stranky
    $modra  = '#2563eb';
    $zelena = '#10b981';
    $tmava  = '#0f172a';
    $jemna  = '#475569';

    return <<<HTML
<!DOCTYPE html>
<html lang="sk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nová správa z webu</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f7fb; font-family:Arial, Helvetica, sans-serif; color:{$tmava};">

  <!-- Skryty preheader (nahlad v schranke) -->
  <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
    Nová správa z kontaktného formulára od {$meno}.
  </div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f7fb; padding:24px 0;">
    <tr>
      <td align="center">

        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 6px 24px rgba(15,23,42,0.08);">

          <!-- Hlavicka -->
          <tr>
            <td style="background-color:{$modra}; border-bottom:4px solid {$zelena}; padding:24px 32px;">
              <p style="margin:0; color:#ffffff; font-size:18px; font-weight:bold;">{$brand}</p>
              <p style="margin:4px 0 0; color:#dbeafe; font-size:13px;">Nová správa z kontaktného formulára</p>
            </td>
          </tr>

          <!-- Uvodny text -->
          <tr>
            <td style="padding:28px 32px 8px;">
              <p style="margin:0 0 6px; font-size:16px;">Dobrý deň, {$prijemca},</p>
              <p style="margin:0; font-size:15px; line-height:1.6; color:{$jemna};">
                cez kontaktný formulár na vašej webovej stránke vám prišla nová správa.
                Nižšie nájdete údaje odosielateľa aj samotný text správy.
              </p>
            </td>
          </tr>

          <!-- Tabulka s udajmi -->
          <tr>
            <td style="padding:16px 32px 8px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                <tr>
                  <td style="padding:8px 0; width:110px; color:{$jemna};">Meno</td>
                  <td style="padding:8px 0; font-weight:bold;">{$meno}</td>
                </tr>
                <tr>
                  <td style="padding:8px 0; color:{$jemna}; border-top:1px solid #eef2f7;">E-mail</td>
                  <td style="padding:8px 0; border-top:1px solid #eef2f7;">
                    <a href="mailto:{$email}" style="color:{$modra}; text-decoration:none;">{$email}</a>
                  </td>
                </tr>
                <tr>
                  <td style="padding:8px 0; color:{$jemna}; border-top:1px solid #eef2f7;">Predmet</td>
                  <td style="padding:8px 0; border-top:1px solid #eef2f7;">{$predmetEsc}</td>
                </tr>
                <tr>
                  <td style="padding:8px 0; color:{$jemna}; border-top:1px solid #eef2f7;">Dátum</td>
                  <td style="padding:8px 0; border-top:1px solid #eef2f7;">{$datum}</td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Text spravy -->
          <tr>
            <td style="padding:8px 32px 24px;">
              <p style="margin:0 0 8px; font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:{$jemna};">Správa</p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background-color:#f4f7fb; border-left:4px solid {$zelena}; border-radius:8px; padding:16px 18px; font-size:15px; line-height:1.7;">
                    {$spravaEsc}
                  </td>
                </tr>
              </table>
              <p style="margin:18px 0 0; font-size:13px; color:{$jemna};">
                Na túto správu môžete odpovedať priamo — odpoveď bude doručená odosielateľovi ({$meno}).
              </p>
            </td>
          </tr>

          <!-- Paticka -->
          <tr>
            <td style="background-color:#f8fafc; border-top:1px solid #eef2f7; padding:18px 32px;">
              <p style="margin:0; font-size:12px; color:#94a3b8; line-height:1.6;">
                Tento e-mail bol automaticky odoslaný z kontaktného formulára webovej stránky {$brand}.
                Prijímate ho, pretože ste uvedený ako kontaktná osoba.
              </p>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
HTML;
}

/**
 * Zostavi textovu alternativu e-mailu (pre klientov bez HTML aj kvoli spam filtrom,
 * ktore preferuju multipart spravy s plnohodnotnou textovou verziou).
 *
 * @param array{meno:string, email:string, predmet:string, sprava:string} $data
 */
function build_contact_text(array $data, string $predmet, array $smtp, array $contact): string
{
    $brand    = $smtp['from_name'] !== '' ? $smtp['from_name'] : 'Webova stranka';
    $prijemca = $contact['recipient_name'] !== '' ? $contact['recipient_name'] : 'Prijemca';
    $datum    = date('d.m.Y H:i');

    return
        "Dobry den, {$prijemca},\n\n" .
        "cez kontaktny formular na vasej webovej stranke vam prisla nova sprava.\n" .
        "Nizsie najdete udaje odosielatela aj samotny text spravy.\n\n" .
        "-----------------------------------\n" .
        "Meno:    {$data['meno']}\n" .
        "E-mail:  {$data['email']}\n" .
        "Predmet: {$predmet}\n" .
        "Datum:   {$datum}\n" .
        "-----------------------------------\n\n" .
        "Sprava:\n{$data['sprava']}\n\n" .
        "Na tuto spravu mozete odpovedat priamo - odpoved bude dorucena odosielatelovi.\n\n" .
        "--\n" .
        "Tento e-mail bol automaticky odoslany z kontaktneho formulara webovej stranky {$brand}.\n";
}
