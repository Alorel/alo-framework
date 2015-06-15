-- Since this table will not hold very large amounts of data, the language code column is not a numeric ID pointing to
-- a different table

CREATE TABLE `alo_locale` (
	`id`    INT UNSIGNED                 NOT NULL AUTO_INCREMENT,
	`lang`  CHAR(2)
	        COLLATE `ascii_general_ci`   NOT NULL,
	`page`  VARCHAR(25)
	        COLLATE `ascii_general_ci`   NOT NULL,
	`key`   VARCHAR(25)
	        COLLATE `ascii_general_ci`   NOT NULL,
	`value` TEXT
	        COLLATE `utf8mb4_general_ci` NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `lang_page_key` (`lang`, `page`, `key`)
)
	ENGINE = InnoDB
	DEFAULT CHARSET = `utf8mb4`;