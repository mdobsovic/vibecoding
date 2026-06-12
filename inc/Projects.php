<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;

/*
 * Repozitar projektov.
 * Teraz vracia staticke pole (rovnake karty ako v povodnej statickej verzii).
 * Ked bude pripravena databaza, staci v configu zapnut 'use_db_projects' = true
 * a doplnit citanie z DB v metode all() (pripravena vetva nizsie).
 */
class Projects
{
    /**
     * Vrati zoznam projektov na zobrazenie.
     *
     * @return array<int, array{nazov:string, popis:string, tech:array<int,string>, tag:?string, url:?string}>
     */
    public static function all(): array
    {
        global $config;

        // Buducnost: ak je zapnuta DB, citaj projekty z databazy.
        // Zatial vypnute cez prepinac v configu (use_db_projects = false).
        if (!empty($config['use_db_projects'])) {
            return self::fromDatabase();
        }

        return self::fallback();
    }

    /**
     * Docasne staticke projekty (kym sa nepouzije databaza).
     */
    private static function fallback(): array
    {
        return [
            [
                'nazov' => 'Názov projektu',
                'popis' => 'Krátky popis projektu sa zobrazí tu. Karty budú doplnené neskôr.',
                'tech'  => ['HTML', 'CSS', 'JS'],
                'tag'   => 'Pripravuje sa',
                'url'   => null,
            ],
            [
                'nazov' => 'Názov projektu',
                'popis' => 'Krátky popis projektu sa zobrazí tu. Karty budú doplnené neskôr.',
                'tech'  => ['PHP', 'MariaDB'],
                'tag'   => 'Pripravuje sa',
                'url'   => null,
            ],
            [
                'nazov' => 'Názov projektu',
                'popis' => 'Krátky popis projektu sa zobrazí tu. Karty budú doplnené neskôr.',
                'tech'  => ['MikroTik', 'Siete'],
                'tag'   => 'Pripravuje sa',
                'url'   => null,
            ],
        ];
    }

    /**
     * Citanie projektov z databazy. Aktivuje sa az vo finalnej faze "Moje projekty".
     * Vyzaduje vyplnene DB udaje v configu a vytvorenu tabulku 'projekty' (sql/schema.sql).
     */
    private static function fromDatabase(): array
    {
        // Pripojenie k DB nacitame az tu (lazy), aby staticka verzia nepotrebovala DB
        require_once __DIR__ . '/db.php';

        $sql = 'SELECT nazov, popis, tech, url
                FROM projekty
                WHERE je_zverejneny = 1
                ORDER BY poradie ASC, id DESC';

        $rows = db()->query($sql)->fetchAll();

        // Pole technologii je v DB ulozene ako text oddeleny ciarkami -> prevod na pole
        return array_map(static function (array $r): array {
            return [
                'nazov' => (string) $r['nazov'],
                'popis' => (string) $r['popis'],
                'tech'  => array_filter(array_map('trim', explode(',', (string) $r['tech']))),
                'tag'   => null,
                'url'   => $r['url'] !== null && $r['url'] !== '' ? (string) $r['url'] : null,
            ];
        }, $rows);
    }
}
