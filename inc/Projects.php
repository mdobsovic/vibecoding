<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;

/*
 * Repozitar projektov.
 * Verejne citanie respektuje prepinac 'use_db_projects' (false -> staticky fallback).
 * Admin CRUD metody pracuju vzdy s databazou.
 */
class Projects
{
    /**
     * Vrati zoznam verejnych projektov na zobrazenie na hlavnej stranke.
     *
     * @return array<int, array{id:?int, nazov:string, slug:?string, popis:string, tech:array<int,string>, tag:?string, url:?string}>
     */
    public static function all(): array
    {
        global $config;

        if (!empty($config['use_db_projects'])) {
            return self::fromDatabase();
        }

        return self::fallback();
    }

    /**
     * Docasne staticke projekty (ked nie je zapnuta DB).
     */
    private static function fallback(): array
    {
        return [
            [
                'id'    => null,
                'nazov' => 'Názov projektu',
                'slug'  => null,
                'popis' => 'Krátky popis projektu sa zobrazí tu. Karty budú doplnené neskôr.',
                'tech'  => ['HTML', 'CSS', 'JS'],
                'tag'   => 'Pripravuje sa',
                'url'   => null,
            ],
            [
                'id'    => null,
                'nazov' => 'Názov projektu',
                'slug'  => null,
                'popis' => 'Krátky popis projektu sa zobrazí tu. Karty budú doplnené neskôr.',
                'tech'  => ['PHP', 'MariaDB'],
                'tag'   => 'Pripravuje sa',
                'url'   => null,
            ],
            [
                'id'    => null,
                'nazov' => 'Názov projektu',
                'slug'  => null,
                'popis' => 'Krátky popis projektu sa zobrazí tu. Karty budú doplnené neskôr.',
                'tech'  => ['MikroTik', 'Siete'],
                'tag'   => 'Pripravuje sa',
                'url'   => null,
            ],
        ];
    }

    /**
     * Citanie verejnych projektov z databazy (pre hlavnu stranku).
     */
    private static function fromDatabase(): array
    {
        require_once __DIR__ . '/db.php';

        $sql = 'SELECT id, nazov, slug, popis, tech, tag, url
                FROM projekty
                WHERE je_zverejneny = 1
                ORDER BY poradie ASC, id DESC';

        $rows = db()->query($sql)->fetchAll();

        return array_map([self::class, 'mapCard'], $rows);
    }

    /**
     * Detail jedneho verejneho projektu podla slugu (pre podstranku /projekt/<slug>).
     * Vrati cely riadok vratane popis_html, alebo null ak neexistuje / nie je zverejneny.
     */
    public static function findBySlug(string $slug): ?array
    {
        require_once __DIR__ . '/db.php';

        $stmt = db()->prepare('SELECT * FROM projekty WHERE slug = ? AND je_zverejneny = 1 LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    // ------------------------------------------------------------
    //  Admin CRUD (pouziva sa len v /admin, vzdy cez databazu)
    // ------------------------------------------------------------

    /**
     * Vsetky projekty (aj neverejne) pre admin zoznam.
     */
    public static function allForAdmin(): array
    {
        require_once __DIR__ . '/db.php';

        return db()->query(
            'SELECT id, nazov, slug, tech, je_zverejneny, poradie, upravene, vytvorene
             FROM projekty
             ORDER BY poradie ASC, id DESC'
        )->fetchAll();
    }

    /**
     * Jeden projekt podla id (pre editaciu v adminovi). Null ak neexistuje.
     */
    public static function find(int $id): ?array
    {
        require_once __DIR__ . '/db.php';

        $stmt = db()->prepare('SELECT * FROM projekty WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Vlozi novy projekt. Vrati id noveho zaznamu.
     *
     * @param array $d ['nazov','slug','popis','popis_html','tech','tag','url','poradie','je_zverejneny']
     */
    public static function create(array $d): int
    {
        require_once __DIR__ . '/db.php';

        $stmt = db()->prepare(
            'INSERT INTO projekty (nazov, slug, popis, popis_html, tech, tag, url, poradie, je_zverejneny)
             VALUES (:nazov, :slug, :popis, :popis_html, :tech, :tag, :url, :poradie, :je_zverejneny)'
        );
        $stmt->execute(self::bindParams($d));

        return (int) db()->lastInsertId();
    }

    /**
     * Aktualizuje existujuci projekt.
     */
    public static function update(int $id, array $d): void
    {
        require_once __DIR__ . '/db.php';

        $params = self::bindParams($d);
        $params[':id'] = $id;

        $stmt = db()->prepare(
            'UPDATE projekty SET
                nazov = :nazov, slug = :slug, popis = :popis, popis_html = :popis_html,
                tech = :tech, tag = :tag, url = :url, poradie = :poradie, je_zverejneny = :je_zverejneny
             WHERE id = :id'
        );
        $stmt->execute($params);
    }

    /**
     * Zmaze projekt. DB zaznamy obrazkov sa zmazu cez ON DELETE CASCADE,
     * ale samotne subory na disku treba odstranit rucne (este pred mazanim).
     */
    public static function delete(int $id): void
    {
        require_once __DIR__ . '/db.php';
        require_once __DIR__ . '/ProjectImages.php';

        // Najprv pozbierame nazvy suborov, kym este existuju v DB
        $subory = ProjectImages::fileNamesForProject($id);

        $stmt = db()->prepare('DELETE FROM projekty WHERE id = ?');
        $stmt->execute([$id]);

        // Zmazanie suborov z priecinka galerie (chyba nie je fatalna)
        $dir = gallery_dir();
        foreach ($subory as $subor) {
            $cesta = $dir . '/' . basename((string) $subor);
            if (is_file($cesta)) {
                @unlink($cesta);
            }
        }
    }

    /**
     * Zisti, ci uz dany slug existuje (volitelne okrem konkretneho id - pri editacii).
     */
    public static function slugExists(string $slug, int $exceptId = 0): bool
    {
        require_once __DIR__ . '/db.php';

        $stmt = db()->prepare('SELECT 1 FROM projekty WHERE slug = ? AND id <> ? LIMIT 1');
        $stmt->execute([$slug, $exceptId]);

        return $stmt->fetchColumn() !== false;
    }

    // ------------------------------------------------------------
    //  Pomocne
    // ------------------------------------------------------------

    /**
     * Prevedie DB riadok na polia pre kartu na hlavnej stranke (tech ako pole).
     */
    private static function mapCard(array $r): array
    {
        return [
            'id'    => (int) $r['id'],
            'nazov' => (string) $r['nazov'],
            'slug'  => (string) $r['slug'],
            'popis' => (string) $r['popis'],
            'tech'  => self::techToArray((string) $r['tech']),
            'tag'   => isset($r['tag']) && $r['tag'] !== '' ? (string) $r['tag'] : null,
            'url'   => isset($r['url']) && $r['url'] !== null && $r['url'] !== '' ? (string) $r['url'] : null,
        ];
    }

    /**
     * Technologie ulozene ako text oddeleny ciarkami -> pole orezanych hodnot.
     *
     * @return array<int,string>
     */
    public static function techToArray(string $tech): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $tech)), static function ($t) {
            return $t !== '';
        }));
    }

    /**
     * Pripravi parametre pre INSERT/UPDATE z validovanych vstupnych dat.
     */
    private static function bindParams(array $d): array
    {
        return [
            ':nazov'         => (string) $d['nazov'],
            ':slug'          => (string) $d['slug'],
            ':popis'         => (string) $d['popis'],
            ':popis_html'    => $d['popis_html'] !== '' ? (string) $d['popis_html'] : null,
            ':tech'          => (string) $d['tech'],
            ':tag'           => isset($d['tag']) && $d['tag'] !== '' ? (string) $d['tag'] : null,
            ':url'           => isset($d['url']) && $d['url'] !== '' ? (string) $d['url'] : null,
            ':poradie'       => (int) $d['poradie'],
            ':je_zverejneny' => !empty($d['je_zverejneny']) ? 1 : 0,
        ];
    }
}
