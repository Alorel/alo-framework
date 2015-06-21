-- Create session table and cleanup event
CREATE TABLE `alo_session` (
  `id`     CHAR(128)
           CHARACTER SET `ascii`        NOT NULL,
  `data`   VARCHAR(16000)
           COLLATE `utf8mb4_general_ci` NOT NULL,
  `access` TIMESTAMP                    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY (`access`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = `utf8mb4`;

DELIMITER ~~
CREATE EVENT `clean_sessions`
  ON SCHEDULE EVERY 1 MINUTE STARTS '2015-01-01 00:00:15'
  ON COMPLETION PRESERVE ENABLE DO BEGIN
  DELETE FROM `alo_session`
  WHERE
    `access` < DATE_SUB(NOW(), INTERVAL 5 MINUTE);
END~~
DELIMITER ;
