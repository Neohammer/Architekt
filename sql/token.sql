DROP TABLE IF EXISTS `token`;
CREATE TABLE IF NOT EXISTS `token` (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` int UNSIGNED NOT NULL,
    `code` varchar(20) NOT NULL,
    `datetime` datetime NOT NULL,
    `key` varchar(32) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`,`key`)
)
ENGINE=InnoDB;