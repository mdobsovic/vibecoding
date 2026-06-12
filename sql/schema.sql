-- ============================================================
--  Schema databazy pre osobnu stranku Michala Dobsovica
--  Pouzitie: import na hostingu (phpMyAdmin / Adminer / CLI).
--  Tabulka projekty a projekt_obrazky pre funkcionalitu "Moje projekty".
-- ============================================================

-- Nastavenie kodovania pre korektnu diakritiku
SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Projekty
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projekty` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nazov`         VARCHAR(150)  NOT NULL,                 -- nazov projektu
    `slug`          VARCHAR(160)  NOT NULL,                 -- URL identifikator (z nazvu), unikatny
    `popis`         TEXT          NOT NULL,                 -- kratky popis (karta + meta description)
    `popis_html`    MEDIUMTEXT        NULL,                 -- detailny HTML popis pre podstranku (z WYSIWYG)
    `tech`          VARCHAR(255)  NOT NULL DEFAULT '',      -- technologie oddelene ciarkami, napr. "PHP,MariaDB"
    `tag`           VARCHAR(50)       NULL DEFAULT NULL,    -- volitelny badge na karte (napr. "Nove")
    `url`           VARCHAR(255)      NULL DEFAULT NULL,    -- volitelny externy odkaz (zive demo)
    `poradie`       INT           NOT NULL DEFAULT 0,       -- rucne poradie zobrazenia (mensie = vyssie)
    `je_zverejneny` TINYINT(1)    NOT NULL DEFAULT 1,       -- 1 = zobrazit na webe, 0 = skryt
    `vytvorene`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `upravene`      TIMESTAMP         NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_slug` (`slug`),
    KEY `idx_zverejnene_poradie` (`je_zverejneny`, `poradie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Obrazky k projektom (galeria) - pripravene pre dalsi krok
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projekt_obrazky` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `projekt_id` INT UNSIGNED NOT NULL,
    `subor`      VARCHAR(255) NOT NULL,                     -- nazov suboru v priecinku s nahratymi obrazkami
    `alt`        VARCHAR(200) NOT NULL DEFAULT '',          -- alternativny text (pristupnost)
    `poradie`    INT          NOT NULL DEFAULT 0,
    `vytvorene`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_projekt` (`projekt_id`, `poradie`),
    CONSTRAINT `fk_obrazky_projekt` FOREIGN KEY (`projekt_id`)
        REFERENCES `projekty` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ukazkove data (volitelne) - mozes odkomentovat a upravit:
-- INSERT INTO `projekty` (`nazov`, `slug`, `popis`, `tech`, `url`, `poradie`) VALUES
-- ('Moj prvy projekt', 'moj-prvy-projekt', 'Kratky popis projektu.', 'HTML,CSS,JS', NULL, 1),
-- ('Druhy projekt',    'druhy-projekt',    'Kratky popis projektu.', 'PHP,MariaDB', 'https://priklad.sk', 2);
