<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;

/*
 * VZOROVA konfiguracia.
 * Skopiruj tento subor ako "config.php" (v tom istom priecinku) a vypln skutocne udaje.
 * Subor config.php sa NIKDY necommituje do gitu (je v .gitignore) - obsahuje hesla.
 */

return [

    // Nastavenia SMTP servera, cez ktory sa odosielaju e-maily z kontaktneho formulara
    'smtp' => [
        'host'       => 'smtp.priklad.sk',   // adresa SMTP servera hostingu
        'port'       => 587,                  // 587 = STARTTLS, 465 = SSL
        'secure'     => 'tls',               // 'tls' pre port 587, 'ssl' pre port 465
        'user'       => 'web@priklad.sk',    // prihlasovacie meno k schranke (zvycajne cely e-mail)
        'pass'       => '',                  // heslo k schranke - VYPLNIT na hostingu
        'from_email' => 'web@priklad.sk',    // adresa odosielatela (musi sediet so schrankou kvoli SPF/DMARC)
        'from_name'  => 'Web Michal Dobsovic',
    ],

    // Kam sa maju dorucovat spravy z formulara
    'contact' => [
        'recipient_email' => 'dobsovic@itlearning.sk',
        'recipient_name'  => 'Michal Dobsovic',
    ],

    // Databaza (MariaDB) - pripravene na buducu funkcionalitu Projekty.
    // Kym nechces pouzivat DB, netreba nic vyplnat.
    'db' => [
        'host'    => 'localhost',
        'name'    => '',
        'user'    => '',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],

    // Prepinac: ked bude true, projekty sa budu citat z databazy (zatial false -> staticke pole)
    'use_db_projects' => false,
];
