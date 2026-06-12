<?php
// Ochrana pred priamym pristupom k suboru
defined('APP') || exit;

/*
 * Repozitar obrazkov galerie (tabulka projekt_obrazky).
 * Kazdy obrazok patri jednemu projektu (FK s ON DELETE CASCADE).
 * Samotne subory su ulozene v priecinku gallery_dir(); v DB je len nazov suboru.
 */
class ProjectImages
{
    // Maximalna velkost jedneho obrazka: 2 MB
    const MAX_BYTES = 2097152; // 2 * 1024 * 1024

    /**
     * Mapovanie povolenych MIME typov na priponu suboru.
     * Vrati priponu alebo null, ak format nie je podporovany.
     */
    public static function extForMime(string $mime): ?string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];

        return $map[$mime] ?? null;
    }

    /**
     * Vsetky obrazky daneho projektu, zoradene podla poradia.
     *
     * @return array<int, array{id:int, projekt_id:int, subor:string, alt:string, poradie:int}>
     */
    public static function forProject(int $projektId): array
    {
        require_once __DIR__ . '/db.php';

        $stmt = db()->prepare(
            'SELECT id, projekt_id, subor, alt, poradie
             FROM projekt_obrazky
             WHERE projekt_id = ?
             ORDER BY poradie ASC, id ASC'
        );
        $stmt->execute([$projektId]);

        return $stmt->fetchAll();
    }

    /**
     * Jeden obrazok podla id, alebo null ak neexistuje.
     */
    public static function find(int $id): ?array
    {
        require_once __DIR__ . '/db.php';

        $stmt = db()->prepare('SELECT * FROM projekt_obrazky WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * Prida novy obrazok k projektu. Poradie sa nastavi automaticky na koniec.
     * Vrati id noveho zaznamu.
     */
    public static function add(int $projektId, string $subor, string $alt = ''): int
    {
        require_once __DIR__ . '/db.php';

        $stmt = db()->prepare(
            'INSERT INTO projekt_obrazky (projekt_id, subor, alt, poradie)
             VALUES (:projekt_id, :subor, :alt, :poradie)'
        );
        $stmt->execute([
            ':projekt_id' => $projektId,
            ':subor'      => $subor,
            ':alt'        => $alt,
            ':poradie'    => self::nextPoradie($projektId),
        ]);

        return (int) db()->lastInsertId();
    }

    /**
     * Aktualizuje metadata obrazka (alternativny text a poradie).
     */
    public static function updateMeta(int $id, string $alt, int $poradie): void
    {
        require_once __DIR__ . '/db.php';

        $stmt = db()->prepare('UPDATE projekt_obrazky SET alt = :alt, poradie = :poradie WHERE id = :id');
        $stmt->execute([
            ':alt'     => $alt,
            ':poradie' => $poradie,
            ':id'      => $id,
        ]);
    }

    /**
     * Zmaze zaznam obrazka z databazy (subor na disku riesi volajuci).
     */
    public static function delete(int $id): void
    {
        require_once __DIR__ . '/db.php';

        $stmt = db()->prepare('DELETE FROM projekt_obrazky WHERE id = ?');
        $stmt->execute([$id]);
    }

    /**
     * Nazvy suborov vsetkych obrazkov projektu - pouziva sa pri mazani projektu,
     * aby sa odstranili aj subory z disku (DB zaznamy zmaze CASCADE).
     *
     * @return array<int, string>
     */
    public static function fileNamesForProject(int $projektId): array
    {
        require_once __DIR__ . '/db.php';

        $stmt = db()->prepare('SELECT subor FROM projekt_obrazky WHERE projekt_id = ?');
        $stmt->execute([$projektId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Nasledujuce poradie (najvyssie + 1) pre novy obrazok daneho projektu.
     */
    private static function nextPoradie(int $projektId): int
    {
        require_once __DIR__ . '/db.php';

        $stmt = db()->prepare('SELECT COALESCE(MAX(poradie), 0) + 1 FROM projekt_obrazky WHERE projekt_id = ?');
        $stmt->execute([$projektId]);

        return (int) $stmt->fetchColumn();
    }
}
