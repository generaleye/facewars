CREATE DATABASE IF NOT EXISTS `oaufacewars`;

USE `oaufacewars`;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email_address` VARCHAR(255) NOT NULL UNIQUE,
#   `matric_no` VARCHAR(12) NOT NULL UNIQUE,
  `password` TEXT NOT NULL,
  `token` TEXT NOT NULL,
  `verification_token` VARCHAR(6) NOT NULL DEFAULT '000000',
  `created_time` DATETIME NOT NULL,
  `modified_time` DATETIME NOT NULL,
  `active_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 2,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `dates` (
  `date_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` DATE NOT NULL UNIQUE,
  `created_time` DATETIME NOT NULL,
  `active_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`date_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `competitors` (
  `competitor_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_id` INT UNSIGNED NOT NULL,
  `matric_no` VARCHAR(12) NOT NULL,
  `votes` INT NOT NULL DEFAULT 0,
  `position` INT NOT NULL DEFAULT 0,
  `created_time` DATETIME NOT NULL,
  `modified_time` DATETIME NOT NULL,
  `active_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`competitor_id`),
  CONSTRAINT `fk_competitors_date_id` FOREIGN KEY (`date_id`) REFERENCES dates(`date_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `votes` (
  `vote_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `competitor_id` INT UNSIGNED NOT NULL,
  `created_time` DATETIME NOT NULL,
  `active_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`vote_id`),
  CONSTRAINT `fk_votes_date_id` FOREIGN KEY (`date_id`) REFERENCES dates(`date_id`),
  CONSTRAINT `fk_votes_user_id` FOREIGN KEY (`user_id`) REFERENCES users(`user_id`),
  CONSTRAINT `fk_votes_competitor_id` FOREIGN KEY (`competitor_id`) REFERENCES competitors(`competitor_id`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `whitelists` (
  `whitelist_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `matric_no` VARCHAR(12) NOT NULL,
  `description` VARCHAR(511) NOT NULL,
  `active_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`whitelist_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `blacklists` (
  `blacklist_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `matric_no` VARCHAR(12) NOT NULL,
  `competitor_id` INT UNSIGNED NOT NULL,
  `active_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`blacklist_id`),
  CONSTRAINT `fk_blacklists_competitor_id` FOREIGN KEY (`competitor_id`) REFERENCES competitors(`competitor_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `comment` VARCHAR(511) NOT NULL,
  `created_time` DATETIME NOT NULL,
  `active_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`comment_id`),
  CONSTRAINT `fk_comments_date_id` FOREIGN KEY (`date_id`) REFERENCES dates(`date_id`),
  CONSTRAINT `fk_comments_user_id` FOREIGN KEY (`user_id`) REFERENCES users(`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_name` VARCHAR(50) NOT NULL,
  `email_address` VARCHAR(255) NOT NULL,
  `secret_user_id` VARCHAR(255) NULL,
  `message` TEXT NOT NULL,
  `created_time` DATETIME NOT NULL,
  `active_status` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB;