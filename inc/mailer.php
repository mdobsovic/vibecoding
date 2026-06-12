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
        $mail->addAddress($contact['recipient_email'], $contact['recipient_name']);
        // Odpoved pojde priamo navstevnikovi
        $mail->addReplyTo($data['email'], $data['meno']);

        // --- Obsah ---
        $predmet = $data['predmet'] !== '' ? $data['predmet'] : '(bez predmetu)';
        $mail->Subject = 'Web kontakt: ' . $predmet;

        $teloText =
            "Nova sprava z kontaktneho formulara\n" .
            "-----------------------------------\n" .
            "Meno:    {$data['meno']}\n" .
            "E-mail:  {$data['email']}\n" .
            "Predmet: {$predmet}\n\n" .
            "Sprava:\n{$data['sprava']}\n";

        $teloHtml =
            '<h2 style="margin:0 0 12px">Nová správa z kontaktného formulára</h2>' .
            '<p><strong>Meno:</strong> ' . htmlspecialchars($data['meno'], ENT_QUOTES, 'UTF-8') . '<br>' .
            '<strong>E-mail:</strong> ' . htmlspecialchars($data['email'], ENT_QUOTES, 'UTF-8') . '<br>' .
            '<strong>Predmet:</strong> ' . htmlspecialchars($predmet, ENT_QUOTES, 'UTF-8') . '</p>' .
            '<p><strong>Správa:</strong><br>' . nl2br(htmlspecialchars($data['sprava'], ENT_QUOTES, 'UTF-8')) . '</p>';

        $mail->isHTML(true);
        $mail->Body    = $teloHtml;
        $mail->AltBody = $teloText;

        $mail->send();
    } catch (PHPMailerException $e) {
        // Detail zalogujeme, navstevnikovi vratime vseobecnu chybu (riesi volajuci)
        error_log('Kontaktny formular - chyba SMTP: ' . $mail->ErrorInfo);
        throw new RuntimeException('Spravu sa nepodarilo odoslat.');
    }
}
