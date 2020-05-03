SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
                         `id` tinyint(2) UNSIGNED NOT NULL,
                         `nick_name` varchar(25) NOT NULL,
                         `password` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `affix`;
CREATE TABLE `affix` (
                         `id` mediumint(5) UNSIGNED NOT NULL,
                         `name` varchar(30) NOT NULL,
                         `starting_level` smallint(3) UNSIGNED NOT NULL,
                         `image` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `affix_set`;
CREATE TABLE `affix_set` (
                             `id` smallint(3) UNSIGNED NOT NULL,
                             `affix1_id` mediumint(5) UNSIGNED NOT NULL,
                             `affix2_id` mediumint(5) UNSIGNED NOT NULL,
                             `affix3_id` mediumint(5) UNSIGNED NOT NULL,
                             `affix4_id` mediumint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `class`;
CREATE TABLE `class` (
                         `id` tinyint(3) UNSIGNED NOT NULL,
                         `name` varchar(25) NOT NULL,
                         `image` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `current_update`;
CREATE TABLE `current_update` (
                                  `is_forcing` tinyint(1) UNSIGNED NOT NULL,
                                  `is_reseting` tinyint(1) UNSIGNED NOT NULL,
                                  `is_running` tinyint(1) UNSIGNED NOT NULL,
                                  `is_running_since` datetime NOT NULL,
                                  `key_level_max` tinyint(2) UNSIGNED DEFAULT NULL,
                                  `key_level_min` tinyint(2) UNSIGNED DEFAULT NULL,
                                  `last_leaderboard_id` int(10) UNSIGNED NOT NULL,
                                  `season` tinyint(2) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `current_update` (`is_forcing`, `is_reseting`, `is_running`, `is_running_since`, `key_level_max`, `key_level_min`, `last_leaderboard_id`, `season`) VALUES
(0, 0, 0, '2020-01-01 00:00:00', NULL, NULL, 0, 0);

DROP TABLE IF EXISTS `dungeon`;
CREATE TABLE `dungeon` (
                           `id` mediumint(5) UNSIGNED NOT NULL,
                           `name` varchar(70) NOT NULL,
                           `chest1` mediumint(7) NOT NULL,
                           `chest2` mediumint(7) NOT NULL,
                           `chest3` mediumint(7) NOT NULL,
                           `image` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `faction`;
CREATE TABLE `faction` (
                           `id` tinyint(1) UNSIGNED NOT NULL,
                           `name` varchar(25) NOT NULL,
                           `image` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `last_updated`;
CREATE TABLE `last_updated` (
                                `id` mediumint(6) UNSIGNED NOT NULL,
                                `dungeon_id` mediumint(5) UNSIGNED NOT NULL,
                                `realm_id` mediumint(6) UNSIGNED NOT NULL,
                                `region_id` tinyint(2) UNSIGNED NOT NULL,
                                `last_period` mediumint(5) UNSIGNED NOT NULL,
                                `last_dungeon` bigint(13) UNSIGNED NOT NULL,
                                `last_update_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `leaderboard`;
CREATE TABLE `leaderboard` (
                               `id` int(10) UNSIGNED NOT NULL,
                               `affix` smallint(3) UNSIGNED NOT NULL,
                               `chest` tinyint(1) UNSIGNED NOT NULL,
                               `completed_timestamp` bigint(14) UNSIGNED NOT NULL,
                               `dungeon` mediumint(5) UNSIGNED NOT NULL,
                               `faction` tinyint(1) UNSIGNED NOT NULL,
                               `level` tinyint(2) UNSIGNED NOT NULL,
                               `member_1` smallint(4) UNSIGNED DEFAULT NULL,
                               `member_2` smallint(4) UNSIGNED DEFAULT NULL,
                               `member_3` smallint(4) UNSIGNED DEFAULT NULL,
                               `member_4` smallint(4) UNSIGNED DEFAULT NULL,
                               `member_5` smallint(4) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `leaderboard_stats`;
CREATE TABLE `leaderboard_stats` (
                                     `affix` smallint(3) UNSIGNED NOT NULL,
                                     `dungeon` mediumint(5) UNSIGNED NOT NULL,
                                     `faction` tinyint(1) UNSIGNED NOT NULL,
                                     `level` tinyint(2) UNSIGNED NOT NULL,
                                     `specialization` smallint(4) UNSIGNED NOT NULL,
                                     `chest_0` int(9) UNSIGNED NOT NULL DEFAULT '0',
                                     `chest_1` int(9) UNSIGNED NOT NULL DEFAULT '0',
                                     `chest_2` int(9) UNSIGNED NOT NULL DEFAULT '0',
                                     `chest_3` int(9) UNSIGNED NOT NULL DEFAULT '0',
                                     `total` int(9) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `period`;
CREATE TABLE `period` (
    `id` mediumint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `realm`;
CREATE TABLE `realm` (
                         `id` mediumint(6) UNSIGNED NOT NULL,
                         `region_id` tinyint(2) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `region`;
CREATE TABLE `region` (
                          `id` tinyint(2) UNSIGNED NOT NULL,
                          `name` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `region` (`id`, `name`) VALUES
(1, 'us'),
(2, 'eu'),
(3, 'kr'),
(4, 'tw');

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
                        `id` tinyint(2) UNSIGNED NOT NULL,
                        `name` varchar(10) NOT NULL,
                        `image` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `spec`;
CREATE TABLE `spec` (
                        `id` smallint(4) UNSIGNED NOT NULL,
                        `name` varchar(25) NOT NULL,
                        `image` varchar(50) NOT NULL,
                        `role_id` tinyint(2) UNSIGNED NOT NULL,
                        `class_id` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `admin`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `nick_name` (`nick_name`);

ALTER TABLE `affix`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `affix_set`
    ADD PRIMARY KEY (`id`),
    ADD KEY `affix1_id` (`affix1_id`),
    ADD KEY `affix2_id` (`affix2_id`),
    ADD KEY `affix3_id` (`affix3_id`),
    ADD KEY `affix4_id` (`affix4_id`);

ALTER TABLE `class`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `dungeon`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `faction`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `last_updated`
    ADD PRIMARY KEY (`id`),
    ADD KEY `next_update_date` (`last_update_date`,`id`) USING BTREE;

ALTER TABLE `leaderboard`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `affix` (`affix`,`chest`,`completed_timestamp`,`dungeon`,`faction`,`level`,`member_1`,`member_2`,`member_3`,`member_4`,`member_5`);

ALTER TABLE `leaderboard_stats`
    ADD UNIQUE KEY `affix_set_id` (`affix`,`dungeon`,`faction`,`level`,`specialization`) USING BTREE;

ALTER TABLE `period`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `realm`
    ADD PRIMARY KEY (`id`),
    ADD KEY `region_id` (`region_id`) USING BTREE;

ALTER TABLE `region`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `role`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `spec`
    ADD PRIMARY KEY (`id`),
    ADD KEY `class_id` (`class_id`),
    ADD KEY `role_id` (`role_id`);


ALTER TABLE `admin`
    MODIFY `id` tinyint(2) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `affix_set`
    MODIFY `id` smallint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `faction`
    MODIFY `id` tinyint(1) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `last_updated`
    MODIFY `id` mediumint(6) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `leaderboard`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `region`
    MODIFY `id` tinyint(2) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `role`
    MODIFY `id` tinyint(2) UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `affix_set`
    ADD CONSTRAINT `affix_set_ibfk_1` FOREIGN KEY (`affix1_id`) REFERENCES `affix` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    ADD CONSTRAINT `affix_set_ibfk_2` FOREIGN KEY (`affix2_id`) REFERENCES `affix` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    ADD CONSTRAINT `affix_set_ibfk_3` FOREIGN KEY (`affix3_id`) REFERENCES `affix` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    ADD CONSTRAINT `affix_set_ibfk_4` FOREIGN KEY (`affix4_id`) REFERENCES `affix` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `realm`
    ADD CONSTRAINT `realm_ibfk_1` FOREIGN KEY (`region_id`) REFERENCES `region` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `spec`
    ADD CONSTRAINT `spec_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    ADD CONSTRAINT `spec_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;
