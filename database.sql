
-- ---
-- Table 'chats'
--
-- ---

CREATE TABLE IF NOT EXISTS `chats` (
  `chat_id` BIGINT(30) NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `greet_message` VARCHAR(4000) NULL,
  `leave_message` VARCHAR(4000) NULL,
  `rules` VARCHAR(4000) NULL,
  PRIMARY KEY (`chat_id`)
)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ---
-- Table 'chat_participants'
--
-- ---

CREATE TABLE IF NOT EXISTS `chat_participants`(
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `chat_id` BIGINT(30) NOT NULL,
  `user_id` BIGINT(30) NOT NULL,
  `username` VARCHAR(40),
  `first_name` VARCHAR(40),
  `last_name` VARCHAR(40),
  `msg_count` BIGINT DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
   PRIMARY KEY (`id`),
   FOREIGN KEY(chat_id) REFERENCES chats(chat_id)
)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ---
-- Table 'admins'
--
-- ---

CREATE TABLE IF NOT EXISTS `admins`(
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(64) NOT NULL,
  `password` VARCHAR(64) NOT NULL,
  `telegram_id` BIGINT(30) NULL,
   PRIMARY KEY (`id`),
    UNIQUE (login)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `songs` (
  `id` BIGINT AUTO_INCREMENT,
  `name` TEXT,
  `text` TEXT,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `errors` (
  `id` BIGINT AUTO_INCREMENT,
  `text` TEXT,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- ---
-- Table 'chat_participants'
--
-- ---

CREATE TABLE IF NOT EXISTS `quotes`(
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `chat_id` BIGINT(30) NOT NULL,
  `user_id` BIGINT(30) NOT NULL,
  `text` text NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
   PRIMARY KEY (`id`),
   FOREIGN KEY(chat_id) REFERENCES chats(chat_id)
)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ---
-- Table 'nfs'
--
-- ---

CREATE TABLE IF NOT EXISTS `nfs`(
   `user_id` BIGINT(30) NOT NULL,
  `chat_id` BIGINT(30) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `nf_timer` DATETIME NOT NULL,
  `fails` INT DEFAULT 0,
   PRIMARY KEY (`user_id`),
   FOREIGN KEY(chat_id) REFERENCES chats(chat_id)
)  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;