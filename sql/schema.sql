-- ============================================================
--  Schema databazy pre osobnu stranku Michala Dobsovica
--  Pouzitie: import na hostingu (phpMyAdmin / Adminer / CLI).
--  Tabulka projekty sa pouzije vo finalnej faze "Moje projekty".
-- ============================================================

-- Nastavenie kodovania pre korektnu diakritiku
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `projekty` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nazov`         VARCHAR(150)  NOT NULL,                 -- nazov projektu
    `popis`         TEXT          NOT NULL,                 -- kratky popis
    `tech`          VARCHAR(255)  NOT NULL DEFAULT '',      -- technologie oddelene ciarkami, napr. "PHP,MariaDB"
    `url`           VARCHAR(255)      NULL DEFAULT NULL,    -- odkaz na projekt (volitelny)
    `poradie`       INT           NOT NULL DEFAULT 0,       -- rucne poradie zobrazenia (mensie = vyssie)
    `je_zverejneny` TINYINT(1)    NOT NULL DEFAULT 1,       -- 1 = zobrazit na webe, 0 = skryt
    `vytvorene`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_zverejnene_poradie` (`je_zverejneny`, `poradie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ukazkove data (volitelne) - mozes odkomentovat a upravit:
-- INSERT INTO `projekty` (`nazov`, `popis`, `tech`, `url`, `poradie`) VALUES
-- ('Moj prvy projekt', 'Kratky popis projektu.', 'HTML,CSS,JS', NULL, 1),
-- ('Druhy projekt',    'Kratky popis projektu.', 'PHP,MariaDB', 'https://priklad.sk', 2);
