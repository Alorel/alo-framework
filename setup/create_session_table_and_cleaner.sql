-- Create session table and cleanup event
CREATE TABLE `alo_session` (
    `id` char(128) CHARACTER SET ascii NOT NULL,
    `data` varchar(16000) NOT NULL,
    `access` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DELIMITER ~~
CREATE EVENT `clean_sessions` ON SCHEDULE EVERY 1 MINUTE STARTS '2014-05-11 00:00:00' ON COMPLETION PRESERVE ENABLE DO BEGIN
	DELETE FROM `site_session`
	WHERE
	`access` < DATE_SUB(NOW(), INTERVAL 5 MINUTE);
END~~
DELIMITER ;