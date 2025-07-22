-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 10, 2025 at 06:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET
SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET
time_zone = "+00:00";

CREATE TABLE `directories`
(
    `id`         int(11) NOT NULL,
    `user_id`    int(11) NOT NULL,
    `name`       varchar(255) NOT NULL,
    `parent_id`  int(11) DEFAULT NULL,
    `created_at` timestamp    NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `files`
(
    `id`           int(11) NOT NULL,
    `user_id`      int(11) NOT NULL,
    `filename`     varchar(255) NOT NULL,
    `stored_name`  varchar(255) NOT NULL,
    `mime_type`    varchar(100)          DEFAULT NULL,
    `size`         bigint(20) DEFAULT NULL,
    `created_at`   timestamp    NOT NULL DEFAULT current_timestamp(),
    `public_token` varchar(255)          DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `file_shares`
(
    `id`         int(11) NOT NULL,
    `file_id`    int(11) NOT NULL,
    `token`      varchar(255) NOT NULL,
    `created_at` timestamp    NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users`
(
    `id`       int(11) NOT NULL,
    `name`     varchar(255) NOT NULL,
    `email`    varchar(255) NOT NULL,
    `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `files`
    ADD COLUMN `directory_id` INT NULL AFTER `user_id`,
  ADD KEY `directory_id` (`directory_id`),
  ADD CONSTRAINT `files_ibfk_2`
    FOREIGN KEY (`directory_id`)
    REFERENCES `directories` (`id`)
    ON DELETE SET NULL;

ALTER TABLE `users`
    ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'user';

INSERT INTO `users` (`id`, `name`, `email`, `password`)
VALUES (1, 'Alice', 'alice@example.com', '$2y$10$9fMPtjSvMaz2FMNM3wdm8.Hj9n2yNmG7rh5ahLZq3whPdxHhAZcJi'),
       (2, 'Bob', 'bob@example.com', '$2y$10$DS0JynaDU5rSvfNibSkJS.5pseIHaWUYL.T90Pt7k5n9ytbvKt3rW');

ALTER TABLE `file_shares`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `file_id` (`file_id`);

ALTER TABLE `files`
    ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `users`
    ADD PRIMARY KEY (`id`);

CREATE TABLE IF NOT EXISTS `file_user_access`
(
    `file_id`
    INT
    NOT
    NULL,
    `user_id`
    INT
    NOT
    NULL,
    `granted_at`
    TIMESTAMP
    DEFAULT
    CURRENT_TIMESTAMP,
    PRIMARY
    KEY
(
    `file_id`,
    `user_id`
),
    FOREIGN KEY
(
    `file_id`
) REFERENCES `files`
(
    `id`
) ON DELETE CASCADE,
    FOREIGN KEY
(
    `user_id`
) REFERENCES `users`
(
    `id`
)
  ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `password_resets`
(
    `email`
    VARCHAR
(
    255
) NOT NULL,
    `token` VARCHAR
(
    255
) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY
(
    `email`,
    `token`
)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `directories`
    ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`);

ALTER TABLE `directories`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `files`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `file_shares`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
    MODIFY `id` int (11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `directories`
    ADD CONSTRAINT `directories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `directories_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `directories` (`id`) ON
DELETE
CASCADE;

ALTER TABLE `files`
    ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `file_shares`
    ADD CONSTRAINT `file_shares_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE;

ALTER TABLE users
    ADD UNIQUE (email);
COMMIT;